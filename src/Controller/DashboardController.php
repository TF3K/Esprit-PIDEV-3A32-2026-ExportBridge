<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CertificateRepository;
use App\Repository\CompanyRepository;
use App\Repository\ManagerRepository;
use App\Repository\PartnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepo,
        CertificateRepository $certRepo,
        CompanyRepository $companyRepo,
        ManagerRepository $managerRepo,
        PartnershipRepository $partnershipRepo
    ): Response {

        // ===============================
        // Utilisateur connecté
        // ===============================
        $user = $this->getUser();

        $company = null;

        if ($user && method_exists($user, 'getCompany')) {
            $company = $user->getCompany();
        }

        // ===============================
        // Statistiques globales
        // ===============================
        $totalUsers = $managerRepo->count([]);
        $totalCompanies = $companyRepo->count([]);
        $totalProducts = $productRepo->count([]);
        $totalPartnerships = $partnershipRepo->count([]);
        $allManagers = $managerRepo->findAll();

        // ===============================
        // Statistiques personnelles
        // ===============================
        $myProductsCount = 0;
        $myCertificatesCount = 0;
        $recentProducts = [];

        if ($company) {

            $myProductsCount = $productRepo->count([
                'company' => $company
            ]);

            $myCertificatesCount = $certRepo->count([
                'company' => $company
            ]);

            $recentProducts = $productRepo->findBy(
                ['company' => $company],
                ['created_at' => 'DESC'],
                5
            );
        }

        // ===============================
        // Graph Charts
        // ===============================
        $chartData = method_exists($managerRepo, 'getCountByDay')
            ? $managerRepo->getCountByDay()
            : [];

        $companyChartData = method_exists($companyRepo, 'getCountByDay')
            ? $companyRepo->getCountByDay()
            : [];

        $partnershipChartData = method_exists($partnershipRepo, 'getCountByDay')
            ? $partnershipRepo->getCountByDay()
            : [];

        $productChartData = method_exists($productRepo, 'getCountByDay')
            ? $productRepo->getCountByDay()
            : [];

        // ===============================
        // Render
        // ===============================
        return $this->render('user/dashboard/index.html.twig', [
 'allManagers' => $allManagers,
            'stats' => [
               
                'totalUsers' => $totalUsers,
                'totalCompanies' => $totalCompanies,
                'totalProducts' => $totalProducts,
                'totalPartnerships' => $totalPartnerships,
                'myProducts' => $myProductsCount,
                'myCertificates' => $myCertificatesCount,
            ],

            'recentProducts' => $recentProducts,

            'chartData' => $chartData,
            'companyChartData' => $companyChartData,
            'partnershipChartData' => $partnershipChartData,
            'productChartData' => $productChartData,
        ]);
    }
}