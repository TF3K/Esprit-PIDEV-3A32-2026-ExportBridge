<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Company;
use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/** @phpstan-ignore-next-line */
class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $product1 = new Product();
        $product1->setName('Smartphone X1');
        $product1->setDescription('Latest smartphone with advanced features');
        $product1->setHsCode('8517130000');
        $product1->setQuantity(500.00);
        $product1->setUnit('pieces');
        $product1->setUnitPrice(699.99);
        $product1->setCurrency('EUR');
        $product1->setOriginCriteria('Made in France');
        $product1->setCompany($this->getReference('company-tech', Company::class));
        $product1->setProductCategory($this->getReference('category-electronics', ProductCategory::class));
        $product1->setCreatedAt(new \DateTime('2023-01-15'));
        $product1->setLastUpdated(new \DateTime('2023-01-15'));

        $manager->persist($product1);
        $this->addReference('product-smartphone', $product1);

        $product2 = new Product();
        $product2->setName('Industrial Robot Arm');
        $product2->setDescription('Precision robotic arm for manufacturing');
        $product2->setHsCode('8479500000');
        $product2->setQuantity(25.00);
        $product2->setUnit('units');
        $product2->setUnitPrice(15000.00);
        $product2->setCurrency('EUR');
        $product2->setOriginCriteria('Made in Germany');
        $product2->setCompany($this->getReference('company-manufacturing', Company::class));
        $product2->setProductCategory($this->getReference('category-machinery', ProductCategory::class));
        $product2->setCreatedAt(new \DateTime('2023-02-10'));
        $product2->setLastUpdated(new \DateTime('2023-02-10'));

        $manager->persist($product2);
        $this->addReference('product-robot', $product2);

        $product3 = new Product();
        $product3->setName('Premium Cotton Fabric');
        $product3->setDescription('High-quality cotton fabric for clothing');
        $product3->setHsCode('5208320000');
        $product3->setQuantity(1000.00);
        $product3->setUnit('meters');
        $product3->setUnitPrice(15.50);
        $product3->setCurrency('EUR');
        $product3->setOriginCriteria('Made in France');
        $product3->setCompany($this->getReference('company-tech', Company::class));
        $product3->setProductCategory($this->getReference('category-textiles', ProductCategory::class));
        $product3->setCreatedAt(new \DateTime('2023-03-05'));
        $product3->setLastUpdated(new \DateTime('2023-03-05'));

        $manager->persist($product3);
        $this->addReference('product-fabric', $product3);

        $product4 = new Product();
        $product4->setName('Organic Fertilizer');
        $product4->setDescription('Environmentally friendly organic fertilizer');
        $product4->setHsCode('3101009000');
        $product4->setQuantity(2000.00);
        $product4->setUnit('kg');
        $product4->setUnitPrice(2.75);
        $product4->setCurrency('EUR');
        $product4->setOriginCriteria('Made in France');
        $product4->setCompany($this->getReference('company-export', Company::class));
        $product4->setProductCategory($this->getReference('category-chemicals', ProductCategory::class));
        $product4->setCreatedAt(new \DateTime('2023-04-01'));
        $product4->setLastUpdated(new \DateTime('2023-04-01'));

        $manager->persist($product4);
        $this->addReference('product-fertilizer', $product4);

        $product5 = new Product();
        $product5->setName('French Wine Collection');
        $product5->setDescription('Premium French wine selection');
        $product5->setHsCode('2204210000');
        $product5->setQuantity(750.00);
        $product5->setUnit('bottles');
        $product5->setUnitPrice(45.00);
        $product5->setCurrency('EUR');
        $product5->setOriginCriteria('Made in France');
        $product5->setCompany($this->getReference('company-export', Company::class));
        $product5->setProductCategory($this->getReference('category-food', ProductCategory::class));
        $product5->setCreatedAt(new \DateTime('2023-05-01'));
        $product5->setLastUpdated(new \DateTime('2023-05-01'));

        $manager->persist($product5);
        $this->addReference('product-wine', $product5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CompanyFixtures::class,
            ProductCategoryFixtures::class,
        ];
    }
}
