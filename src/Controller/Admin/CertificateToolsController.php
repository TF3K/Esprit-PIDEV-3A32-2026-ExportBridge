<?php

namespace App\Controller\Admin;

use App\Entity\Certificate;
use App\Service\CloudinaryUploadService;
use App\Service\PartnershipCertificateNotifier;
use App\Service\CertificatePdfFactory;
use App\Service\PublicQrUrlFactory;
use App\Service\QrCodeSvgGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/admin/certificates')]
class CertificateToolsController extends AbstractController
{
    #[Route('/{id}/send-expiry-email', name: 'app_admin_certificates_send_expiry_email', methods: ['POST'])]
    public function sendExpiryEmail(Certificate $certificate, PartnershipCertificateNotifier $notifier, MailerInterface $mailer): Response
    {
        if (!$certificate->getCompany()) {
            $this->addFlash('error', 'Certificate is not linked to a company.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        $message = sprintf(
            'Certificate %s (%s) is currently %s and expires on %s.',
            $certificate->getCertificateNumber(),
            $certificate->getType(),
            $certificate->getStatus(),
            $certificate->getExpiryDate() ? $certificate->getExpiryDate()->format('Y-m-d') : 'N/A'
        );

        // Keep the in-app notification only. Do not send to the company/manager email here,
        // because in testing that email may be invalid and can create Gmail delivery errors.
        $notifier->notifyCompany(
            $certificate->getCompany(),
            'certificate',
            'Certificate status update',
            $message,
            false
        );

        $user = $this->getUser();
        $accountEmail = null;

        if ($user && method_exists($user, 'getEmail') && $user->getEmail()) {
            $accountEmail = $user->getEmail();
        } elseif ($user && method_exists($user, 'getUserIdentifier')) {
            $accountEmail = $user->getUserIdentifier();
        }

        $fixedEmail = 'cheebishaima15@gmail.com';

        $recipients = array_values(array_unique(array_filter([
            $accountEmail,
            $fixedEmail,
        ])));

        $validRecipients = array_values(array_filter($recipients, static function (?string $email): bool {
            return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }));

        if (!$validRecipients) {
            $this->addFlash('error', 'No valid email recipient found.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        try {
            $mailer->send((new Email())
                ->from('cheebishaima15@gmail.com')
                ->to(...$validRecipients)
                ->subject('Certificate status update #' . $certificate->getId() . ' - ' . date('Y-m-d H:i:s'))
                ->text($message)
            );

            $this->addFlash('success', 'Email sent successfully to: ' . implode(', ', $validRecipients));
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Email not sent: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
    }

    #[Route('/{id}/upload-document', name: 'app_admin_certificates_upload_document', methods: ['POST'])]
    public function uploadDocument(Certificate $certificate, Request $request, CloudinaryUploadService $cloudinary, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('document_file');
        if (!$file) {
            $this->addFlash('error', 'Choose a file first.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        $result = $cloudinary->upload($file);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Upload failed.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        $certificate->setDocumentFile((string) ($result['url'] ?? ''));
        $certificate->setLastUpdated(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'Certificate document uploaded to Cloudinary.');

        return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
    }

    #[Route('/{id}/open-uploaded-pdf', name: 'app_admin_certificate_open_uploaded_pdf', methods: ['GET'])]
    public function openUploadedPdf(Certificate $certificate): Response
    {
        $file = $certificate->getDocumentFile();
        if (!$file) {
            $this->addFlash('error', 'No uploaded PDF found for this certificate.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        if (preg_match('#^https?://#i', $file)) {
            return $this->redirect($file);
        }

        $fullPath = $this->resolveUploadedPdfPath($file, 'certificates');
        if (!$fullPath) {
            $this->addFlash('error', 'Uploaded PDF file not found. Upload it again or copy it into public/uploads/certificates.');
            return $this->redirectToRoute('app_admin_certificates_show', ['id' => $certificate->getId()]);
        }

        return $this->file($fullPath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{id}/pdf', name: 'app_admin_certificates_pdf', methods: ['GET'])]
    public function pdf(Certificate $certificate, CertificatePdfFactory $certificatePdfFactory): Response
    {
        $pdf = $certificatePdfFactory->generate($certificate);

        return $this->pdfDownloadResponse(
            $pdf,
            $certificatePdfFactory->fileName($certificate)
        );
    }

    #[Route('/{id}/open-generated-pdf', name: 'app_admin_certificates_open_generated_pdf', methods: ['GET'])]
    public function openGeneratedPdf(Certificate $certificate, CertificatePdfFactory $certificatePdfFactory): Response
    {
        $pdf = $certificatePdfFactory->generate($certificate);

        return $this->pdfInlineResponse(
            $pdf,
            $certificatePdfFactory->fileName($certificate)
        );
    }

    #[Route('/{id}/qr', name: 'app_admin_certificates_qr', methods: ['GET'])]
    public function qr(Certificate $certificate, Request $request, QrCodeSvgGenerator $qrCodeSvgGenerator, PublicQrUrlFactory $publicQrUrlFactory): Response
    {
        $certificateImageUrl = $publicQrUrlFactory->absoluteUrl(
            $request,
            $this->generateUrl('app_public_certificate_image', ['id' => $certificate->getId()])
        );
        $qrImage = $qrCodeSvgGenerator->dataUri($certificateImageUrl);

        return $this->render('admin/certificates/qr.html.twig', [
            'certificate' => $certificate,
            'qr_image' => $qrImage,
        ]);
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

    private function safeFileName(string $value): string
    {
        $value = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value) ?? 'file';
        $value = trim($value, '-_.');

        return $value !== '' ? strtolower($value) : 'file';
    }
}
