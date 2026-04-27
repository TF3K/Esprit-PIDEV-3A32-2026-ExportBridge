<?php

namespace App\Twig;

use App\Service\OAuthStorageService;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CurrentUserAvatarExtension extends AbstractExtension
{
    public function __construct(
        private Security $security,
        private OAuthStorageService $oauthStorage,
        private Packages $assets,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_avatar_url', [$this, 'getCurrentAvatarUrl']),
        ];
    }

    public function getCurrentAvatarUrl(): string
    {
        $user = $this->security->getUser();

        if ($user instanceof UserInterface) {
            $email = (string) $user->getUserIdentifier();
            if ($email !== '') {
                $oauthData = $this->oauthStorage->get($email);
                $imageUrl = $oauthData['image_url'] ?? null;

                if (is_string($imageUrl) && trim($imageUrl) !== '') {
                    return $imageUrl;
                }
            }
        }

        return $this->assets->getUrl('assets/images/placeholder.svg');
    }
}
