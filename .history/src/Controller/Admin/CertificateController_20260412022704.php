<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;

#[Route('/admin/certificates')]
class CertificateController extends AbstractController
{
    use FormValidationTrait;

    #[Route('', name: 'app_admin_certificates')]
    public function index(CertificateRepository $repo): Response
    {
        return $this->render('admin/certificates/index.html.twig', [
            'certificates' => $repo->findAll(),
        ]);
    }

    #[Route('/add', name: 'app_admin_certificates_add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo
    ): Response {
        $certificate = new Certificate();

        if ($request->isMethod('POST')) {
            $this->clearValidationErrors();

            $certificateNumber = trim($request->request->get('certificate_number') ?? '');
            $type = trim($request->request->get('type') ?? '');
            $status = trim($request->request->get('status') ?? '');
            $countryOfOrigin = trim($request->request->get('country_of_origin') ?? '');
            $issuingAuthority = trim($request->request->get('issuing_authority') ?? '');
            $documentFile = trim($request->request->get('document_file') ?? '');
            $issueDate = $request->request->get('issue_date');
            $expiryDate = $request->request->get('expiry_date');
            $companyId = $request->request->get('company_id');

            // Validate required fields
            $this->validateAlphanumeric($certificateNumber, 'Certificate number', true);
            $this->validateRequired($type, 'Type', true);
            $this->validateRequired($status, 'Status', true);
            $this->validateName($countryOfOrigin, 'Country of origin', false);
            $this->validateName($issuingAuthority, 'Issuing authority', false);
            $this->validateUrl($documentFile, false);
            $this->validateDate($issueDate, 'Issue date', false);
            $this->validateDate($expiryDate, 'Expiry date', false);

            // Validate date range if both dates provided
            if ($issueDate && $expiryDate) {
                $this->validateDateRange($issueDate, $expiryDate);
            }

            if ($this->hasValidationErrors()) {
                return $this->render('admin/certificates/form.html.twig', [
                    'certificate' => $certificate,
                    'companies'   => $companyRepo->findAll(),
                    'mode'        => 'add',
                    'error'       => implode(' ', $this->getValidationErrors()),
                ]);
            }

            $this->handleForm($certificate, $request, $companyRepo);
            $certificate->setCreatedAt(new \DateTime());
            $certificate->setLastUpdated(new \DateTime());

            $em->persist($certificate);
            $em->flush();

            $this->addFlash('success', 'Certificate added successfully.');
            return $this->redirectToRoute('app_admin_certificates');
        }

        return $this->render('admin/certificates/form.html.twig', [
            'certificate' => $certificate,
            'companies'   => $companyRepo->findAll(),
            'mode'        => 'add',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_certificates_edit')]
    public function edit(
        Certificate $certificate,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $this->clearValidationErrors();

            $certificateNumber = trim($request->request->get('certificate_number') ?? '');
            $type = trim($request->request->get('type') ?? '');
            $status = trim($request->request->get('status') ?? '');
            $countryOfOrigin = trim($request->request->get('country_of_origin') ?? '');
            $issuingAuthority = trim($request->request->get('issuing_authority') ?? '');
            $documentFile = trim($request->request->get('document_file') ?? '');
            $issueDate = $request->request->get('issue_date');
            $expiryDate = $request->request->get('expiry_date');
            $companyId = $request->request->get('company_id');

            // Validate required fields
            $this->validateAlphanumeric($certificateNumber, 'Certificate number', true);
            $this->validateRequired($type, 'Type', true);
            $this->validateRequired($status, 'Status', true);
            $this->validateName($countryOfOrigin, 'Country of origin', false);
            $this->validateName($issuingAuthority, 'Issuing authority', false);
            $this->validateUrl($documentFile, false);
            $this->validateDate($issueDate, 'Issue date', false);
            $this->validateDate($expiryDate, 'Expiry date', false);

            // Validate date range if both dates provided
            if ($issueDate && $expiryDate) {
                $this->validateDateRange($issueDate, $expiryDate);
            }

            if ($this->hasValidationErrors()) {
                return $this->render('admin/certificates/form.html.twig', [
                    'certificate' => $certificate,
                    'companies'   => $companyRepo->findAll(),
                    'mode'        => 'edit',
                    'error'       => implode(' ', $this->getValidationErrors()),
                ]);
            }

            $this->handleForm($certificate, $request, $companyRepo);
            $certificate->setLastUpdated(new \DateTime());

            $em->flush();

            $this->addFlash('success', 'Certificate updated successfully.');
            return $this->redirectToRoute('app_admin_certificates');
        }

        return $this->render('admin/certificates/form.html.twig', [
            'certificate' => $certificate,
            'companies'   => $companyRepo->findAll(),
            'mode'        => 'edit',
        ]);
    }

    #[Route('/delete/{id}', name: 'app_admin_certificates_delete', methods: ['POST'])]
    public function delete(Certificate $certificate, EntityManagerInterface $em): Response
    {
        $em->remove($certificate);
        $em->flush();

        $this->addFlash('success', 'Certificate deleted.');
        return $this->redirectToRoute('app_admin_certificates');
    }

    #[Route('/{id}', name: 'app_admin_certificates_show')]
    public function show(Certificate $certificate): Response
    {
        return $this->render('admin/certificates/show.html.twig', [
            'certificate' => $certificate,
        ]);
    }

    #[Route('/{id}/signature/add', name: 'app_admin_certificates_signature_add', methods: ['POST'])]
    public function addSignature(
        Certificate $certificate,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $signature = new \App\Entity\Signature();
        $signature->setCertificate($certificate);
        $signature->setSignatoryName($request->request->get('signatory_name'));
        $signature->setSignatoryTitle($request->request->get('signatory_title'));
        $signature->setType($request->request->get('type'));
        $signature->setDigitalSignature($request->request->get('digital_signature'));
        $signature->setSignedDate(new \DateTime($request->request->get('signed_date')));

        $em->persist($signature);
        $em->flush();

        $this->addFlash('success', 'Signature added.');
        return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
    }

    #[Route('/{id}/signature/{signatureId}/delete', name: 'app_admin_certificates_signature_delete', methods: ['POST'])]
    public function deleteSignature(
        Certificate $certificate,
        int $signatureId,
        EntityManagerInterface $em
    ): Response {
        $signature = $em->getRepository(\App\Entity\Signature::class)->find($signatureId);

        if ($signature && $signature->getCertificate()->getId() === $certificate->getId()) {
            $em->remove($signature);
            $em->flush();
            $this->addFlash('success', 'Signature removed.');
        }

        return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
    }

    private function handleForm(
        Certificate $certificate,
        Request $request,
        CompanyRepository $companyRepo
    ): void {
        $certificate->setType($request->request->get('type'));
        $certificate->setCertificateNumber($request->request->get('certificate_number'));
        $certificate->setStatus($request->request->get('status'));
        $certificate->setCountryOfOrigin($request->request->get('country_of_origin'));
        $certificate->setIssuingAuthority($request->request->get('issuing_authority'));
        $certificate->setDocumentFile($request->request->get('document_file'));

        $issueDate = $request->request->get('issue_date');
        $certificate->setIssueDate($issueDate ? new \DateTime($issueDate) : null);

        $expiryDate = $request->request->get('expiry_date');
        $certificate->setExpiryDate($expiryDate ? new \DateTime($expiryDate) : null);

        $companyId = $request->request->get('company_id');
        $certificate->setCompany($companyId ? $companyRepo->find($companyId) : null);
    }
}