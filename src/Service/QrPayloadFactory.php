<?php

namespace App\Service;

use App\Entity\Partnership;

class QrPayloadFactory
{
    public function partnership(Partnership $partnership): string
    {
        return $this->encode([
            'kind' => 'partnership',
            'id' => $partnership->getId(),
            'type' => $partnership->getType(),
            'status' => $partnership->getStatus(),
            'established' => $this->date($partnership->getEstablishedDate()),
            'terminated' => $this->date($partnership->getTerminatedDate()),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function encode(array $payload): string
    {
        $payload = array_filter(
            $payload,
            static fn (mixed $value): bool => $value !== null && $value !== ''
        );

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function date(?\DateTimeInterface $date): ?string
    {
        return $date?->format('Y-m-d');
    }
}
