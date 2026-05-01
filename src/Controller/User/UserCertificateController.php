<?php

namespace App\Controller\User;

use App\Entity\Certificate;
use App\Entity\Company;
use App\Repository\CertificateRepository;
use App\Repository\PartnershipRepository;
use App\Service\PartnershipCertificateNotifier;
use App\Service\AiCertificateExtractorService;
use App\Service\CertificatePdfFactory;
use App\Service\PublicQrUrlFactory;
use App\Service\QrCodeSvgGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/certificates')]
class UserCertificateController extends AbstractController
{
    #[Route('', name: 'app_user_certificates')]
    public function index(CertificateRepository $certRepo, EntityManagerInterface $em): Response
    {
        $company = $this->getOwnedCompany($em);
        $certificates = $company ? $certRepo->findBy(['company' => $company], ['created_at' => 'DESC']) : [];

        return $this->render('user/certificates/index.html.twig', [
            'certificates' => $certificates,
            'company' => $company,
        ]);
    }

    #[Route('/new', name: 'app_user_certificate_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, PartnershipCertificateNotifier $notifier, PartnershipRepository $partnershipRepository): Response
    {
        $company = $this->getOwnedCompany($em);
        if (!$company) {
            $this->addFlash('error', 'No company could be created for your account. Please contact the administrator.');
            return $this->redirectToRoute('app_user_certificates');
        }

        $certificate = new Certificate();

        $partnershipId = $request->query->get('partnershipId');
        if ($partnershipId) {
            $partnership = $partnershipRepository->find($partnershipId);
            if ($partnership && $partnership->getCompany()?->getId() === $company->getId() && $partnership->getStatus() === 'active') {
                $certificate->setPartnership($partnership);
            }
        }

        if ($request->isMethod('POST')) {
            $certificate->setCompany($company);
            $certificate->setCertificateNumber(trim((string) $request->request->get('certificate_number')));
            $certificate->setType(trim((string) $request->request->get('type')));
            $certificate->setStatus('pending');
            $certificate->setCountryOfOrigin(trim((string) $request->request->get('country_of_origin')) ?: null);
            $certificate->setIssuingAuthority(trim((string) $request->request->get('issuing_authority')) ?: null);
            $certificate->setCreatedAt(new \DateTime());
            $certificate->setLastUpdated(new \DateTime());

            $issueDate = $request->request->get('issue_date');
            $expiryDate = $request->request->get('expiry_date');
            $certificate->setIssueDate($issueDate ? new \DateTime($issueDate) : null);
            $certificate->setExpiryDate($expiryDate ? new \DateTime($expiryDate) : null);

            $uploadOk = $this->storeCertificateUpload($certificate, $request);

            if (!$certificate->getCertificateNumber() || !$certificate->getType()) {
                $this->addFlash('error', 'Certificate number and type are required.');
            } elseif ($uploadOk) {
                $em->persist($certificate);
                $em->flush();

                $notifier->notifyCompany(
                    $company,
                    'certificate',
                    'New certificate submitted successfully',
                    'A new certificate ' . $certificate->getCertificateNumber() . ' has been submitted successfully.',
                    false
                );

                $this->addFlash('success', 'Certificate added successfully.');
                return $this->redirectToRoute('app_user_certificate_show', ['id' => $certificate->getId()]);
            }
        }

        return $this->render('user/certificates/form.html.twig', [
            'mode' => 'add',
            'certificate' => $certificate,
            'company' => $company,
        ]);
    }

    #[Route('/ai-extract', name: 'app_user_certificate_ai_extract', methods: ['POST'])]
    public function aiExtract(Request $request, AiCertificateExtractorService $extractor): JsonResponse
    {
        $file = $request->files->get('ai_certificate_file') ?: $request->files->get('document_file');

        if (!$file instanceof UploadedFile) {
            return $this->json([
                'success' => false,
                'message' => 'Please choose a certificate PDF first.',
                'fields' => [],
                'confidence' => 0,
                'warnings' => ['Please choose a certificate PDF first.'],
            ], 400);
        }

        if (!$this->isPdfUpload($file)) {
            return $this->json([
                'success' => false,
                'message' => 'Please upload a valid PDF file.',
                'fields' => [],
                'confidence' => 0,
                'warnings' => ['Please upload a valid PDF file.'],
            ], 400);
        }

        $result = $extractor->extractFromPdf($file);

        return $this->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    #[Route('/{id}', name: 'app_user_certificate_show', methods: ['GET'])]
    public function show(Certificate $certificate): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);

