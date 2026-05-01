<?php

namespace App\Tests\Service;

use App\Entity\Partnership;
use App\Service\QrPayloadFactory;
use PHPUnit\Framework\TestCase;

class QrPayloadFactoryTest extends TestCase
{
    public function testPartnershipPayloadIsJsonWithoutLinks(): void
    {
        $partnership = (new Partnership())
            ->setId(7)
            ->setType('Distribution')
            ->setStatus('ACTIVE')
            ->setEstablishedDate(new \DateTimeImmutable('2026-02-15'));

        $json = (new QrPayloadFactory())->partnership($partnership);
        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        self::assertSame([
            'kind' => 'partnership',
            'id' => 7,
            'type' => 'Distribution',
            'status' => 'ACTIVE',
            'established' => '2026-02-15',
        ], $payload);
        self::assertStringNotContainsString('http://', $json);
        self::assertStringNotContainsString('https://', $json);
    }
}
