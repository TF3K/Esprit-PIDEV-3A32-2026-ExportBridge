<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Entity\Signature;
use App\Repository\CertificateRepository;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/certificates')]
class CertificateController extends AbstractController
{
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
        $signature = new Signature();
        $signature->setCertificate($certificate);
        $signature->setSignatoryName((string) $request->request->get('signatory_name', ''));
        $signature->setSignatoryTitle($request->request->get('signatory_title') !== null ? (string) $request->request->get('signatory_title') : null);
        $signature->setType((string) $request->request->get('type', ''));
        $signature->setDigitalSignature($request->request->get('digital_signature') !== null ? (string) $request->request->get('digital_signature') : null);
        $signature->setSignedDate(new \DateTime((string) $request->request->get('signed_date', 'now')));

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

        if ($signature && ($signatureCertificate = $signature->getCertificate()) && $signatureCertificate->getId() === $certificate->getId()) {
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
        $certificate->setType((string) $request->request->get('type', ''));
        $certificate->setCertificateNumber((string) $request->request->get('certificate_number', ''));
        $certificate->setStatus((string) $request->request->get('status', ''));
        $certificate->setCountryOfOrigin($request->request->get('country_of_origin') !== null ? (string) $request->request->get('country_of_origin') : null);
        $certificate->setIssuingAuthority($request->request->get('issuing_authority') !== null ? (string) $request->request->get('issuing_authority') : null);
        $certificate->setDocumentFile($request->request->get('document_file') !== null ? (string) $request->request->get('document_file') : null);

        $issueDate = $request->request->get('issue_date');
        $certificate->setIssueDate($issueDate !== null && $issueDate !== '' ? new \DateTime((string) $issueDate) : null);

        $expiryDate = $request->request->get('expiry_date');
        $certificate->setExpiryDate($expiryDate !== null && $expiryDate !== '' ? new \DateTime((string) $expiryDate) : null);

        $companyId = $request->request->get('company_id');
        $certificate->setCompany($companyId ? $companyRepo->find($companyId) : null);
    }
}
