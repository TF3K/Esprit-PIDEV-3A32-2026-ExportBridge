<?php

namespace App\Service;

class QrCodeSvgGenerator
{
    /**
     * Version data for QR Code error correction level L.
     *
     * @var array<int, array{ec: int, blocks: array<int, array{data: int, count: int}>}>
     */
    private const LEVEL_L = [
        1 => ['ec' => 7, 'blocks' => [['data' => 19, 'count' => 1]]],
        2 => ['ec' => 10, 'blocks' => [['data' => 34, 'count' => 1]]],
        3 => ['ec' => 15, 'blocks' => [['data' => 55, 'count' => 1]]],
        4 => ['ec' => 20, 'blocks' => [['data' => 80, 'count' => 1]]],
        5 => ['ec' => 26, 'blocks' => [['data' => 108, 'count' => 1]]],
        6 => ['ec' => 18, 'blocks' => [['data' => 68, 'count' => 2]]],
        7 => ['ec' => 20, 'blocks' => [['data' => 78, 'count' => 2]]],
        8 => ['ec' => 24, 'blocks' => [['data' => 97, 'count' => 2]]],
        9 => ['ec' => 30, 'blocks' => [['data' => 116, 'count' => 2]]],
        10 => ['ec' => 18, 'blocks' => [['data' => 68, 'count' => 2], ['data' => 69, 'count' => 2]]],
    ];

    /** @var array<int, array<int, int>> */
    private const ALIGNMENT_POSITIONS = [
        1 => [],
        2 => [6, 18],
        3 => [6, 22],
        4 => [6, 26],
        5 => [6, 30],
        6 => [6, 34],
        7 => [6, 22, 38],
        8 => [6, 24, 42],
        9 => [6, 26, 46],
        10 => [6, 28, 50],
    ];

    /** @var array<int, int>|null */
    private ?array $gfExp = null;

    /** @var array<int, int>|null */
    private ?array $gfLog = null;

