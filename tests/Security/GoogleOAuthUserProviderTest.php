<?php

namespace App\Tests\Security;

use App\Entity\Manager;
use App\Security\GoogleOAuthUserProvider;
use App\Service\OAuthStorageService;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class GoogleOAuthUserProviderTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    /** @var OAuthStorageService&MockObject */
    private $storage;
    private GoogleOAuthUserProvider $provider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->storage = $this->createMock(OAuthStorageService::class);
        $this->provider = new GoogleOAuthUserProvider($this->entityManager, $this->storage);
    }

    public function testLoadUserByOAuthResponseCreatesManagerAndStoresOAuthData(): void
    {
        $email = 'google-' . uniqid() . '@example.com';
        $response = $this->createUserResponse(
            email: $email,
            username: 'google-12345',
            profilePicture: 'https://example.com/avatar.png',
            firstName: 'Google',
            lastName: 'User',
            accessToken: 'token-123'
        );

        $this->storage->expects(self::once())
            ->method('save')
            ->with($email, self::callback(static function (array $payload) use ($email): bool {
                return $payload['google_id'] === 'google-12345'
                    && $payload['image_url'] === 'https://example.com/avatar.png'
                    && $payload['access_token'] === 'token-123'
                    && $payload['email'] === $email
                    && $payload['first_name'] === 'Google'
                    && $payload['last_name'] === 'User';
            }));

        $manager = $this->provider->loadUserByOAuthUserResponse($response);

        self::assertInstanceOf(Manager::class, $manager);
        /** @var Manager $manager */
        self::assertSame($email, $manager->getEmail());
        self::assertSame('Google', $manager->getFirstName());
        self::assertSame('User', $manager->getLastName());
        self::assertContains('ROLE_USER', $manager->getRoles());
        self::assertNotNull($this->entityManager->getRepository(Manager::class)->findOneBy(['email' => $email]));
    }

    public function testLoadUserByIdentifierReturnsExistingManager(): void
    {
        $email = 'existing-' . uniqid() . '@example.com';
        $manager = new Manager();
        $manager->setFirstName('Existing');
        $manager->setLastName('Manager');
        $manager->setEmail($email);
        $manager->setPassword('password123');
        $manager->setRoles(['ROLE_USER']);
        $manager->setCreatedAt(new \DateTime());

        $this->entityManager->persist($manager);
        $this->entityManager->flush();

        $loaded = $this->provider->loadUserByIdentifier($email);

        self::assertInstanceOf(Manager::class, $loaded);
        /** @var Manager $loaded */
        self::assertSame($manager->getId(), $loaded->getId());
    }

    public function testRefreshUserRejectsUnsupportedUserClasses(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $this->provider->refreshUser($this->createMock(UserInterface::class));
    }

    public function testSupportsClassReturnsTrueOnlyForManager(): void
    {
        self::assertTrue($this->provider->supportsClass(Manager::class));
        self::assertFalse($this->provider->supportsClass(\stdClass::class));
    }

    private function createUserResponse(
        string $email,
        string $username,
        ?string $profilePicture,
        ?string $firstName,
        ?string $lastName,
        string $accessToken
    ): UserResponseInterface {
        $response = $this->createMock(UserResponseInterface::class);
        $response->method('getEmail')->willReturn($email);
        $response->method('getUsername')->willReturn($username);
        $response->method('getProfilePicture')->willReturn($profilePicture);
        $response->method('getFirstName')->willReturn($firstName);
        $response->method('getLastName')->willReturn($lastName);
        $response->method('getAccessToken')->willReturn($accessToken);

        return $response;
    }
}
