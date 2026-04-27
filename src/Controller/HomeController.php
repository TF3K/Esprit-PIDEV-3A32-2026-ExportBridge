<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\CompanyRepository;
use App\Repository\ManagerRepository;
use App\Repository\ProductRepository;
use App\Repository\PartnershipRepository;

use Knp\Component\Pager\PaginatorInterface;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home_root')]
    public function homeRoot(Request $request): Response
    {
        $locale = $request->getLocale();

        if (!\in_array($locale, ['en', 'fr', 'ar'], true)) {
            $locale = 'en';
        }

        return $this->redirectToRoute('home', ['_locale' => $locale]);
    }

    #[Route('/{_locale}/home', name: 'home', requirements: ['_locale' => 'en|fr|ar'])]
    public function index(
        CompanyRepository $companyRepository,
        ManagerRepository $managerRepository,
        ProductRepository $productRepository,
        PartnershipRepository $partnershipRepository,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        // Données statiques avec extensions vérifiées
        $data = [
            [
                'image' => 'assets/images/Houssem.JPG',
                'title' => 'Houssem Kanzari',
                'text' => 'Discover your emotional state and learn how balanced your feelings are in daily life.'
            ],
            [
                'image' => 'assets/images/process-icon1.PNG',
                'title' => 'Olive Oil',
                'text' => 'Measure your stress level and identify the emotional pressure affecting your mind.'
            ]
        ];

        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            1 // 1 item par page
        );

        return $this->render('home/home.html.twig', [
            'companyCount' => $companyRepository->count([]),
            'managerCount' => $managerRepository->count([]),
            'productCount' => $productRepository->count([]),
            'partnerCount' => $partnershipRepository->count([]),
            // Correction : ajout de la virgule manquante ici
            'items'        => $pagination
        ]);
    }
}
