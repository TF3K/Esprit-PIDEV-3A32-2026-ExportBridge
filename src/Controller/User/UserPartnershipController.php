<?php

namespace App\Controller\User;

use App\Repository\PartnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/partnership')]
class UserPartnershipController extends AbstractController
{
    #[Route('', name: 'app_user_partnership')]
    public function index(PartnershipRepository $repo): Response
    {
        $company = $this->getUser()->getCompany();

        $partnership = $company
            ? $repo->findOneBy(['company' => $company])
            : null;

        return $this->render('user/partnership/index.html.twig', [
            'partnership' => $partnership,
        ]);
    }
}