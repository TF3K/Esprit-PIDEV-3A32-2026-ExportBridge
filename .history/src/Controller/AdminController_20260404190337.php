<?php

namespace App\Controller;

use App\Repository\CompanRepository;
use App\Repository\ProductRepository;
use App\Repository\ManagerRepository;
use App\Repository\CertificateRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(
        CompanyRepository $companyRepo,
        ProductRepository $productRepo,
        ManagerRepository $managerRepo,
        CertificateRepository $certificateRepo
    ): Response {
        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => [
                'companies'    => $companyRepo->count([]),
                'products'     => $productRepo->count([]),
                'managers'     => $managerRepo->count([]),
                'certificates' => $certificateRepo->count([]),
            ],
            'recentCompanies' => $companyRepo->findBy([], ['created_at' => 'DESC'], 5),
        ]);
    }
}