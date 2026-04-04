<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepo,
        CertificateRepository $certRepo
    ): Response {
        $user = $this->getUser();
        $company = $user->getCompanie();

        $products = $company
            ? $productRepo->findBy(['company' => $company], ['created_at' => 'DESC'])
            : [];

        $certificates = $company
            ? $certRepo->findBy(['company' => $company], ['created_at' => 'DESC'])
            : [];

        $activeCertificates = array_filter($certificates, fn($c) => $c->getStatus() === 'active');

        return $this->render('user/dashboard/index.html.twig', [
            'stats' => [
                'products'           => count($products),
                'certificates'       => count($certificates),
                'activeCertificates' => count($activeCertificates),
            ],
            'recentProducts'     => array_slice($products, 0, 5),
            'recentCertificates' => array_slice($certificates, 0, 5),
        ]);
    }
}