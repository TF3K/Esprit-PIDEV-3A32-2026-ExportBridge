<?php

namespace App\Controller\Admin;

use App\Entity\Companie;
use App\Repository\CompanieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/companies')]
class CompanyController extends AbstractController
{
    #[Route('', name: 'app_admin_companies')]
    public function index(CompanieRepository $repo): Response
    {
        return $this->render('admin/companies/index.html.twig', [
            'companies' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_companies_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $company = new Companie();

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

            $em->persist($company);
            $em->flush();

            $this->addFlash('success', 'Company added successfully.');
            return $this->redirectToRoute('app_admin_companies');
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode' => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_companies_edit')]
    public function edit(Companie $company, Request $request, EntityManagerInterface $em): Response
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

            $em->flush();

            $this->addFlash('success', 'Company updated successfully.');
            return $this->redirectToRoute('app_admin_companies');
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode' => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_companies_delete', methods: ['POST'])]
    public function delete(Companie $company, EntityManagerInterface $em): Response
    {
        $em->remove($company);
        $em->flush();

        $this->addFlash('success', 'Company deleted.');
        return $this->redirectToRoute('app_admin_companies');
    }
}