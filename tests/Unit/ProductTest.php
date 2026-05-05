<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Entity\Company;
use App\Entity\ProductCategory;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        $this->product = new Product();
    }

    public function testProductCreation(): void
    {
        $this->assertInstanceOf(Product::class, $this->product);
    }

    public function testId(): void
    {
        $this->assertNull($this->product->getId());
    }

    public function testName(): void
    {
        $name = 'Test Product';
        $this->product->setName($name);
        $this->assertEquals($name, $this->product->getName());
    }

    public function testDescription(): void
    {
        $description = 'Test Description';
        $this->product->setDescription($description);
        $this->assertEquals($description, $this->product->getDescription());
    }

    public function testHsCode(): void
    {
        $hsCode = '1234567890';
        $this->product->setHsCode($hsCode);
        $this->assertEquals($hsCode, $this->product->getHsCode());
    }

    public function testQuantity(): void
    {
        $quantity = 100.50;
        $this->product->setQuantity($quantity);
        $this->assertEquals($quantity, $this->product->getQuantity());
    }

    public function testQuantityNull(): void
    {
        $this->product->setQuantity(null);
        $this->assertNull($this->product->getQuantity());
    }

    public function testUnit(): void
    {
        $unit = 'kg';
        $this->product->setUnit($unit);
        $this->assertEquals($unit, $this->product->getUnit());
    }

    public function testUnitPrice(): void
    {
        $unitPrice = 25.99;
        $this->product->setUnitPrice($unitPrice);
        $this->assertEquals($unitPrice, $this->product->getUnitPrice());
    }

    public function testUnitPriceNull(): void
    {
        $this->product->setUnitPrice(null);
        $this->assertNull($this->product->getUnitPrice());
    }

    public function testCurrency(): void
    {
        $currency = 'EUR';
        $this->product->setCurrency($currency);
        $this->assertEquals($currency, $this->product->getCurrency());
    }

    public function testOriginCriteria(): void
    {
        $originCriteria = 'Made in France';
        $this->product->setOriginCriteria($originCriteria);
        $this->assertEquals($originCriteria, $this->product->getOriginCriteria());
    }

    public function testCompany(): void
    {
        $company = new Company();
        $this->product->setCompany($company);
        $this->assertEquals($company, $this->product->getCompany());
    }

    public function testCompanyNull(): void
    {
        $this->product->setCompany(null);
        $this->assertNull($this->product->getCompany());
    }

    public function testProductCategory(): void
    {
        $category = new ProductCategory();
        $this->product->setProductCategory($category);
        $this->assertEquals($category, $this->product->getProductCategory());
    }

    public function testProductCategoryNull(): void
    {
        $this->product->setProductCategory(null);
        $this->assertNull($this->product->getProductCategory());
    }

    public function testCreatedAt(): void
    {
        $createdAt = new \DateTime('2023-01-01 12:00:00');
        $this->product->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->product->getCreatedAt());
    }

    public function testLastUpdated(): void
    {
        $lastUpdated = new \DateTime('2023-01-01 12:00:00');
        $this->product->setLastUpdated($lastUpdated);
        $this->assertEquals($lastUpdated, $this->product->getLastUpdated());
    }

    public function testDecimalFieldsTypeConversion(): void
    {
        // Test that decimal fields are properly converted
        $this->product->setQuantity(100.50);
        $this->product->setUnitPrice(25.99);

        // These should return float values
        $this->assertIsFloat($this->product->getQuantity());
        $this->assertIsFloat($this->product->getUnitPrice());

        $this->assertEquals(100.50, $this->product->getQuantity());
        $this->assertEquals(25.99, $this->product->getUnitPrice());
    }
}
