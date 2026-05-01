<?php

namespace App\Controller\Admin;

use App\Entity\Partnership;
use App\Service\CloudinaryUploadService;
use App\Service\PartnershipCertificateNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/partnerships')]
class PartnershipToolsController extends AbstractController
{
    #[Route('/{id}/send-status-email', name: 'app_admin_partnerships_send_status_email', methods: ['POST'])]
    public function sendStatusEmail(Partnership $partnership, PartnershipCertificateNotifier $notifier): Response
    {
        if (!$partnership->getCompany()) {
            $this->addFlash('error', 'Partnership is not linked to a company.');
            return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
        }

        $message = sprintf(
            'Your partnership is currently %s. Type: %s. Established date: %s.',
            $partnership->getStatus(),
            $partnership->getType() ?? 'N/A',
            $partnership->getEstablishedDate() ? $partnership->getEstablishedDate()->format('Y-m-d') : 'N/A'
        );
        $notifier->notifyCompany($partnership->getCompany(), 'partnership', 'Partnership status update', $message, true);
        $this->addFlash('success', 'Partnership email sent and notifications created.');

        return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
    }

    #[Route('/{id}/upload-document', name: 'app_admin_partnerships_upload_document', methods: ['POST'])]
    public function uploadDocument(Partnership $partnership, Request $request, CloudinaryUploadService $cloudinary, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('document_file');
        if (!$file) {
            $this->addFlash('error', 'Choose a file first.');
            return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
        }

        $result = $cloudinary->upload($file);
        if (!$result['ok']) {
            $this->addFlash('error', $result['message'] ?? 'Upload failed.');
            return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
        }

        $partnership->setDocumentUrl((string) ($result['url'] ?? ''));
        $partnership->setLastUpdated(new \DateTime());
        $em->flush();
        $this->addFlash('success', 'Partnership document uploaded to Cloudinary.');

        return $this->redirectToRoute('app_admin_partnerships_show', ['id' => $partnership->getId()]);
    }

    #[Route('/{id}/pdf', name: 'app_admin_partnerships_pdf', methods: ['GET'])]
    public function pdf(Partnership $partnership): Response
    {
        return $this->render('admin/partnerships/pdf.html.twig', ['partnership' => $partnership]);
    }

}
