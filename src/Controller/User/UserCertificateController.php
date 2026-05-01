<?php

namespace App\Controller\User;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use App\Service\PublicQrUrlFactory;
use App\Service\QrCodeSvgGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/certificates')]
class UserCertificateController extends AbstractController
{
    #[Route('', name: 'app_user_certificates')]
    public function index(CertificateRepository $certRepo): Response
    {
        $company = $this->getUser()->getCompany();

        $certificates = $company
            ? $certRepo->findBy(['company' => $company], ['created_at' => 'DESC'])
            : [];

        return $this->render('user/certificates/index.html.twig', [
            'certificates' => $certificates,
        ]);
    }

    #[Route('/{id}/qr', name: 'app_user_certificate_qr', methods: ['GET'])]
    public function qr(
        Certificate $certificate,
        Request $request,
        QrCodeSvgGenerator $qrCodeSvgGenerator,
        PublicQrUrlFactory $publicQrUrlFactory
    ): Response {
        $company = $this->getUser()->getCompany();
        if (!$company || $certificate->getCompany()?->getId() !== $company->getId()) {
            throw $this->createAccessDeniedException();
        }

        $certificateImageUrl = $publicQrUrlFactory->absoluteUrl(
            $request,
            $this->generateUrl('app_public_certificate_image', ['id' => $certificate->getId()])
        );

        return $this->render('user/certificates/qr.html.twig', [
            'certificate' => $certificate,
            'qr_image' => $qrCodeSvgGenerator->dataUri($certificateImageUrl),
        ]);
    }
}
