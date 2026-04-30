<?php

namespace App\Controller;

use App\Entity\Manager;
use App\Repository\CertificateRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepo,
        CertificateRepository $certRepo
    ): Response {
        $user = $this->getUser();
        $company = null;

        if ($user instanceof Manager) {
            $company = $user->getCompany();
        }

        $products = [];
        $certificates = [];
        $recentProducts = [];

        if ($company) {
            $products = $productRepo->findBy(['company' => $company], ['created_at' => 'DESC']);
            $certificates = $certRepo->findBy(['company' => $company], ['created_at' => 'DESC']);

            $recentProducts = array_slice($products, 0, 5);
        }

        $activeCertificates = array_filter($certificates, static function ($certificate): bool {
            return $certificate->getStatus() === 'active';
        });

        return $this->render('user/base_user.html.twig', [
            'stats' => [
                'products' => count($products),
                'certificates' => count($certificates),
                'activeCertificates' => count($activeCertificates),
            ],
            'recentProducts' => $recentProducts,
            'recentCertificates' => array_slice($certificates, 0, 5),
        ]);
    }
}
