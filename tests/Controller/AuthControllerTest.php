<?php

namespace App\Tests\Controller;

use App\Entity\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testLoginPageIsRenderedForGuests(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
    }

    public function testLoggedInUserIsRedirectedFromLoginToDashboard(): void
    {
        $manager = $this->createManager('user-' . uniqid() . '@example.com', ['ROLE_USER']);

        $this->client->loginUser($manager);
        $this->client->request('GET', '/login');

        self::assertResponseRedirects();
        self::assertStringStartsWith('/dashboard', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testLoggedInAdminIsRedirectedFromLoginToAdminDashboard(): void
    {
        $manager = $this->createManager('admin-' . uniqid() . '@example.com', ['ROLE_ADMIN']);

        $this->client->loginUser($manager);
        $this->client->request('GET', '/login');

        self::assertResponseRedirects();
        self::assertStringStartsWith('/admin', (string) $this->client->getResponse()->headers->get('Location'));
    }

    public function testRegisterCreatesAccountAndRedirectsToLogin(): void
    {
        $email = 'register-' . uniqid() . '@example.com';

        $this->client->request('POST', '/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'password' => 'password123',
            'confirm_password' => 'password123',
            'company_id' => '',
        ]);

        self::assertResponseRedirects('/login');
        self::assertNotNull($this->entityManager->getRepository(Manager::class)->findOneBy(['email' => $email]));
    }

    public function testLoggedInUserIsRedirectedFromRegisterToRedirectRoute(): void
    {
        $manager = $this->createManager('register-user-' . uniqid() . '@example.com', ['ROLE_USER']);

        $this->client->loginUser($manager);
        $this->client->request('GET', '/register');

        self::assertResponseRedirects();
        self::assertStringStartsWith('/redirect', (string) $this->client->getResponse()->headers->get('Location'));
    }

    private function createManager(string $email, array $roles): Manager
    {
        $manager = new Manager();
        $manager->setFirstName('Test');
        $manager->setLastName('User');
        $manager->setEmail($email);
        $manager->setPassword('password123');
        $manager->setRoles($roles);
        $manager->setCreatedAt(new \DateTime());

        $this->entityManager->persist($manager);
        $this->entityManager->flush();

        return $manager;
    }
}
