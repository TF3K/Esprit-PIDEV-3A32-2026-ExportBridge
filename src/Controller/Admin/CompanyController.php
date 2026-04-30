<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/companies')]
class CompanyController extends AbstractController
{
    #[Route('', name: 'app_admin_companies')]
    public function list(Request $request, CompanyRepository $repo): Response
    {
        $limit        = 5;
        $page         = max(1, (int) $request->query->get('page', 1));
        $totalEntries = $repo->countAll();
        $companies    = $repo->findPaginated($page, $limit);

        $from       = $totalEntries > 0 ? ($page - 1) * $limit + 1 : 0;
        $to         = min($page * $limit, $totalEntries);
        $totalPages = (int) ceil($totalEntries / $limit);

        return $this->render('admin/companies/listCompany.html.twig', [
            'companies'    => $companies,
            'totalEntries' => $totalEntries,
            'from'         => $from,
            'to'           => $to,
            'currentPage'  => $page,
            'totalPages'   => $totalPages,
        ]);
    }

    #[Route('/add', name: 'app_admin_companies_add')]
    public function add(Request $request, CompanyRepository $companyRepository): Response
    {
        $company = new Company();

        if ($request->isMethod('POST')) {
            $company->setCompanyName($request->request->get('company_name'));
            $company->setDomain($request->request->get('domain'));
            $company->setTaxNumber($request->request->get('tax_number'));
            $company->setRegistrationNumber($request->request->get('registration_number'));
            $company->setCountry($request->request->get('country'));
            $company->setAddress($request->request->get('address'));
            $company->setContactEmail($request->request->get('contact_email'));
            $company->setContactPhone($request->request->get('contact_phone'));
            $company->setRating($request->request->get('rating') ? (int) $request->request->get('rating') : null);
            $company->setWarnings(0);
            $company->setIsBanned(false);
            $company->setCreatedAt(new \DateTime());
            $company->setLastUpdated(new \DateTime());

            $companyRepository->save($company, true);

            $this->addFlash('success', 'Company added successfully.');
            return $this->redirectToRoute('app_admin_companies');
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode' => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_companies_edit')]
    public function edit(Company $company, Request $request, CompanyRepository $companyRepository): Response
    {
        if ($request->isMethod('POST')) {
            $company->setCompanyName($request->request->get('company_name'));
            $company->setDomain($request->request->get('domain'));
            $company->setTaxNumber($request->request->get('tax_number'));
            $company->setRegistrationNumber($request->request->get('registration_number'));
            $company->setCountry($request->request->get('country'));
            $company->setAddress($request->request->get('address'));
            $company->setContactEmail($request->request->get('contact_email'));
            $company->setContactPhone($request->request->get('contact_phone'));
            $company->setRating($request->request->get('rating') ? (int) $request->request->get('rating') : null);
            $company->setIsBanned((bool) $request->request->get('is_banned'));
            $company->setLastUpdated(new \DateTime());

            $companyRepository->save($company, true);

            $this->addFlash('success', 'Company updated successfully.');
            return $this->redirectToRoute('app_admin_companies');
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode' => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_companies_delete', methods: ['POST'])]
    public function delete(Company $company, CompanyRepository $companyRepository): Response
    {
        $companyRepository->remove($company, true);

        $this->addFlash('success', 'Company deleted.');
        return $this->redirectToRoute('app_admin_companies');
    }
}
