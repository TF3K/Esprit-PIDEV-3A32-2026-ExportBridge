<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ManagerControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testIndexShowsOnlyNonAdmins(): void
    {
        $adminEmail = 'admin-' . uniqid() . '@example.com';
        $userEmail = 'user-' . uniqid() . '@example.com';

        $admin = $this->createManager($adminEmail, ['ROLE_ADMIN'], 'Admin', 'User');
        $user = $this->createManager($userEmail, ['ROLE_USER'], 'Regular', 'Manager');

        $this->client->loginUser($admin);
        $crawler = $this->client->request('GET', '/admin/managers');
        $tableText = $crawler->filter('#managersTable')->text();

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Regular Manager', $tableText);
        self::assertStringNotContainsString('Admin User', $tableText);
    }

    public function testAddManagerCreatesNewManager(): void
    {
        $admin = $this->createManager('admin-add-' . uniqid() . '@example.com', ['ROLE_ADMIN'], 'Admin', 'User');
        $this->client->loginUser($admin);

        $email = 'new-manager-' . uniqid() . '@example.com';

        $this->client->request('POST', '/admin/managers/add', [
            'first_name' => 'New',
            'last_name' => 'Manager',
            'email' => $email,
            'password' => 'password123',
            'company_id' => '',
            'role' => 'ROLE_ADMIN',
        ]);

        self::assertResponseRedirects('/admin/managers');

        $created = $this->entityManager->getRepository(Manager::class)->findOneBy(['email' => $email]);
        self::assertNotNull($created);
        self::assertContains('ROLE_ADMIN', $created->getRoles());
    }

    public function testDeleteManagerRemovesManager(): void
    {
        $admin = $this->createManager('admin-delete-' . uniqid() . '@example.com', ['ROLE_ADMIN'], 'Admin', 'User');
        $this->client->loginUser($admin);

        $manager = $this->createManager('delete-' . uniqid() . '@example.com', ['ROLE_USER'], 'To', 'Delete');
        $managerId = $manager->getId();

        $this->client->request('POST', '/admin/managers/delete/' . $managerId);

        self::assertResponseRedirects('/admin/managers');
        self::assertNull($this->entityManager->getRepository(Manager::class)->find($managerId));
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
