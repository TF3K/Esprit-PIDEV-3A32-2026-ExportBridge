<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Market;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MarketRepository;

class MarketController extends AbstractController
{
    #[Route('/market/create', name: 'app_market_create', methods: ['GET', 'POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): Response
    {
        if ($request->isMethod('POST')) {

            $token = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('market_create', $token)) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            $em = $doctrine->getManager();

            $name            = trim((string) $request->request->get('title', ''));
            $tradeAgreement  = trim((string) $request->request->get('trade_agreement', ''));
            $region          = $request->request->get('region') !== null ? (string) $request->request->get('region') : null;
            $countryCode     = $request->request->get('country_code') !== null ? (string) $request->request->get('country_code') : null;
            $description     = trim((string) $request->request->get('description', ''));
            $isEu            = $request->request->getBoolean('featured');

            if (empty($name) || empty($tradeAgreement) || empty($region) || empty($countryCode) || empty($description)) {
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis.');
                return $this->redirectToRoute('app_market_create');
            }

            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]{3,50}$/", $name)) {
                $this->addFlash('error', 'Le titre du marché doit contenir uniquement des lettres (entre 3 et 50 caractères).');
                return $this->redirectToRoute('app_market_create');
            }

            if (strlen($description) < 10) {
                $this->addFlash('error', 'La description est trop courte (minimum 10 caractères).');
                return $this->redirectToRoute('app_market_create');
            }

            $market = new Market();
            $market->setName($name);
            $market->setTradeAgreement($tradeAgreement);
            $market->setRegion($region);
            $market->setCountryCode($countryCode);
            $market->setIsEu($isEu);
            $market->setDescription($description);
            $market->setCreatedAt(new \DateTime());

            try {
                $em->persist($market);
                $em->flush();
                $this->addFlash('success', 'Le marché "' . $name . '" a été ajouté avec succès !');
                return $this->redirectToRoute('app_admin_markets');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la sauvegarde en base de données.');
            }

            return $this->redirectToRoute('app_market_create');
        }

        return $this->render('markets/AddMarket.html.twig');
    }

    #[Route('/market/list', name: 'app_market_list')]
    public function list(Request $request, MarketRepository $repo): Response
    {
        $limit = 5;
        $page  = max(1, (int) $request->query->get('page', 1));

        $totalEntries = $repo->countAll();
        $markets      = $repo->findPaginated($page, $limit);

        $from       = $totalEntries > 0 ? ($page - 1) * $limit + 1 : 0;
        $to         = min($page * $limit, $totalEntries);
        $totalPages = (int) ceil($totalEntries / $limit);

        return $this->render('markets/listMarket.html.twig', [
            'markets'      => $markets,
            'totalEntries' => $totalEntries,
            'from'         => $from,
            'to'           => $to,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
        ]);
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request, MarketRepository $repo): Response
    {
        $name   = $request->query->get('name') !== null ? (string) $request->query->get('name') : null;
        $sortBy = $request->query->get('sort') !== null ? (string) $request->query->get('sort') : null;

        $list = $repo->searchAndSort($name, $sortBy);

        return $this->render('markets/listMarket.html.twig', [
            'markets'      => $list,
            'totalEntries' => count($list),
            'from'         => count($list) > 0 ? 1 : 0,
            'to'           => count($list),
            'currentPage'  => 1,
            'totalPages'   => 1,
        ]);
    }

    // ✅ FIXED: méthode delete complète avec dissociation + remove + flush
    #[Route('/deleteMarket/{id}', name: 'deleteMarket', methods: ['POST'])]
    public function delete(int $id, ManagerRegistry $manager, MarketRepository $repo): Response
    {
        $em     = $manager->getManager();
        $market = $repo->find($id);

        if ($market) {
            // ✅ Dissocier toutes les companies avant suppression
            foreach ($market->getCompanies() as $company) {
                $company->setMarket(null);
                $em->persist($company);
            }

            // ✅ flush pour sauvegarder la dissociation
            $em->flush();

            // ✅ Supprimer le market
            $em->remove($market);
            $em->flush();

            $this->addFlash('success', 'Le marché a été supprimé avec succès.');
        }

        return $this->redirectToRoute('app_market_list');
    }

    #[Route('/market/update/{id}', name: 'app_market_update', methods: ['GET', 'POST'])]
    public function update(Market $market, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {

            $token = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('market_update_' . $market->getId(), $token)) {
                throw $this->createAccessDeniedException('Invalid CSRF token');
            }

            $name           = trim((string) $request->request->get('title', ''));
            $tradeAgreement = trim((string) $request->request->get('trade_agreement', ''));
            $region         = $request->request->get('region') !== null ? (string) $request->request->get('region') : null;
            $countryCode    = $request->request->get('country_code') !== null ? (string) $request->request->get('country_code') : null;
            $description    = trim((string) $request->request->get('description', ''));
            $isEu           = $request->request->getBoolean('featured');

            if (empty($name) || empty($tradeAgreement) || empty($region) || empty($countryCode)) {
                $this->addFlash('error', 'Tous les champs obligatoires doivent être remplis');
                return $this->redirectToRoute('app_market_update', ['id' => $market->getId()]);
            }

            if (!preg_match("/^[a-zA-ZÀ-ÿ\s]{3,50}$/", $name)) {
                $this->addFlash('error', 'Le titre du marché doit contenir uniquement des lettres (entre 3 et 50 caractères).');
                return $this->redirectToRoute('app_market_update', ['id' => $market->getId()]);
            }

            if (strlen($description) < 10) {
                $this->addFlash('error', 'La description est trop courte (minimum 10 caractères).');
                return $this->redirectToRoute('app_market_update', ['id' => $market->getId()]);
            }

            $market->setName($name);
            $market->setTradeAgreement($tradeAgreement);
            $market->setRegion($region);
            $market->setCountryCode($countryCode);
            $market->setDescription($description);
            $market->setIsEu($isEu);

            try {
                $em->flush();
                $this->addFlash('success', 'Le marché "' . $name . '" a été modifié avec succès !');
                return $this->redirectToRoute('app_admin_markets');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la sauvegarde.');
            }

            return $this->redirectToRoute('app_market_update', ['id' => $market->getId()]);
        }

        return $this->render('markets/updateMarket.html.twig', [
            'market' => $market
        ]);
    }

    #[Route('/marketUser/list', name: 'app_marketUser_list')]
    public function marketUserList(Request $request, MarketRepository $repo): Response
    {
        $limit = 5;
        $page  = max(1, (int) $request->query->get('page', 1));

        $totalEntries = $repo->countAll();
        $markets      = $repo->findPaginated($page, $limit);

        $from       = $totalEntries > 0 ? ($page - 1) * $limit + 1 : 0;
        $to         = min($page * $limit, $totalEntries);
        $totalPages = (int) ceil($totalEntries / $limit);

        return $this->render('market/userlist.html.twig', [
            'marketsUsers' => $markets,
            'totalEntries' => $totalEntries,
            'from'         => $from,
            'to'           => $to,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
        ]);
    }

    #[Route('/searchUser', name: 'searchUser')]
    public function searchUser(Request $request, MarketRepository $repo): Response
    {
        $name   = $request->query->get('name') !== null ? (string) $request->query->get('name') : null;
        $sortBy = $request->query->get('sort') !== null ? (string) $request->query->get('sort') : null;

        $list = $repo->searchAndSort($name, $sortBy);

        return $this->render('market/userlist.html.twig', [
            'marketsUsers' => $list,
            'totalEntries' => count($list),
            'from'         => count($list) > 0 ? 1 : 0,
            'to'           => count($list),
            'currentPage'  => 1,
            'totalPages'   => 1,
        ]);
    }

    #[Route('/searchUserOption', name: 'searchUserOption')]
    public function searchUserOption(Request $request, MarketRepository $repo): Response
    {
        $name   = $request->query->get('name') !== null ? (string) $request->query->get('name') : null;
        $sortBy = $request->query->get('sort') !== null ? (string) $request->query->get('sort') : null;

        $list = $repo->searchAndSort($name, $sortBy);

        return $this->render('market/userlist.html.twig', [
            'marketsUsers' => $list,
            'totalEntries' => count($list),
            'from'         => count($list) > 0 ? 1 : 0,
            'to'           => count($list),
            'currentPage'  => 1,
            'totalPages'   => 1,
        ]);
    }
}