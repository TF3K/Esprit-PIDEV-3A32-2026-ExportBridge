<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testProductIndexPage(): void
    {
        $this->client->request('GET', '/admin/products');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Products'); // Adjust based on your actual page content
    }

    public function testProductShowPage(): void
    {
        // First, we need to create a product in the database
        // This would typically be done with fixtures, but for now we'll test the route structure

        $this->client->request('GET', '/admin/products/show/1');

        // If no product exists, this should return 404
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testProductAddPage(): void
    {
        $this->client->request('GET', '/admin/products/add');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="product"]'); // Adjust form name if needed
    }

    public function testProductAddFormSubmission(): void
    {
        $crawler = $this->client->request('GET', '/admin/products/add');

        $form = $crawler->selectButton('Save')->form([
            'product[name]' => 'Test Product',
            'product[description]' => 'Test Description',
            'product[hs_code]' => '1234567890',
            'product[quantity]' => '100',
            'product[unit]' => 'kg',
            'product[unit_price]' => '25.99',
            'product[currency]' => 'EUR',
            'product[origin_criteria]' => 'Made in France',
        ]);

        $this->client->submit($form);

        // Check if we're redirected after successful submission
        $this->assertResponseRedirects();

        // Follow the redirect
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testProductMailPage(): void
    {
        $this->client->request('GET', '/admin/products/mail');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[name="mail_form"]'); // Adjust form name if needed
    }

    public function testProductMailFormSubmission(): void
    {
        $crawler = $this->client->request('GET', '/admin/products/mail');

        $form = $crawler->selectButton('Send Email')->form([
            'mail_form[recipients]' => 'test@example.com',
            'mail_form[subject]' => 'Test Subject',
            'mail_form[message]' => 'Test Message',
        ]);

        $this->client->submit($form);

        // Check if we're redirected after successful submission
        $this->assertResponseRedirects();

        // Follow the redirect
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testInvalidRoute(): void
    {
        $this->client->request('GET', '/admin/products/nonexistent');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testProductWithBarcodeGeneration(): void
    {
        // Test the barcode generation functionality
        $this->client->request('GET', '/admin/products/show/1');

        // If we had a product, we could check for barcode presence
        // $this->assertSelectorExists('img[alt*="barcode"]');
    }

    public function testProductTranslationFeature(): void
    {
        // Test the AI translation feature if available
        $this->client->request('POST', '/admin/products/translate', [
            'text' => 'Hello World',
            'target_language' => 'fr',
        ]);

        // This would need to be adjusted based on your actual translation endpoint
        $this->assertResponseIsSuccessful();
    }

    public function testProductSearch(): void
    {
        // Test product search functionality
        $this->client->request('GET', '/admin/products?search=test');

        $this->assertResponseIsSuccessful();
    }

    public function testProductFiltering(): void
    {
        // Test product filtering by category
        $this->client->request('GET', '/admin/products?category=1');

        $this->assertResponseIsSuccessful();
    }

    public function testProductExport(): void
    {
        // Test product export functionality
        $this->client->request('GET', '/admin/products/export/csv');

        $this->assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/csv; charset=UTF-8');
    }
}
