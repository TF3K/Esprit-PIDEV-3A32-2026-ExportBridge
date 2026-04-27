<?php

namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use App\Repository\MarketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Dompdf\Dompdf;
use Dompdf\Options;

class CompanyController extends AbstractController
{
    #[Route('/company/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CompanyRepository $companyRepository, MarketRepository $marketRepository, HttpClientInterface $httpClient): Response
    {
        $markets = $marketRepository->findAll();

        if ($request->isMethod('POST')) {
            // ✅ cast explicite en string pour éviter mixed
            $companyName = (string) $request->request->get('company_name', '');
            $marketName  = (string) $request->request->get('market_name', '');
            $domain      = (string) $request->request->get('domain', '');
            $email       = (string) $request->request->get('email', '');
            $country     = (string) $request->request->get('country', '');
            $address     = (string) $request->request->get('address', '');

            if (!$companyName || !$marketName || !$email || !$country || !$address) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                return $this->redirectToRoute('app_company_new');
            }

            try {
                $aiResponse = $httpClient->request('POST', 'http://127.0.0.1:3000/api/validate-company', [
                    'json' => [
                        'company_name' => $companyName,
                        'market_name'  => $marketName,
                        'email'        => $email,
                        'domain'       => $domain,
                    ]
                ]);

                $aiResult = $aiResponse->toArray();

                if (isset($aiResult['status']) && $aiResult['status'] === 'reject') {
                    $reason = $aiResult['reason'] ?? 'Non conforme aux critères de Sikipon';
                    $this->addFlash('error', "Sikipon AI a refusé l'inscription : " . $reason);
                    return $this->redirectToRoute('app_company_new');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', "Erreur de communication avec Sikipon AI.");
                return $this->redirectToRoute('app_company_new');
            }

            $contractHash = '';
            try {
                $bcResponse = $httpClient->request('POST', 'http://127.0.0.1:5000/api/sign-contract', [
                    'json' => [
                        'company_name' => $companyName,
                        'market_name'  => $marketName,
                        'email'        => $email,
                        'domain'       => $domain,
                    ]
                ]);

                $bcResult     = $bcResponse->toArray();
                $contractHash = (string) ($bcResult['contract_hash'] ?? '');
            } catch (\Exception $e) {
                $this->addFlash('error', "Échec de la sécurisation Blockchain.");
                return $this->redirectToRoute('app_company_new');
            }

            $company = new Company();
            $company->setCompanyName($companyName);
            // ✅ FIXED level 7: strlen() au lieu de ?: pour éviter "ternary always true"
            // après un cast (string), la valeur est toujours un string donc ?: null est toujours true
            $company->setDomain(strlen($domain) > 0 ? $domain : null);
            $company->setContactEmail(strlen($email) > 0 ? $email : null);
            $company->setCountry(strlen($country) > 0 ? $country : null);
            $company->setAddress(strlen($address) > 0 ? $address : null);
            $company->setContractHash(strlen($contractHash) > 0 ? $contractHash : null);

            $market = $marketRepository->findOneBy(['name' => $marketName]);
            if ($market) {
                $company->setMarket($market);
            }

            $now = new \DateTime();
            $company->setCreatedAt($now);
            $company->setLastUpdated($now);

            $companyRepository->save($company, true);

            $this->addFlash('success', "Entreprise validée par l'IA et scellée sur la Blockchain.");
            $this->addFlash('info', "Hash : " . $contractHash);

            return $this->redirectToRoute('app_company_list');
        }

        return $this->render('company/new.html.twig', [
            'markets' => $markets
        ]);
    }

    #[Route('/company/list', name: 'app_company_list')]
    public function list(Request $request, CompanyRepository $repo): Response
    {
        $limit        = 5;
        $page         = max(1, (int) $request->query->get('page', 1));
        $totalEntries = $repo->countAll();
        $companies    = $repo->findPaginated($page, $limit);

        $from       = $totalEntries > 0 ? ($page - 1) * $limit + 1 : 0;
        $to         = min($page * $limit, $totalEntries);
        $totalPages = (int) ceil($totalEntries / $limit);

        return $this->render('company/listCompany.html.twig', [
            'companies'    => $companies,
            'totalEntries' => $totalEntries,
            'from'         => $from,
            'to'           => $to,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
        ]);
    }

    #[Route('/company/search', name: 'app_company_search')]
    public function search(Request $request, CompanyRepository $repo): Response
    {
        $name   = $request->query->get('name') !== null ? (string) $request->query->get('name') : null;
        $sortBy = (string) $request->query->get('sort', 'id');

        $list = $repo->searchAndSort($name, $sortBy);

        return $this->render('company/listCompany.html.twig', [
            'companies'    => $list,
            'totalEntries' => count($list),
            'from'         => count($list) > 0 ? 1 : 0,
            'to'           => count($list),
            'currentPage'  => 1,
            'totalPages'   => 1,
        ]);
    }

    #[Route('/company/{id}/show', name: 'app_company_show')]
    public function show(int $id, CompanyRepository $repo): Response
    {
        $company = $repo->find($id);

        if (!$company) {
            throw $this->createNotFoundException('Company not found');
        }

        $url = $this->generateUrl(
            'app_company_show',
            ['id' => $company->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            size: 150,
            margin: 10
        );

        $writer        = new SvgWriter();
        $result        = $writer->write($qrCode);
        $qrCodeDataUri = $result->getDataUri();

        return $this->render('company/show.html.twig', [
            'company' => $company,
            'qrCode'  => $qrCodeDataUri,
        ]);
    }

    #[Route('/company/{id}/pdf', name: 'app_company_pdf')]
    public function pdf(int $id, CompanyRepository $repo): Response
    {
        $company = $repo->find($id);

        if (!$company) {
            throw $this->createNotFoundException('Company not found');
        }

        $url = $this->generateUrl(
            'app_company_show',
            ['id' => $company->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            size: 150,
            margin: 10
        );

        $writer        = new SvgWriter();
        $result        = $writer->write($qrCode);
        $qrCodeDataUri = $result->getDataUri();

        $html = $this->renderView('company/pdf.html.twig', [
            'company' => $company,
            'qrCode'  => $qrCodeDataUri,
        ]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // ✅ FIXED level 7: output() retourne toujours string après render()
        // suppression du ?? '' qui causait "variable always exists and is not nullable"
        $pdfContent = (string) $dompdf->output();

        return new Response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="certificat-' . $company->getId() . '.pdf"',
        ]);
    }
}
