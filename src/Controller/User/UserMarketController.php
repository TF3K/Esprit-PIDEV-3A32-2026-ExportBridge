<?php

namespace App\Controller\User;

use App\Repository\MarketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Market;

#[Route('/dashboard/markets')]
class UserMarketController extends AbstractController
{
    #[Route('', name: 'app_user_markets')]
    public function index(MarketRepository $repo): Response
    {
        return $this->render('user/markets/index.html.twig', [
            'markets' => $repo->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_markets_show')]
    public function show(Market $market): Response
    {
        return $this->render('user/markets/show.html.twig', [
            'market' => $market,
        ]);
    }
}