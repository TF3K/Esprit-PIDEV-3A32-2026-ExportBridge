<?php

namespace App\Controller\Admin;

use App\Entity\Market;
use App\Repository\MarketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/markets')]
class MarketController extends AbstractController
{
    #[Route('', name: 'app_admin_markets')]
    public function index(MarketRepository $repo): Response
    {
        return $this->render('admin/markets/index.html.twig', [
            'markets' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_markets_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $market = new Market();

        if ($request->isMethod('POST')) {
            $this->handleForm($market, $request);
            $market->setCreatedAt(new \DateTime());

            $em->persist($market);
            $em->flush();

            $this->addFlash('success', 'Market added successfully.');
            return $this->redirectToRoute('app_admin_markets');
        }

        return $this->render('admin/markets/form.html.twig', [
            'market' => $market,
            'mode'   => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_markets_edit')]
    public function edit(Market $market, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $this->handleForm($market, $request);
            $em->flush();

            $this->addFlash('success', 'Market updated successfully.');
            return $this->redirectToRoute('app_admin_markets');
        }

        return $this->render('admin/markets/form.html.twig', [
            'market' => $market,
            'mode'   => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_markets_delete', methods: ['POST'])]
    public function delete(Market $market, EntityManagerInterface $em): Response
    {
        $em->remove($market);
        $em->flush();

        $this->addFlash('success', 'Market deleted.');
        return $this->redirectToRoute('app_admin_markets');
    }

    private function handleForm(Market $market, Request $request): void
    {
        $market->setName($request->request->get('name'));
        $market->setCountryCode(strtoupper($request->request->get('country_code')));
        $market->setRegion($request->request->get('region'));
        $market->setDescription($request->request->get('description'));
        $market->setTradeAgreement($request->request->get('trade_agreement'));
        $market->setIsEu((bool) $request->request->get('is_eu'));
    }
}