    public function dataUri(string $data): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->svg($data));
    }

    public function svg(string $data, int $border = 4): string
    {
        $version = $this->selectVersion($data);
        [$modules, $reserved] = $this->drawFunctionPatterns($version);
        $codewords = $this->makeCodewords($data, $version);

        $this->drawCodewords($modules, $reserved, $codewords);

        $bestMask = 0;
        $bestPenalty = PHP_INT_MAX;
        $bestModules = $modules;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = $modules;
            $this->applyMask($candidate, $reserved, $mask);
            $this->drawFormatBits($candidate, $version, $mask);
            $penalty = $this->penaltyScore($candidate);
            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMask = $mask;
                $bestModules = $candidate;
            }
        }

        $this->drawFormatBits($bestModules, $version, $bestMask);

        return $this->modulesToSvg($bestModules, $border);
    }

    private function selectVersion(string $data): int
    {
        $length = strlen($data);

        foreach (self::LEVEL_L as $version => $info) {
            $capacityBits = $this->totalDataCodewords($version) * 8;
            $countBits = $version <= 9 ? 8 : 16;
            $requiredBits = 4 + $countBits + ($length * 8);

            if ($requiredBits <= $capacityBits) {
                return $version;
            }
        }

        throw new \InvalidArgumentException('The QR payload is too long for the built-in generator.');
    }

    /**
     * @return array{0: array<int, array<int, bool>>, 1: array<int, array<int, bool>>}
     */
    private function drawFunctionPatterns(int $version): array
    {
        $size = $this->size($version);
        $modules = array_fill(0, $size, array_fill(0, $size, false));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        $this->drawFinderPattern($modules, $reserved, 0, 0);
        $this->drawFinderPattern($modules, $reserved, $size - 7, 0);
        $this->drawFinderPattern($modules, $reserved, 0, $size - 7);

        for ($i = 8; $i < $size - 8; $i++) {
            $dark = $i % 2 === 0;
            $this->setModule($modules, $reserved, $i, 6, $dark);
            $this->setModule($modules, $reserved, 6, $i, $dark);
        }

        foreach (self::ALIGNMENT_POSITIONS[$version] as $x) {
            foreach (self::ALIGNMENT_POSITIONS[$version] as $y) {
                if ($reserved[$y][$x]) {
                    continue;
                }
                $this->drawAlignmentPattern($modules, $reserved, $x, $y);
            }
        }

        $this->setModule($modules, $reserved, 8, (4 * $version) + 9, true);
        if ($version >= 7) {
            $this->drawVersionBits($modules, $reserved, $version);
        }
        $this->reserveFormatAreas($modules, $reserved);

        return [$modules, $reserved];
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawFinderPattern(array &$modules, array &$reserved, int $left, int $top): void
    {
        $size = count($modules);

        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $x = $left + $dx;
                $y = $top + $dy;
                if ($x < 0 || $y < 0 || $x >= $size || $y >= $size) {
                    continue;
                }

                $inFinder = $dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6;
                $isBorder = $dx === 0 || $dx === 6 || $dy === 0 || $dy === 6;
                $isCenter = $dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4;
                $modules[$y][$x] = $inFinder && ($isBorder || $isCenter);
                $reserved[$y][$x] = true;
            }
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawAlignmentPattern(array &$modules, array &$reserved, int $centerX, int $centerY): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $distance = max(abs($dx), abs($dy));
                $this->setModule($modules, $reserved, $centerX + $dx, $centerY + $dy, $distance !== 1);
            }
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function reserveFormatAreas(array &$modules, array &$reserved): void
    {
        $size = count($modules);

        for ($i = 0; $i <= 8; $i++) {
            $this->setModule($modules, $reserved, 8, $i, $modules[$i][8] ?? false);
            $this->setModule($modules, $reserved, $i, 8, $modules[8][$i] ?? false);
        }

        for ($i = 0; $i < 8; $i++) {
            $this->setModule($modules, $reserved, $size - 1 - $i, 8, false);
            $this->setModule($modules, $reserved, 8, $size - 1 - $i, false);
        }
    }

    /**
     * @return array<int, int>
     */
    private function makeCodewords(string $data, int $version): array
    {
        $dataCodewords = $this->makeDataCodewords($data, $version);
        $ecCount = self::LEVEL_L[$version]['ec'];
        $blocks = [];
        $offset = 0;

        foreach (self::LEVEL_L[$version]['blocks'] as $group) {
            for ($i = 0; $i < $group['count']; $i++) {
                $blockData = array_slice($dataCodewords, $offset, $group['data']);
                $offset += $group['data'];
                $blocks[] = [
                    'data' => $blockData,
                    'ec' => $this->reedSolomonRemainder($blockData, $ecCount),
                ];
            }
        }

        $result = [];
        $maxDataLength = max(array_map(static fn (array $block): int => count($block['data']), $blocks));

        for ($i = 0; $i < $maxDataLength; $i++) {
            foreach ($blocks as $block) {
                if (array_key_exists($i, $block['data'])) {
                    $result[] = $block['data'][$i];
                }
            }
        }

        for ($i = 0; $i < $ecCount; $i++) {
            foreach ($blocks as $block) {
                $result[] = $block['ec'][$i];
            }
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    private function makeDataCodewords(string $data, int $version): array
    {
        $bits = [];
        $this->appendBits($bits, 0b0100, 4);
        $this->appendBits($bits, strlen($data), $version <= 9 ? 8 : 16);

        foreach (array_values(unpack('C*', $data) ?: []) as $byte) {
            $this->appendBits($bits, $byte, 8);
        }

        $capacityBits = $this->totalDataCodewords($version) * 8;
        $this->appendBits($bits, 0, min(4, $capacityBits - count($bits)));

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $value = 0;
            for ($j = 0; $j < 8; $j++) {
                $value = ($value << 1) | $bits[$i + $j];
            }
            $codewords[] = $value;
        }

        for ($pad = 0; count($codewords) < $this->totalDataCodewords($version); $pad++) {
            $codewords[] = $pad % 2 === 0 ? 0xEC : 0x11;
        }

        return $codewords;
    }

    /**
     * @param array<int, int> $bits
     */
    private function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     * @param array<int, int> $codewords
     */
    private function drawCodewords(array &$modules, array $reserved, array $codewords): void
    {
        $size = count($modules);
        $bitIndex = 0;
        $totalBits = count($codewords) * 8;
        $direction = -1;

        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vertical = 0; $vertical < $size; $vertical++) {
                $y = $direction === -1 ? $size - 1 - $vertical : $vertical;

                for ($column = 0; $column < 2; $column++) {
                    $x = $right - $column;
                    if ($reserved[$y][$x]) {
                        continue;
                    }

                    $dark = false;
                    if ($bitIndex < $totalBits) {
                        $dark = (($codewords[intdiv($bitIndex, 8)] >> (7 - ($bitIndex % 8))) & 1) !== 0;
                    }

                    $modules[$y][$x] = $dark;
                    $bitIndex++;
                }
            }

            $direction *= -1;
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function applyMask(array &$modules, array $reserved, int $mask): void
    {
        $size = count($modules);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if (!$reserved[$y][$x] && $this->maskBit($mask, $x, $y)) {
                    $modules[$y][$x] = !$modules[$y][$x];
                }
            }
        }
    }

    private function maskBit(int $mask, int $x, int $y): bool
    {
        return match ($mask) {
            0 => ($x + $y) % 2 === 0,
            1 => $y % 2 === 0,
            2 => $x % 3 === 0,
            3 => ($x + $y) % 3 === 0,
            4 => (intdiv($y, 2) + intdiv($x, 3)) % 2 === 0,
            5 => (($x * $y) % 2) + (($x * $y) % 3) === 0,
            6 => ((($x * $y) % 2) + (($x * $y) % 3)) % 2 === 0,
            7 => ((($x + $y) % 2) + (($x * $y) % 3)) % 2 === 0,
        };
    }

    /**
     * @param array<int, array<int, bool>> $modules
     */
    private function drawFormatBits(array &$modules, int $version, int $mask): void
    {
        $size = $this->size($version);
        $format = $this->formatBits($mask);

        for ($i = 0; $i <= 5; $i++) {
            $modules[$i][8] = $this->bit($format, $i);
        }
        $modules[7][8] = $this->bit($format, 6);
        $modules[8][8] = $this->bit($format, 7);
        $modules[8][7] = $this->bit($format, 8);
        for ($i = 9; $i < 15; $i++) {
            $modules[8][14 - $i] = $this->bit($format, $i);
        }

        for ($i = 0; $i < 8; $i++) {
            $modules[8][$size - 1 - $i] = $this->bit($format, $i);
        }
        for ($i = 8; $i < 15; $i++) {
            $modules[$size - 15 + $i][8] = $this->bit($format, $i);
        }
        $modules[$size - 8][8] = true;
    }

    private function formatBits(int $mask): int
    {
        $data = (0b01 << 3) | $mask;
        $remainder = $data << 10;

        for ($i = 14; $i >= 10; $i--) {
            if ((($remainder >> $i) & 1) !== 0) {
                $remainder ^= 0x537 << ($i - 10);
            }
        }

        return (($data << 10) | $remainder) ^ 0x5412;
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawVersionBits(array &$modules, array &$reserved, int $version): void
    {
        $size = $this->size($version);
        $versionBits = $this->versionBits($version);

        for ($i = 0; $i < 18; $i++) {
            $dark = $this->bit($versionBits, $i);
            $a = $size - 11 + ($i % 3);
            $b = intdiv($i, 3);

            $this->setModule($modules, $reserved, $a, $b, $dark);
            $this->setModule($modules, $reserved, $b, $a, $dark);
        }
    }

    private function versionBits(int $version): int
    {
        $remainder = $version << 12;

        for ($i = 17; $i >= 12; $i--) {
            if ((($remainder >> $i) & 1) !== 0) {
                $remainder ^= 0x1F25 << ($i - 12);
            }
        }

        return ($version << 12) | ($remainder & 0xFFF);
    }

    private function bit(int $value, int $index): bool
    {
        return (($value >> $index) & 1) !== 0;
    }

    /**
     * @param array<int, int> $data
     * @return array<int, int>
     */
    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = $this->reedSolomonGenerator($degree);
        $remainder = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;

            for ($i = 0; $i < $degree; $i++) {
                $remainder[$i] ^= $this->gfMultiply($generator[$i + 1], $factor);
            }
        }

        return $remainder;
    }

    /**
     * @return array<int, int>
     */
    private function reedSolomonGenerator(int $degree): array
    {
        $generator = [1];

        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($generator) + 1, 0);
            foreach ($generator as $j => $coefficient) {
                $next[$j] ^= $coefficient;
                $next[$j + 1] ^= $this->gfMultiply($coefficient, $this->gfPow($i));
            }
            $generator = $next;
        }

        return $generator;
    }

    private function gfMultiply(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        $this->initGaloisTables();

        return $this->gfExp[($this->gfLog[$x] + $this->gfLog[$y]) % 255];
    }

    private function gfPow(int $exponent): int
    {
        $this->initGaloisTables();

        return $this->gfExp[$exponent % 255];
    }

    private function initGaloisTables(): void
    {
        if ($this->gfExp !== null && $this->gfLog !== null) {
            return;
        }

        $this->gfExp = array_fill(0, 512, 0);
        $this->gfLog = array_fill(0, 256, 0);
        $value = 1;

        for ($i = 0; $i < 255; $i++) {
            $this->gfExp[$i] = $value;
            $this->gfLog[$value] = $i;
            $value <<= 1;
            if (($value & 0x100) !== 0) {
                $value ^= 0x11D;
            }
        }

        for ($i = 255; $i < 512; $i++) {
            $this->gfExp[$i] = $this->gfExp[$i - 255];
        }
    }

    /**
     * @param array<int, array<int, bool>> $matrix
     */
    private function penaltyScore(array $matrix): int
    {
        $size = count($matrix);
        $score = 0;

        for ($y = 0; $y < $size; $y++) {
            $score += $this->linePenalty($matrix[$y]);
        }

        for ($x = 0; $x < $size; $x++) {
            $column = [];
            for ($y = 0; $y < $size; $y++) {
                $column[] = $matrix[$y][$x];
            }
            $score += $this->linePenalty($column);
        }

        for ($y = 0; $y < $size - 1; $y++) {
            for ($x = 0; $x < $size - 1; $x++) {
                $color = $matrix[$y][$x];
                if ($matrix[$y][$x + 1] === $color && $matrix[$y + 1][$x] === $color && $matrix[$y + 1][$x + 1] === $color) {
                    $score += 3;
                }
            }
        }

        $dark = 0;
        foreach ($matrix as $row) {
            foreach ($row as $module) {
                if ($module) {
                    $dark++;
                }
            }
        }

        $total = $size * $size;
        $score += intdiv(abs(($dark * 20) - ($total * 10)), $total) * 10;

        return $score;
    }

    /**
     * @param array<int, bool> $line
     */
    private function linePenalty(array $line): int
    {
        $score = 0;
        $runColor = $line[0];
        $runLength = 1;
        $length = count($line);

        for ($i = 1; $i < $length; $i++) {
            if ($line[$i] === $runColor) {
                $runLength++;
            } else {
                if ($runLength >= 5) {
                    $score += 3 + ($runLength - 5);
                }
                $runColor = $line[$i];
                $runLength = 1;
            }
        }

        if ($runLength >= 5) {
            $score += 3 + ($runLength - 5);
        }

        for ($i = 0; $i <= $length - 11; $i++) {
            $pattern = [
                $line[$i],
                $line[$i + 1],
                $line[$i + 2],
                $line[$i + 3],
                $line[$i + 4],
                $line[$i + 5],
                $line[$i + 6],
            ];

            if ($pattern === [true, false, true, true, true, false, true]) {
                $before = $i >= 4 && !$line[$i - 1] && !$line[$i - 2] && !$line[$i - 3] && !$line[$i - 4];
                $after = $i + 10 < $length && !$line[$i + 7] && !$line[$i + 8] && !$line[$i + 9] && !$line[$i + 10];
                if ($before || $after) {
                    $score += 40;
                }
            }
        }

        return $score;
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function setModule(array &$modules, array &$reserved, int $x, int $y, bool $dark): void
    {
        $modules[$y][$x] = $dark;
        $reserved[$y][$x] = true;
    }

    private function totalDataCodewords(int $version): int
    {
        $total = 0;
        foreach (self::LEVEL_L[$version]['blocks'] as $group) {
            $total += $group['data'] * $group['count'];
        }

        return $total;
    }

    private function size(int $version): int
    {
        return 21 + (($version - 1) * 4);
    }

    /**
     * @param array<int, array<int, bool>> $modules
     */
    private function modulesToSvg(array $modules, int $border): string
    {
        $size = count($modules);
        $viewBoxSize = $size + ($border * 2);
        $path = '';

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($modules[$y][$x]) {
                    $path .= sprintf('M%d %dh1v1h-1z', $x + $border, $y + $border);
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" shape-rendering="crispEdges"><path fill="#fff" d="M0 0h%1$dv%1$dH0z"/><path fill="#111" d="%2$s"/></svg>',
            $viewBoxSize,
            $path
        );
    }
}
