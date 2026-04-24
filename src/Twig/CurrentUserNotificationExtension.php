<?php

namespace App\Twig;

use App\Entity\Manager;
use App\Repository\NotificationRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CurrentUserNotificationExtension extends AbstractExtension
{
    /**
     * @var array<int, array>
     */
    private array $recentCache = [];
    private ?int $unreadCountCache = null;

    public function __construct(
        private Security $security,
        private NotificationRepository $notificationRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_notifications', [$this, 'getCurrentNotifications']),
            new TwigFunction('current_unread_notification_count', [$this, 'getCurrentUnreadNotificationCount']),
        ];
    }

    /**
     * @return array
     */
    public function getCurrentNotifications(int $limit = 6): array
    {
        if (isset($this->recentCache[$limit])) {
            return $this->recentCache[$limit];
        }

        $manager = $this->getCurrentManager();
        if (!$manager) {
            return [];
        }

        $this->recentCache[$limit] = $this->notificationRepository->findRecentForManager($manager, $limit);

        return $this->recentCache[$limit];
    }

    public function getCurrentUnreadNotificationCount(): int
    {
        if ($this->unreadCountCache !== null) {
            return $this->unreadCountCache;
        }

        $manager = $this->getCurrentManager();
        if (!$manager) {
            return 0;
        }

        $this->unreadCountCache = $this->notificationRepository->countUnreadForManager($manager);

        return $this->unreadCountCache;
    }

    private function getCurrentManager(): ?Manager
    {
        $user = $this->security->getUser();

        return $user instanceof Manager ? $user : null;
    }
}