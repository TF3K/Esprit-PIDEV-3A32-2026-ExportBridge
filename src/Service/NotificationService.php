<?php

namespace App\Service;

use App\Entity\Manager;
use App\Entity\Notification;
use App\Repository\NotificationRepository;

class NotificationService
{
    public function __construct(private NotificationRepository $notificationRepository) {}

    public function createForManager(Manager $manager, string $type, string $message): void
    {
        $notification = new Notification();
        $notification->setManager($manager);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime());

        $this->notificationRepository->save($notification, true);
    }
}
