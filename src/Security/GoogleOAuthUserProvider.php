<?php

namespace App\Security;

use App\Entity\Manager;
use App\Service\OAuthStorageService;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<Manager>
 */
class GoogleOAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private OAuthStorageService $oauthStorage,
    ) {}

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email     = (string) $response->getEmail();
        if ($email === '') {
            throw new AccountNotLinkedException();
        }

        $googleId  = $response->getUsername();
        $imageUrl  = $response->getProfilePicture();
        $firstName = $response->getFirstName();
        $lastName  = $response->getLastName();

        $this->oauthStorage->save($email, [
            'google_id'    => $googleId,
            'image_url'    => $imageUrl,
            'access_token' => $response->getAccessToken(),
            'email'        => $email,
            'first_name'   => $firstName,
            'last_name'    => $lastName,
        ]);

        $manager = $this->em->getRepository(Manager::class)
            ->findOneBy(['email' => $email]);

        if (!$manager) {
            $manager = new Manager();
            $manager->setEmail($email);
            $manager->setFirstName($firstName ?? 'Google');
            $manager->setLastName($lastName ?? 'User');
            $manager->setRoles(['ROLE_USER']);
            $manager->setPassword('');
            $manager->setCreatedAt(new \DateTime());

            $this->em->persist($manager);
            $this->em->flush();
        }

        return $manager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $manager = $this->em->getRepository(Manager::class)
            ->findOneBy(['email' => $identifier]);

        if (!$manager) {
            throw new AccountNotLinkedException();
        }

        return $manager;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Manager) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === Manager::class;
    }
}
