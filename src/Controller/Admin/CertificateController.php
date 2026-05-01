<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Repository\CertificateRepository;
use App\Repository\CompanyRepository;
use App\Repository\PartnershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Traits\FormValidationTrait;
use App\Service\PartnershipCertificateNotifier;

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
        CompanyRepository $companyRepo,
        PartnershipRepository $partnershipRepo,
        PartnershipCertificateNotifier $notifier
    ): Response {
        $certificate = new Certificate();
        $errors      = [];
        $old         = [];

        $partnershipId = $request->query->get('partnershipId');
        if ($partnershipId && !$request->isMethod('POST')) {
            $partnership = $partnershipRepo->find($partnershipId);
            if ($partnership) {
                $certificate->setPartnership($partnership);
                if ($partnership->getCompany()) {
                    $certificate->setCompany($partnership->getCompany());
                }
            }
        }

        if ($request->isMethod('POST')) {
            [$errors, $old] = $this->validateCertificateForm($request);

            if (empty($errors)) {
                $this->applyToEntity($certificate, $request, $companyRepo, $partnershipRepo);
                $certificate->setCreatedAt(new \DateTime());
                $certificate->setLastUpdated(new \DateTime());

                $em->persist($certificate);
                $em->flush();

                if ($certificate->getCompany()) {
                    $notifier->notifyCompany($certificate->getCompany(), 'certificate', 'Certificate created', 'A new certificate ' . $certificate->getCertificateNumber() . ' has been created for your company.', false);
                }

                $this->addFlash('success', 'Certificate added successfully.');
                return $this->redirectToRoute('app_admin_certificates');
            }
        }

        return $this->render('admin/certificates/form.html.twig', [
            'certificate' => $certificate,
            'companies'   => $companyRepo->findAll(),
            'partnerships' => $partnershipRepo->findAll(),
            'mode'        => 'add',
            'errors'      => $errors,
            'old'         => $old,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_admin_certificates_edit')]
    public function edit(
        Certificate $certificate,
        Request $request,
        EntityManagerInterface $em,
        CompanyRepository $companyRepo,
        PartnershipRepository $partnershipRepo,
        PartnershipCertificateNotifier $notifier
    ): Response {
        $errors = [];
        $old    = [];

        if ($request->isMethod('POST')) {
            [$errors, $old] = $this->validateCertificateForm($request);

            if (empty($errors)) {
                $this->applyToEntity($certificate, $request, $companyRepo, $partnershipRepo);
                $certificate->setLastUpdated(new \DateTime());

                $em->flush();

                if ($certificate->getCompany()) {
                    $notifier->notifyCompany($certificate->getCompany(), 'certificate', 'Certificate updated', 'Certificate ' . $certificate->getCertificateNumber() . ' has been updated. Current status: ' . $certificate->getStatus() . '.', false);
                }

                $this->addFlash('success', 'Certificate updated successfully.');
                return $this->redirectToRoute('app_admin_certificates');
            }
        }

        return $this->render('admin/certificates/form.html.twig', [
            'certificate' => $certificate,
            'companies'   => $companyRepo->findAll(),
            'partnerships' => $partnershipRepo->findAll(),
            'mode'        => 'edit',
            'errors'      => $errors,
            'old'         => $old,
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

    // Extracted since add and edit share identical validation logic
    private function validateCertificateForm(Request $request): array
    {
        $certificateNumber = trim($request->request->get('certificate_number') ?? '');
        $type              = trim($request->request->get('type') ?? '');
        $status            = trim($request->request->get('status') ?? '');
        $countryOfOrigin   = trim($request->request->get('country_of_origin') ?? '');
        $issuingAuthority  = trim($request->request->get('issuing_authority') ?? '');
        $documentFile      = trim((string) ($request->request->get('document_file') ?? ''));
        $issueDate         = $request->request->get('issue_date');
        $expiryDate        = $request->request->get('expiry_date');
        $companyId         = $request->request->get('company_id');
        $partnershipId     = $request->request->get('partnership_id');

        $old = compact(
            'certificateNumber', 'type', 'status', 'countryOfOrigin',
            'issuingAuthority', 'documentFile', 'issueDate', 'expiryDate', 'companyId', 'partnershipId'
        );

        $errors = [];
        $this->clearValidationErrors();

        $this->validateAlphanumeric($certificateNumber, 'Certificate number', true);
        if ($this->hasValidationErrors()) {
            $errors['certificate_number'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateRequired($type, 'Type', 1);
        if ($this->hasValidationErrors()) {
            $errors['type'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateRequired($status, 'Status', 1);
        if ($this->hasValidationErrors()) {
            $errors['status'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateName($countryOfOrigin, 'Country of origin', false);
        if ($this->hasValidationErrors()) {
            $errors['country_of_origin'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateName($issuingAuthority, 'Issuing authority', false);
        if ($this->hasValidationErrors()) {
            $errors['issuing_authority'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateUrl($documentFile, false);
        if ($this->hasValidationErrors()) {
            $errors['document_file'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateDate($issueDate, 'Issue date', false);
        if ($this->hasValidationErrors()) {
            $errors['issue_date'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateDate($expiryDate, 'Expiry date', false);
        if ($this->hasValidationErrors()) {
            $errors['expiry_date'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        $this->validateRequired($companyId, 'Company', 1);
        if ($this->hasValidationErrors()) {
            $errors['company_id'] = $this->getFirstValidationError();
            $this->clearValidationErrors();
        }

        if (!isset($errors['issue_date']) && !isset($errors['expiry_date'])
            && $issueDate && $expiryDate) {
            $this->validateDateRange($issueDate, $expiryDate);
            if ($this->hasValidationErrors()) {
                $errors['expiry_date'] = $this->getFirstValidationError();
                $this->clearValidationErrors();
            }
        }

        return [$errors, $old];
    }

    private function applyToEntity(
        Certificate $certificate,
        Request $request,
        CompanyRepository $companyRepo,
        PartnershipRepository $partnershipRepo
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

        $partnershipId = $request->request->get('partnership_id');
        $partnership = $partnershipId ? $partnershipRepo->find($partnershipId) : null;
        $certificate->setPartnership($partnership);

        if ($partnership && $partnership->getCompany()) {
            $certificate->setCompany($partnership->getCompany());
        }
    }
}
