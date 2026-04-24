<?php

namespace App\Controller;

use App\Entity\Manager;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    #[Route('/read/{id}', name: 'app_notifications_read', methods: ['POST'], requirements: ['id' => '\\d+'])]
    public function markAsRead(Notification $notification, Request $request, EntityManagerInterface $em): Response
    {
        /** @var Manager|null $manager */
        $manager = $this->getUser();

        if (!$manager instanceof Manager || $notification->getManager()?->getId() !== $manager->getId()) {
            throw $this->createAccessDeniedException('You cannot modify this notification.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('read-notification-' . $notification->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $em->flush();
        }

        return $this->redirectToReferer($request);
    }

    #[Route('/read-all', name: 'app_notifications_read_all', methods: ['POST'])]
    public function markAllAsRead(Request $request, NotificationRepository $notificationRepository): Response
    {
        /** @var Manager|null $manager */
        $manager = $this->getUser();

        if (!$manager instanceof Manager) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('read-all-notifications', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $notificationRepository->markAllReadForManager($manager);

        return $this->redirectToReferer($request);
    }

    private function redirectToReferer(Request $request): RedirectResponse
    {
        $referer = (string) $request->headers->get('referer');
        if ($referer !== '') {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_redirect');
    }
}