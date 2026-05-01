<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/companies')]
class CompanyController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_companies')]
    public function index(CompanyRepository $repo): Response
    {
        return $this->render('admin/companies/index.html.twig', [
            'companies' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_companies_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $company = new Company();
        $errors  = [];
        $old     = [];

        if ($request->isMethod('POST')) {
            $companyName        = trim($request->request->get('company_name') ?? '');
            $domain             = trim($request->request->get('domain') ?? '');
            $taxNumber          = trim($request->request->get('tax_number') ?? '');
            $registrationNumber = trim($request->request->get('registration_number') ?? '');
            $country            = trim($request->request->get('country') ?? '');
            $address            = trim($request->request->get('address') ?? '');
            $contactEmail       = trim($request->request->get('contact_email') ?? '');
            $contactPhone       = trim($request->request->get('contact_phone') ?? '');
            $rating             = $request->request->get('rating');

            $old = compact(
                'companyName', 'domain', 'taxNumber', 'registrationNumber',
                'country', 'address', 'contactEmail', 'contactPhone', 'rating'
            );

            $this->clearValidationErrors();

            $this->validateName($companyName, 'Company name', true);
            if ($this->hasValidationErrors()) {
                $errors['company_name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateEmail($contactEmail, 'Contact email', false);
            if ($this->hasValidationErrors()) {
                $errors['contact_email'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validatePhone($contactPhone, false);
            if ($this->hasValidationErrors()) {
                $errors['contact_phone'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateName($country, 'Country', false);
            if ($this->hasValidationErrors()) {
                $errors['country'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($domain, 'Domain', false);
            if ($this->hasValidationErrors()) {
                $errors['domain'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($taxNumber, 'Tax number', false);
            if ($this->hasValidationErrors()) {
                $errors['tax_number'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($registrationNumber, 'Registration number', false);
            if ($this->hasValidationErrors()) {
                $errors['registration_number'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if ($rating !== null && $rating !== '') {
                $this->validateNumber($rating, 'Rating', false, 1, 5);
                if ($this->hasValidationErrors()) {
                    $errors['rating'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if (empty($errors)) {
                $company->setCompanyName($companyName);
                $company->setDomain($domain);
                $company->setTaxNumber($taxNumber);
                $company->setRegistrationNumber($registrationNumber);
                $company->setCountry($country);
                $company->setAddress($address);
                $company->setContactEmail($contactEmail);
                $company->setContactPhone($contactPhone);
                $company->setRating($rating ? (int) $rating : null);
                $company->setWarnings(0);
                $company->setIsBanned(false);
                $company->setCreatedAt(new \DateTime());
                $company->setLastUpdated(new \DateTime());

                $em->persist($company);
                $em->flush();

                $this->addFlash('success', 'Company added successfully.');
                return $this->redirectToRoute('app_admin_companies');
            }
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode'    => 'add',
            'errors'  => $errors,
            'old'     => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_companies_edit')]
    public function edit(Company $company, Request $request, EntityManagerInterface $em): Response
    {
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            $companyName        = trim($request->request->get('company_name') ?? '');
            $domain             = trim($request->request->get('domain') ?? '');
            $taxNumber          = trim($request->request->get('tax_number') ?? '');
            $registrationNumber = trim($request->request->get('registration_number') ?? '');
            $country            = trim($request->request->get('country') ?? '');
            $address            = trim($request->request->get('address') ?? '');
            $contactEmail       = trim($request->request->get('contact_email') ?? '');
            $contactPhone       = trim($request->request->get('contact_phone') ?? '');
            $rating             = $request->request->get('rating');

            $old = compact(
                'companyName', 'domain', 'taxNumber', 'registrationNumber',
                'country', 'address', 'contactEmail', 'contactPhone', 'rating'
            );

            $this->clearValidationErrors();

            $this->validateName($companyName, 'Company name', true);
            if ($this->hasValidationErrors()) {
                $errors['company_name'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateEmail($contactEmail, 'Contact email', false);
            if ($this->hasValidationErrors()) {
                $errors['contact_email'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validatePhone($contactPhone, false);
            if ($this->hasValidationErrors()) {
                $errors['contact_phone'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateName($country, 'Country', false);
            if ($this->hasValidationErrors()) {
                $errors['country'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($domain, 'Domain', false);
            if ($this->hasValidationErrors()) {
                $errors['domain'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($taxNumber, 'Tax number', false);
            if ($this->hasValidationErrors()) {
                $errors['tax_number'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            $this->validateAlphanumeric($registrationNumber, 'Registration number', false);
            if ($this->hasValidationErrors()) {
                $errors['registration_number'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }

            if ($rating !== null && $rating !== '') {
                $this->validateNumber($rating, 'Rating', false, 1, 5);
                if ($this->hasValidationErrors()) {
                    $errors['rating'] = $this->getFirstValidationError();
                    $this->clearValidationErrors();
                }
            }

            if (empty($errors)) {
                $company->setCompanyName($companyName);
                $company->setDomain($domain);
                $company->setTaxNumber($taxNumber);
                $company->setRegistrationNumber($registrationNumber);
                $company->setCountry($country);
                $company->setAddress($address);
                $company->setContactEmail($contactEmail);
                $company->setContactPhone($contactPhone);
                $company->setRating($rating ? (int) $rating : null);
                $company->setIsBanned((bool) $request->request->get('is_banned'));
                $company->setLastUpdated(new \DateTime());

                $em->flush();

                $this->addFlash('success', 'Company updated successfully.');
                return $this->redirectToRoute('app_admin_companies');
            }
        }

        return $this->render('admin/companies/form.html.twig', [
            'company' => $company,
            'mode'    => 'edit',
            'errors'  => $errors,
            'old'     => $old,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_companies_delete', methods: ['POST'])]
    public function delete(Company $company, EntityManagerInterface $em): Response
    {
        $em->remove($company);
        $em->flush();

        $this->addFlash('success', 'Company deleted.');
        return $this->redirectToRoute('app_admin_companies');
    }
}