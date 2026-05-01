<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CertificateRepository;
use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        ProductRepository $productRepo,
        CertificateRepository $certRepo,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();
        $company = $user && method_exists($user, 'getCompany') ? $user->getCompany() : null;
        if (!$company && $user && method_exists($user, 'getCompanies')) {
            foreach ($user->getCompanies() as $ownedCompany) {
                $company = $ownedCompany;
                if (method_exists($user, 'setCompany')) {
                    $user->setCompany($company);
                    $em->flush();
                }
                break;
            }
        }
        if (!$company && $user && method_exists($user, 'setCompany')) {
            $companyName = trim((string) (($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')));
            if ($companyName === '') {
                $companyName = 'My Company';
            }
            $company = new Company();
            $company->setCompanyName($companyName . ' Company');
            $company->setCountry('Tunisia');
            $company->setContactEmail($user->getEmail());
            $company->setWarnings(0);
            $company->setIsBanned(false);
            $company->setManager($user);
            $company->setCreatedAt(new \DateTime());
            $company->setLastUpdated(new \DateTime());
            $user->setCompany($company);
            $em->persist($company);
            $em->persist($user);
            $em->flush();
        }

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