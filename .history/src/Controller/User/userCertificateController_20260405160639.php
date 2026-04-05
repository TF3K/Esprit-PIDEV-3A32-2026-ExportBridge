<?php

namespace App\Controller\User;

use App\Repository\CertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/certificates')]
class UserCertificateController extends AbstractController
{
    #[Route('', name: 'app_user_certificates')]
    public function index(CertificateRepository $certRepo): Response
    {
        $company = $this->getUser()->getCompanie();

        $certificates = $company
            ? $certRepo->findBy(['company' => $company], ['created_at' => 'DESC'])
            : [];

        return $this->render('user/certificates/index.html.twig', [
            'certificates' => $certificates,
        ]);
    }
}