        return $this->render('user/certificates/show.html.twig', [
            'certificate' => $certificate,
            'company' => $this->getOwnedCompany(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_certificate_edit', methods: ['GET', 'POST'])]
    public function edit(Certificate $certificate, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);

        if ($request->isMethod('POST')) {
            $certificate->setCertificateNumber(trim((string) $request->request->get('certificate_number')));
            $certificate->setType(trim((string) $request->request->get('type')));
            $certificate->setCountryOfOrigin(trim((string) $request->request->get('country_of_origin')) ?: null);
            $certificate->setIssuingAuthority(trim((string) $request->request->get('issuing_authority')) ?: null);
            $certificate->setLastUpdated(new \DateTime());

            $issueDate = $request->request->get('issue_date');
            $expiryDate = $request->request->get('expiry_date');
            $certificate->setIssueDate($issueDate ? new \DateTime($issueDate) : null);
            $certificate->setExpiryDate($expiryDate ? new \DateTime($expiryDate) : null);

            $uploadOk = $this->storeCertificateUpload($certificate, $request);

            if (!$certificate->getCertificateNumber() || !$certificate->getType()) {
                $this->addFlash('error', 'Certificate number and type are required.');
            } elseif ($uploadOk) {
                $em->flush();
                $this->addFlash('success', 'Certificate updated successfully.');
                return $this->redirectToRoute('app_user_certificate_show', ['id' => $certificate->getId()]);
            }
        }

        return $this->render('user/certificates/form.html.twig', [
            'mode' => 'edit',
            'certificate' => $certificate,
            'company' => $this->getOwnedCompany(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_certificate_delete', methods: ['POST'])]
    public function delete(Certificate $certificate, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);
        $em->remove($certificate);
        $em->flush();
        $this->addFlash('success', 'Certificate deleted.');
        return $this->redirectToRoute('app_user_certificates');
    }

    #[Route('/{id}/pdf', name: 'app_user_certificate_pdf', methods: ['GET'])]
    public function pdf(Certificate $certificate, CertificatePdfFactory $certificatePdfFactory): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);

        $pdf = $certificatePdfFactory->generate($certificate);

        return $this->pdfDownloadResponse($pdf, $certificatePdfFactory->fileName($certificate));
    }

    #[Route('/{id}/open-generated-pdf', name: 'app_user_certificate_open_generated_pdf', methods: ['GET'])]
    public function openGeneratedPdf(Certificate $certificate, CertificatePdfFactory $certificatePdfFactory): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);

        $pdf = $certificatePdfFactory->generate($certificate);

        return $this->pdfInlineResponse($pdf, $certificatePdfFactory->fileName($certificate));
    }

    #[Route('/{id}/open-uploaded-pdf', name: 'app_user_certificate_open_uploaded_pdf', methods: ['GET'])]
    public function openUploadedPdf(Certificate $certificate): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);
        $file = $certificate->getDocumentFile();

        if (!$file) {
            $this->addFlash('error', 'No uploaded PDF found for this certificate.');
            return $this->redirectToRoute('app_user_certificate_show', ['id' => $certificate->getId()]);
        }

        if (preg_match('#^https?://#i', $file)) {
            return $this->redirect($file);
        }

        $fullPath = $this->resolveUploadedPdfPath($file, 'certificates');
        if (!$fullPath) {
            $this->addFlash('error', 'Uploaded PDF file not found. Please upload the PDF again from Edit Certificate.');
            return $this->redirectToRoute('app_user_certificate_show', ['id' => $certificate->getId()]);
        }

        return $this->file($fullPath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{id}/qr', name: 'app_user_certificate_qr', methods: ['GET'])]
    public function qr(Certificate $certificate, Request $request, QrCodeSvgGenerator $qrCodeSvgGenerator, PublicQrUrlFactory $publicQrUrlFactory): Response
    {
        $this->denyAccessUnlessGrantedToCertificate($certificate);

        $certificateImageUrl = $publicQrUrlFactory->absoluteUrl(
            $request,
            $this->generateUrl('app_public_certificate_image', ['id' => $certificate->getId()])
        );
        $qrImage = $qrCodeSvgGenerator->dataUri($certificateImageUrl);

        return $this->render('user/certificates/qr.html.twig', [
            'certificate' => $certificate,
            'qr_image' => $qrImage,
        ]);
    }

    private function storeCertificateUpload(Certificate $certificate, Request $request): bool
    {
        $file = $request->files->get('document_file');
        if (!$file instanceof UploadedFile) {
            return true;
        }

        if (!$this->isPdfUpload($file)) {
            $this->addFlash('error', 'Please upload a valid PDF file.');
            return false;
        }

        $dir = $this->getParameter('kernel.project_dir') . '/public/uploads/certificates';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $safeBase = $this->safeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'certificate');
        $fileName = $safeBase . '-' . uniqid('', true) . '.pdf';
        $file->move($dir, $fileName);
        $certificate->setDocumentFile('uploads/certificates/' . $fileName);

        return true;
    }

