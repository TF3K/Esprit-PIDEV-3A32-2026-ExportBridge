<?php

namespace App\Service;

use App\Entity\Manager;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function createForManager(Manager $manager, string $type, string $message): void
    {
        $notification = new Notification();
        $notification->setManager($manager);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setIsRead(false);
        $notification->setCreatedAt(new \DateTime());

        $this->em->persist($notification);
        $this->em->flush();
    }
}