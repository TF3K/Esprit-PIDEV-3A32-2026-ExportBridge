<?php

namespace App\DataFixtures;

use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CompanyFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $company1 = new Company();
        $company1->setCompanyName('Tech Solutions Inc.');
        $company1->setDomain('technology');
        $company1->setTaxNumber('FR12345678901');
        $company1->setRegistrationNumber('REG001');
        $company1->setCountry('France');
        $company1->setAddress('123 Tech Street, Paris, France');
        $company1->setContactEmail('contact@techsolutions.com');
        $company1->setContactPhone('+33123456789');
        $company1->setRating(4);
        $company1->setWarnings(0);
        $company1->setIsBanned(false);
        $company1->setCreatedAt(new \DateTime('2023-01-01'));
        $company1->setLastUpdated(new \DateTime('2023-01-01'));
        
        $manager->persist($company1);
        $this->addReference('company-tech', $company1);

        $company2 = new Company();
        $company2->setCompanyName('Global Export Ltd.');
        $company2->setDomain('export');
        $company2->setTaxNumber('GB98765432109');
        $company2->setRegistrationNumber('REG002');
        $company2->setCountry('United Kingdom');
        $company2->setAddress('456 Export Avenue, London, UK');
        $company2->setContactEmail('info@globalexport.co.uk');
        $company2->setContactPhone('+442076543210');
        $company2->setRating(4);
        $company2->setWarnings(0);
        $company2->setIsBanned(false);
        $company2->setCreatedAt(new \DateTime('2023-02-01'));
        $company2->setLastUpdated(new \DateTime('2023-02-01'));
        
        $manager->persist($company2);
        $this->addReference('company-export', $company2);

        $company3 = new Company();
        $company3->setCompanyName('Manufacturing Pro');
        $company3->setDomain('manufacturing');
        $company3->setTaxNumber('DE56789012345');
        $company3->setRegistrationNumber('REG003');
        $company3->setCountry('Germany');
        $company3->setAddress('789 Factory Road, Berlin, Germany');
        $company3->setContactEmail('production@manufacturing-pro.de');
        $company3->setContactPhone('+493012345678');
        $company3->setRating(4);
        $company3->setWarnings(1);
        $company3->setIsBanned(false);
        $company3->setCreatedAt(new \DateTime('2023-03-01'));
        $company3->setLastUpdated(new \DateTime('2023-03-01'));
        
        $manager->persist($company3);
        $this->addReference('company-manufacturing', $company3);

        $manager->flush();
    }
}