    private function resolveUploadedPdfPath(string $file, string $folder = 'certificates'): ?string
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        $clean = ltrim(str_replace('\\', '/', $file), '/');

        $candidates = [
            $projectDir . '/public/' . $clean,
            $projectDir . '/public/uploads/' . $folder . '/' . basename($clean),
            $projectDir . '/public/uploads/certificates/' . basename($clean),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        foreach (['certificates', $folder] as $candidateFolder) {
            $matches = glob($projectDir . '/public/uploads/' . $candidateFolder . '/*' . basename($clean));
            if ($matches) {
                foreach ($matches as $match) {
                    if (is_file($match)) {
                        return $match;
                    }
                }
            }
        }

        return null;
    }

    private function pdfDownloadResponse(string $pdf, string $fileName): Response
    {
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName),
            'Content-Length' => (string) strlen($pdf),
        ]);
    }

    private function pdfInlineResponse(string $pdf, string $fileName): Response
    {
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName),
            'Content-Length' => (string) strlen($pdf),
        ]);
    }

    private function companyPdfRows($company): array
    {
        if (!$company) {
            return [['label' => 'Company', 'value' => 'N/A']];
        }

        return [
            ['label' => 'Company ID', 'value' => $company->getId()],
            ['label' => 'Name', 'value' => $company->getCompanyName()],
            ['label' => 'Domain', 'value' => $company->getDomain()],
            ['label' => 'Tax Number', 'value' => $company->getTaxNumber()],
            ['label' => 'Registration Number', 'value' => $company->getRegistrationNumber()],
            ['label' => 'Country', 'value' => $company->getCountry()],
            ['label' => 'Address', 'value' => $company->getAddress()],
            ['label' => 'Contact Email', 'value' => $company->getContactEmail()],
            ['label' => 'Contact Phone', 'value' => $company->getContactPhone()],
        ];
    }

    private function isPdfUpload(UploadedFile $file): bool
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        return $extension === 'pdf' || in_array($mimeType, ['application/pdf', 'application/x-pdf'], true);
    }

    private function safeFileName(string $value): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? 'file';
        $value = trim($value, '-_.');

        return $value !== '' ? strtolower($value) : 'file';
    }

    private function getOwnedCompany(?EntityManagerInterface $em = null)
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        if (method_exists($user, 'getCompany') && $user->getCompany()) {
            return $user->getCompany();
        }

        if (method_exists($user, 'getCompanies')) {
            foreach ($user->getCompanies() as $company) {
                if ($company) {
                    if (method_exists($user, 'setCompany')) {
                        $user->setCompany($company);
                        if ($em) {
                            $em->flush();
                        }
                    }
                    return $company;
                }
            }
        }

        if (!$em || !method_exists($user, 'setCompany')) {
            return null;
        }

        $companyName = trim((string) (($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')));
        if ($companyName === '') {
            $companyName = 'My Company';
        }

        $company = new Company();
        $company->setCompanyName($companyName . ' Company');
        $company->setCountry('Tunisia');
        $company->setContactEmail($user->getEmail());
        $company->setWarnings(0);
        $company->setIsBanned(false);
        $company->setManager($user);
        $company->setCreatedAt(new \DateTime());
        $company->setLastUpdated(new \DateTime());
        $user->setCompany($company);

        $em->persist($company);
        $em->persist($user);
        $em->flush();

        return $company;
    }

    private function denyAccessUnlessGrantedToCertificate(Certificate $certificate): void
    {
        $company = $this->getOwnedCompany();
        if (!$company || $certificate->getCompany()?->getId() !== $company->getId()) {
            throw $this->createAccessDeniedException('This certificate does not belong to your company.');
        }
    }
}
