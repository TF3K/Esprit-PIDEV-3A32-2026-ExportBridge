<?php

namespace App\Controller\User;

use App\Entity\Partnership;
use App\Entity\Company;
use App\Repository\PartnershipRepository;
use App\Service\PartnershipCertificateNotifier;
use App\Service\QrPayloadFactory;
use App\Service\QrCodeSvgGenerator;
use App\Service\SimplePdfGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/partnership')]
class UserPartnershipController extends AbstractController
{
    #[Route('', name: 'app_user_partnership')]
    public function index(PartnershipRepository $repo, EntityManagerInterface $em): Response
    {
        $company = $this->getOwnedCompany($em);
        $partnerships = $company ? $repo->findBy(['company' => $company], ['id' => 'DESC']) : [];

        return $this->render('user/partnership/index.html.twig', [
            'partnerships' => $partnerships,
            'company' => $company,
        ]);
    }

    #[Route('/new', name: 'app_user_partnership_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, PartnershipCertificateNotifier $notifier): Response
    {
        $company = $this->getOwnedCompany($em);
        if (!$company) {
            $this->addFlash('error', 'No company could be created for your account. Please contact the administrator.');
            return $this->redirectToRoute('app_user_partnership');
        }

        $partnership = new Partnership();
        $partnership->setCompany($company);
        if (method_exists($partnership, 'setTargetCompany')) {
            $partnership->setTargetCompany($company);
        }
        $partnership->setStatus('pending');
        $partnership->setCreatedAt(new \DateTime());
        $partnership->setLastUpdated(new \DateTime());

        if ($request->isMethod('POST')) {
            $this->fillPartnershipFromRequest($partnership, $request);

            if (!$partnership->getType()) {
                $this->addFlash('error', 'Type is required.');
            } elseif (!$partnership->getNotes()) {
                $this->addFlash('error', 'Notes are required.');
            } else {
                $em->persist($partnership);
                $em->flush();

                $notifier->notifyCompany(
                    $company,
                    'partnership',
                    'New partnership submitted successfully',
                    'A new partnership request has been submitted successfully and is waiting for admin approval.',
                    false
                );

                $this->addFlash('success', 'Partnership added successfully. It is waiting for admin approval.');
                return $this->redirectToRoute('app_user_partnership_show', ['id' => $partnership->getId()]);
            }
        }

        return $this->render('user/partnership/form.html.twig', [
            'mode' => 'add',
            'partnership' => $partnership,
            'company' => $company,
        ]);
    }

