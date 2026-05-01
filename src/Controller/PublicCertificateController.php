<?php

namespace App\Controller;

use App\Entity\Certificate;
use App\Service\CertificateImageFactory;
use App\Service\CertificatePdfFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class PublicCertificateController extends AbstractController
{
    #[Route('/verify/certificate/{id}', name: 'app_public_certificate_verify', methods: ['GET'])]
    public function verify(Certificate $certificate): Response
    {
        return $this->render('public/certificate_verify.html.twig', [
            'certificate' => $certificate,
        ]);
    }

    #[Route('/verify/certificate/{id}/pdf', name: 'app_public_certificate_pdf', methods: ['GET'])]
    public function pdf(Certificate $certificate, CertificatePdfFactory $certificatePdfFactory): Response
    {
        $pdf = $certificatePdfFactory->generate($certificate);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $certificatePdfFactory->fileName($certificate)
            ),
            'Content-Length' => (string) strlen($pdf),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    #[Route('/verify/certificate/{id}/image', name: 'app_public_certificate_image', methods: ['GET'])]
    public function image(Certificate $certificate, CertificateImageFactory $certificateImageFactory): Response
    {
        $svg = $certificateImageFactory->generate($certificate);

        return new Response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Content-Disposition' => HeaderUtils::makeDisposition(
                ResponseHeaderBag::DISPOSITION_INLINE,
                $certificateImageFactory->fileName($certificate)
            ),
            'Content-Length' => (string) strlen($svg),
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
