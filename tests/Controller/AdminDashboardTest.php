<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminDashboardTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testDashboardListsOnlyNonAdminManagers(): void
    {
        $admin = $this->createManager('dashboard-admin-' . uniqid() . '@example.com', ['ROLE_ADMIN'], 'Admin', 'User');
        $user = $this->createManager('dashboard-user-' . uniqid() . '@example.com', ['ROLE_USER'], 'Regular', 'Manager');

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin');
        $tableText = $crawler->filter('#managersTable')->text();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Regular Manager', $tableText);
        self::assertStringNotContainsString('Admin User', $tableText);
    }

    private function createManager(string $email, array $roles, string $firstName, string $lastName): Manager
    {
        $manager = new Manager();
        $manager->setFirstName($firstName);
        $manager->setLastName($lastName);
        $manager->setEmail($email);
        $manager->setPassword('password123');
        $manager->setRoles($roles);
        $manager->setCreatedAt(new \DateTime());

        $this->entityManager->persist($manager);
        $this->entityManager->flush();

        return $manager;
    }
}