    #[Route('/{id}', name: 'app_user_partnership_show', methods: ['GET'])]
    public function show(Partnership $partnership): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);

        return $this->render('user/partnership/show.html.twig', [
            'partnership' => $partnership,
            'company' => $this->getOwnedCompany(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_partnership_edit', methods: ['GET', 'POST'])]
    public function edit(Partnership $partnership, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);

        if ($request->isMethod('POST')) {
            $this->fillPartnershipFromRequest($partnership, $request);
            $partnership->setLastUpdated(new \DateTime());

            if (!$partnership->getType()) {
                $this->addFlash('error', 'Type is required.');
            } elseif (!$partnership->getNotes()) {
                $this->addFlash('error', 'Notes are required.');
            } else {
                $em->flush();
                $this->addFlash('success', 'Partnership updated successfully.');
                return $this->redirectToRoute('app_user_partnership_show', ['id' => $partnership->getId()]);
            }
        }

        return $this->render('user/partnership/form.html.twig', [
            'mode' => 'edit',
            'partnership' => $partnership,
            'company' => $this->getOwnedCompany(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_user_partnership_delete', methods: ['POST'])]
    public function delete(Partnership $partnership, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);
        $em->remove($partnership);
        $em->flush();
        $this->addFlash('success', 'Partnership deleted successfully.');
        return $this->redirectToRoute('app_user_partnership');
    }

    #[Route('/{id}/pdf', name: 'app_user_partnership_pdf', methods: ['GET'])]
    public function pdf(Partnership $partnership, SimplePdfGenerator $pdfGenerator): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);

        $certificateRows = [];
        foreach ($partnership->getCertificates() as $certificate) {
            $certificateRows[] = [
                'label' => 'Certificate #' . $certificate->getId(),
                'value' => sprintf(
                    'Number: %s | Type: %s | Status: %s | Issue: %s | Expiry: %s',
                    $certificate->getCertificateNumber() ?: 'N/A',
                    $certificate->getType() ?: 'N/A',
                    ucfirst((string) ($certificate->getStatus() ?: 'N/A')),
                    $certificate->getIssueDate() ? $certificate->getIssueDate()->format('d/m/Y') : 'N/A',
                    $certificate->getExpiryDate() ? $certificate->getExpiryDate()->format('d/m/Y') : 'N/A'
                ),
            ];
        }

        $pdf = $pdfGenerator->generateDocument(
            'Partnership Details',
            'ExportBridge - generated from the user filled fields',
            [
                [
                    'title' => 'Partnership',
                    'rows' => [
                        ['label' => 'Partnership ID', 'value' => $partnership->getId()],
                        ['label' => 'Type', 'value' => $partnership->getType()],
                        ['label' => 'Status', 'value' => ucfirst((string) $partnership->getStatus())],
                        ['label' => 'Established Date', 'value' => $partnership->getEstablishedDate()],
                        ['label' => 'Terminated Date', 'value' => $partnership->getTerminatedDate()],
                        ['label' => 'Location', 'value' => $partnership->getLocationName() ?: 'N/A'],
                        ['label' => 'Latitude', 'value' => $partnership->getLocationLatitude()],
                        ['label' => 'Longitude', 'value' => $partnership->getLocationLongitude()],
                        ['label' => 'Created At', 'value' => $partnership->getCreatedAt()?->format('d/m/Y H:i')],
                        ['label' => 'Last Updated', 'value' => $partnership->getLastUpdated()?->format('d/m/Y H:i')],
                        ['label' => 'Uploaded PDF', 'value' => $partnership->getDocumentUrl() ?: 'No uploaded PDF'],
                        ['label' => 'Notes', 'value' => $partnership->getNotes()],
                    ],
                ],
                [
                    'title' => 'Company',
                    'rows' => $this->companyPdfRows($partnership->getCompany()),
                ],
                [
                    'title' => 'Linked Certificates',
                    'rows' => $certificateRows ?: [['label' => 'Certificates', 'value' => 'No linked certificates']],
                ],
            ],
            ['Generated on ' . (new \DateTime())->format('d/m/Y H:i'), 'ExportBridge user dashboard']
        );

        $fileKey = (string) ($partnership->getType() ?: ('partnership-' . $partnership->getId()));
        return $this->pdfDownloadResponse($pdf, 'partnership-' . $this->safeFileName($fileKey) . '-details.pdf');
    }

    #[Route('/{id}/open-uploaded-pdf', name: 'app_user_partnership_open_uploaded_pdf', methods: ['GET'])]
    public function openUploadedPdf(Partnership $partnership): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);
        $file = $partnership->getDocumentUrl();

        if (!$file) {
            $this->addFlash('error', 'No uploaded PDF found for this partnership.');
            return $this->redirectToRoute('app_user_partnership_show', ['id' => $partnership->getId()]);
        }

        if (preg_match('#^https?://#i', $file)) {
            return $this->redirect($file);
        }

        $fullPath = $this->resolveUploadedPdfPath($file, 'partnerships');
        if (!$fullPath) {
            $this->addFlash('error', 'Uploaded PDF file not found. Please upload the PDF again from Edit Partnership.');
            return $this->redirectToRoute('app_user_partnership_show', ['id' => $partnership->getId()]);
        }

        return $this->file($fullPath, null, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    #[Route('/{id}/qr', name: 'app_user_partnership_qr', methods: ['GET'])]
    public function qr(Partnership $partnership, QrCodeSvgGenerator $qrCodeSvgGenerator, QrPayloadFactory $qrPayloadFactory): Response
    {
        $this->denyAccessUnlessGrantedToPartnership($partnership);

        $qrPayload = $qrPayloadFactory->partnership($partnership);
        $qrImage = $qrCodeSvgGenerator->dataUri($qrPayload);

        return $this->render('user/partnership/qr.html.twig', [
            'partnership' => $partnership,
            'qr_image' => $qrImage,
        ]);
    }

    private function fillPartnershipFromRequest(Partnership $partnership, Request $request): void
    {
        $partnership->setType(trim((string) $request->request->get('type')) ?: null);
        $partnership->setNotes(trim((string) $request->request->get('notes')) ?: null);

        $establishedDate = $request->request->get('established_date');
        $terminatedDate = $request->request->get('terminated_date');
        $partnership->setEstablishedDate($establishedDate ? new \DateTime($establishedDate) : null);
        $partnership->setTerminatedDate($terminatedDate ? new \DateTime($terminatedDate) : null);

        $locationName = trim((string) $request->request->get('location_name')) ?: null;
        $latitude = $request->request->get('location_latitude');
        $longitude = $request->request->get('location_longitude');

        $partnership->setLocationName($locationName);
        $partnership->setLocationLatitude(is_numeric($latitude) ? (float) $latitude : null);
        $partnership->setLocationLongitude(is_numeric($longitude) ? (float) $longitude : null);
    }

    private function storePartnershipUpload(Partnership $partnership, Request $request): bool
    {
        $file = $request->files->get('document_file');
        if (!$file instanceof UploadedFile) {
            return true;
        }

        if (!$this->isPdfUpload($file)) {
            $this->addFlash('error', 'Please upload a valid PDF file.');
            return false;
        }

        $dir = $this->getParameter('kernel.project_dir') . '/public/uploads/partnerships';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        $safeBase = $this->safeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) ?: 'partnership');
        $fileName = $safeBase . '-' . uniqid('', true) . '.pdf';
        $file->move($dir, $fileName);
        $partnership->setDocumentUrl('uploads/partnerships/' . $fileName);

        return true;
    }

    private function resolveUploadedPdfPath(string $file, string $folder): ?string
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

        foreach ([$folder, 'certificates'] as $candidateFolder) {
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

    private function denyAccessUnlessGrantedToPartnership(Partnership $partnership): void
    {
        $company = $this->getOwnedCompany();
        if (!$company || $partnership->getCompany()?->getId() !== $company->getId()) {
            throw $this->createAccessDeniedException('This partnership does not belong to your company.');
        }
    }
}
