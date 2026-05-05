<?php

namespace App\DataFixtures;

use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductCategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $category1 = new ProductCategory();
        $category1->setName('Electronics');
        $category1->setDescription('Electronic devices and components');

        $manager->persist($category1);
        $this->addReference('category-electronics', $category1);

        $category2 = new ProductCategory();
        $category2->setName('Machinery');
        $category2->setDescription('Industrial machinery and equipment');

        $manager->persist($category2);
        $this->addReference('category-machinery', $category2);

        $category3 = new ProductCategory();
        $category3->setName('Textiles');
        $category3->setDescription('Textile products and materials');

        $manager->persist($category3);
        $this->addReference('category-textiles', $category3);

        $category4 = new ProductCategory();
        $category4->setName('Chemicals');
        $category4->setDescription('Chemical products and raw materials');

        $manager->persist($category4);
        $this->addReference('category-chemicals', $category4);

        $category5 = new ProductCategory();
        $category5->setName('Food & Beverages');
        $category5->setDescription('Food products and beverages');

        $manager->persist($category5);
        $this->addReference('category-food', $category5);

        $manager->flush();
    }
}
