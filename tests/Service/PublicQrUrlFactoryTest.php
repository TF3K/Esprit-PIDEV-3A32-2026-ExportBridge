<?php

namespace App\Tests\Service;

use App\Service\PublicQrUrlFactory;
use App\Service\QrCodeSvgGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PublicQrUrlFactoryTest extends TestCase
{
    public function testLoopbackQrPageBuildsReachableImageUrl(): void
    {
        $factory = new class extends PublicQrUrlFactory {
            protected function detectLanIp(): ?string
            {
                return '10.130.62.166';
            }
        };

        $request = Request::create('https://127.0.0.1:8000/dashboard/certificates/14/qr');
        $url = $factory->absoluteUrl($request, '/verify/certificate/14/image');

        self::assertSame('http://10.130.62.166:8000/verify/certificate/14/image', $url);
        self::assertStringStartsWith('data:image/svg+xml;base64,', (new QrCodeSvgGenerator())->dataUri($url));
    }
}
