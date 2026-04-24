<?php

namespace App\Controller\Admin;

use App\Entity\Market;
use App\Repository\MarketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/markets')]
class MarketController extends AbstractController
{
    use FormValidationTrait;

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
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $name           = trim($request->request->get('name') ?? '');
            $countryCode    = strtoupper(trim($request->request->get('country_code') ?? ''));
            $region         = trim($request->request->get('region') ?? '');
            $description    = trim($request->request->get('description') ?? '');
            $tradeAgreement = trim($request->request->get('trade_agreement') ?? '');

            $old = compact('name', 'countryCode', 'region', 'description', 'tradeAgreement');

            $this->clearValidationErrors();

            $this->validateName($name, 'Country name', true);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateCountryCode($countryCode, true);
            if ($this->hasValidationErrors()) {
                $errors['country_code'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 1);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateName($tradeAgreement, 'Trade agreement', false);
            if ($this->hasValidationErrors()) {
                $errors['trade_agreement'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $this->applyToEntity($market, $name, $countryCode, $region, $description, $tradeAgreement, $request);
                $market->setCreatedAt(new \DateTime());

                $em->persist($market);
                $em->flush();

                $this->addFlash('success', 'Market added successfully.');
                return $this->redirectToRoute('app_admin_markets');
            }
        }

        return $this->render('admin/markets/form.html.twig', [
            'market'  => $market,
            'mode'    => 'add',
            'errors'  => $errors,
            'old'     => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_markets_edit')]
    public function edit(Market $market, Request $request, EntityManagerInterface $em): Response
    {
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $name           = trim($request->request->get('name') ?? '');
            $countryCode    = strtoupper(trim($request->request->get('country_code') ?? ''));
            $region         = trim($request->request->get('region') ?? '');
            $description    = trim($request->request->get('description') ?? '');
            $tradeAgreement = trim($request->request->get('trade_agreement') ?? '');

            $old = compact('name', 'countryCode', 'region', 'description', 'tradeAgreement');

            $this->clearValidationErrors();

            $this->validateName($name, 'Country name', true);
            if ($this->hasValidationErrors()) {
                $errors['name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateCountryCode($countryCode, true);
            if ($this->hasValidationErrors()) {
                $errors['country_code'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateRequired($description, 'Description', 1);
            if ($this->hasValidationErrors()) {
                $errors['description'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateName($tradeAgreement, 'Trade agreement', false);
            if ($this->hasValidationErrors()) {
                $errors['trade_agreement'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if (empty($errors)) {
                $this->applyToEntity($market, $name, $countryCode, $region, $description, $tradeAgreement, $request);
                $em->flush();

                $this->addFlash('success', 'Market updated successfully.');
                return $this->redirectToRoute('app_admin_markets');
            }
        }

        return $this->render('admin/markets/form.html.twig', [
            'market'  => $market,
            'mode'    => 'edit',
            'errors'  => $errors,
            'old'     => $old,
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

    // Renamed from handleForm to be more descriptive, now takes explicit args
    // instead of re-reading from Request (avoids re-trimming / re-uppercasing)
    private function applyToEntity(
        Market $market,
        string $name,
        string $countryCode,
        string $region,
        string $description,
        string $tradeAgreement,
        Request $request
    ): void {
        $market->setName($name);
        $market->setCountryCode($countryCode);
        $market->setRegion($region);
        $market->setDescription($description);
        $market->setTradeAgreement($tradeAgreement);
        $market->setIsEu((bool) $request->request->get('is_eu'));
    }
}