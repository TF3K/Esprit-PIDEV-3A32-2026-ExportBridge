<?php

namespace App\DataFixtures;

use App\Entity\Manager;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/** @phpstan-ignore-next-line */
class ManagerFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $manager1 = new Manager();
        $manager1->setFirstName('John');
        $manager1->setLastName('Doe');
        $manager1->setEmail('john.doe@techsolutions.com');
        $manager1->setPassword($this->passwordHasher->hashPassword($manager1, 'password123'));
        $manager1->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager1->setCompany($this->getReference('company-tech', Company::class));
        $manager1->setCreatedAt(new \DateTime('2023-01-01'));

        $manager->persist($manager1);
        $this->addReference('manager-john', $manager1);

        $manager2 = new Manager();
        $manager2->setFirstName('Jane');
        $manager2->setLastName('Smith');
        $manager2->setEmail('jane.smith@globalexport.co.uk');
        $manager2->setPassword($this->passwordHasher->hashPassword($manager2, 'password123'));
        $manager2->setRoles(['ROLE_MANAGER', 'ROLE_USER']);
        $manager2->setCompany($this->getReference('company-export', Company::class));
        $manager2->setCreatedAt(new \DateTime('2023-02-01'));

        $manager->persist($manager2);
        $this->addReference('manager-jane', $manager2);

        $manager3 = new Manager();
        $manager3->setFirstName('Robert');
        $manager3->setLastName('Johnson');
        $manager3->setEmail('robert.johnson@manufacturing-pro.de');
        $manager3->setPassword($this->passwordHasher->hashPassword($manager3, 'password123'));
        $manager3->setRoles(['ROLE_USER']);
        $manager3->setCompany($this->getReference('company-manufacturing', Company::class));
        $manager3->setCreatedAt(new \DateTime('2023-03-01'));

        $manager->persist($manager3);
        $this->addReference('manager-robert', $manager3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
