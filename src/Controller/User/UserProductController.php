<?php

namespace App\Controller\User;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/products')]
class UserProductController extends AbstractController
{
    #[Route('', name: 'app_user_products')]
    public function index(ProductRepository $productRepo): Response
    {
        $user = $this->getUser();
        $company = $user && method_exists($user, 'getCompany') ? $user->getCompany() : null;
        if (!$company && $user && method_exists($user, 'getCompanies')) {
            foreach ($user->getCompanies() as $ownedCompany) {
                $company = $ownedCompany;
                break;
            }
        }

        $products = $company
            ? $productRepo->findBy(['company' => $company], ['created_at' => 'DESC'])
            : [];

        return $this->render('user/products/index.html.twig', [
            'products' => $products,
        ]);
    }
}