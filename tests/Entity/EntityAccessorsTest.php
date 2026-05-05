<?php

namespace App\Tests\Entity;

use App\Entity\Certificate;
use App\Entity\CertificateRequirement;
use App\Entity\Collaboration;
use App\Entity\Company;
use App\Entity\ContactHistory;
use App\Entity\Manager;
use App\Entity\Market;
use App\Entity\Notification;
use App\Entity\Partnership;
use App\Entity\Product;
use App\Entity\ProductCategory;
use App\Entity\Setting;
use App\Entity\Signature;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntityAccessorsTest extends TestCase
{
    #[DataProvider('entityAccessorProvider')]
    public function testEntityAccessors(string $entityName, object $entity, array $accessors): void
    {
        $this->assertNotSame('', $entityName);

        foreach ($accessors as [$setter, $getter, $value]) {
            $this->assertSame($entity, $entity->{$setter}($value));
            $this->assertSame($value, $entity->{$getter}());
        }
    }

    public static function entityAccessorProvider(): iterable
    {
        $company = new Company();
        $manager = new Manager();
        $market = new Market();
        $partnership = new Partnership();
        $targetCompany = new Company();
        $productCategory = new ProductCategory();
        $certificate = new Certificate();
        $setting = new Setting();
        $createdAt = new \DateTimeImmutable('2026-05-02 09:00:00');
        $lastUpdated = new \DateTimeImmutable('2026-05-02 10:00:00');
        $issueDate = new \DateTimeImmutable('2026-01-01');
        $expiryDate = new \DateTimeImmutable('2027-01-01');

        yield 'Certificate' => [
            'Certificate',
            new Certificate(),
            [
                ['setId', 'getId', 14],
                ['setCompany', 'getCompany', $company],
                ['setType', 'getType', 'ISO'],
                ['setCertificateNumber', 'getCertificateNumber', 'CERT-2026-001'],
                ['setIssueDate', 'getIssueDate', $issueDate],
                ['setExpiryDate', 'getExpiryDate', $expiryDate],
                ['setStatus', 'getStatus', 'active'],
                ['setCountryOfOrigin', 'getCountryOfOrigin', 'TN'],
                ['setIssuingAuthority', 'getIssuingAuthority', 'CEPEX'],
                ['setDocumentFile', 'getDocumentFile', 'certificate.pdf'],
                ['setPartnership', 'getPartnership', $partnership],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastUpdated', 'getLastUpdated', $lastUpdated],
            ],
        ];

        yield 'CertificateRequirement' => [
            'CertificateRequirement',
            new CertificateRequirement(),
            [
                ['setId', 'getId', 1],
                ['setMarketId', 'getMarketId', 5],
                ['setProductCategory', 'getProductCategory', 'Agriculture'],
                ['setCertificateType', 'getCertificateType', 'Certificate of Origin'],
                ['setMandatory', 'isMandatory', true],
                ['setDescription', 'getDescription', 'Required for export'],
            ],
        ];

        yield 'Collaboration' => [
            'Collaboration',
            new Collaboration(),
            [
                ['setId', 'getId', 2],
                ['setPartnership', 'getPartnership', $partnership],
                ['setTitle', 'getTitle', 'Export meeting'],
                ['setDescription', 'getDescription', 'Prepare export documents'],
                ['setStartDate', 'getStartDate', new \DateTimeImmutable('2026-05-01')],
                ['setEndDate', 'getEndDate', new \DateTimeImmutable('2026-05-15')],
                ['setStatus', 'getStatus', 'planned'],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastUpdated', 'getLastUpdated', $lastUpdated],
            ],
        ];

        yield 'Company' => [
            'Company',
            new Company(),
            [
                ['setId', 'getId', 3],
                ['setCompanyName', 'getCompanyName', 'Export Bridge'],
                ['setDomain', 'getDomain', 'logistics'],
                ['setTaxNumber', 'getTaxNumber', 'TN123456'],
                ['setRegistrationNumber', 'getRegistrationNumber', 'REG-001'],
                ['setCountry', 'getCountry', 'Tunisia'],
                ['setAddress', 'getAddress', 'Tunis'],
                ['setContactEmail', 'getContactEmail', 'contact@example.com'],
                ['setContactPhone', 'getContactPhone', '+21612345678'],
                ['setRating', 'getRating', 5],
                ['setWarnings', 'getWarnings', 1],
                ['setIsBanned', 'isBanned', false],
                ['setCompanyManager', 'getCompanyManager', $manager],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastUpdated', 'getLastUpdated', $lastUpdated],
            ],
        ];

        yield 'ContactHistory' => [
            'ContactHistory',
            new ContactHistory(),
            [
                ['setId', 'getId', 4],
                ['setCompany', 'getCompany', $company],
                ['setContactDate', 'getContactDate', $createdAt],
                ['setContactType', 'getContactType', 'email'],
                ['setNotes', 'getNotes', 'First contact'],
                ['setManager', 'getManager', $manager],
            ],
        ];

        yield 'Manager' => [
            'Manager',
            new Manager(),
            [
                ['setId', 'getId', 5],
                ['setFirstName', 'getFirstName', 'Amina'],
                ['setLastName', 'getLastName', 'Mansour'],
                ['setCompany', 'getCompany', $company],
                ['setEmail', 'getEmail', 'amina@example.com'],
                ['setPassword', 'getPassword', 'hashed-password'],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastLogin', 'getLastLogin', $lastUpdated],
                ['setSetting', 'getSetting', $setting],
            ],
        ];

        yield 'Market' => [
            'Market',
            new Market(),
            [
                ['setId', 'getId', 6],
                ['setCountryCode', 'getCountryCode', 'FR'],
                ['setName', 'getName', 'France'],
                ['setRegion', 'getRegion', 'Europe'],
                ['setIsEu', 'isEu', true],
                ['setDescription', 'getDescription', 'European market'],
                ['setTrade_agreement', 'getTrade_agreement', 'EU agreement'],
                ['setTradeAgreement', 'getTradeAgreement', 'Free trade'],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
            ],
        ];

        yield 'Notification' => [
            'Notification',
            new Notification(),
            [
                ['setId', 'getId', 7],
                ['setManager', 'getManager', $manager],
                ['setType', 'getType', 'certificate'],
                ['setMessage', 'getMessage', 'Certificate expires soon'],
                ['setIsRead', 'isRead', true],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
            ],
        ];

        yield 'Partnership' => [
            new Partnership(),
            [
                ['setId', 'getId', 8],
                ['setCompany', 'getCompany', $company],
                ['setStatus', 'getStatus', 'active'],
                ['setType', 'getType', 'distribution'],
                ['setEstablishedDate', 'getEstablishedDate', new \DateTimeImmutable('2026-01-10')],
                ['setTerminatedDate', 'getTerminatedDate', new \DateTimeImmutable('2026-12-31')],
                ['setNotes', 'getNotes', 'Strategic partnership'],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastUpdated', 'getLastUpdated', $lastUpdated],
            ],
        ];

        yield 'Product' => [
            'Product',
            new Product(),
            [
                ['setId', 'getId', 9],
                ['setCompany', 'getCompany', $company],
                ['setName', 'getName', 'Olive oil'],
                ['setDescription', 'getDescription', 'Premium product'],
                ['setHsCode', 'getHsCode', '1509'],
                ['setProductCategory', 'getProductCategory', $productCategory],
                ['setQuantity', 'getQuantity', '100.50'],
                ['setUnit', 'getUnit', 'L'],
                ['setUnitPrice', 'getUnitPrice', '12.00'],
                ['setCurrency', 'getCurrency', 'EUR'],
                ['setOriginCriteria', 'getOriginCriteria', 'Wholly obtained'],
                ['setCreatedAt', 'getCreatedAt', $createdAt],
                ['setLastUpdated', 'getLastUpdated', $lastUpdated],
            ],
        ];

        yield 'ProductCategory' => [
            'ProductCategory',
            new ProductCategory(),
            [
                ['setId', 'getId', 10],
                ['setName', 'getName', 'Food'],
                ['setDescription', 'getDescription', 'Food products'],
                ['setSlug', 'getSlug', 'food'],
            ],
        ];

        yield 'Setting' => [
            'Setting',
            new Setting(),
            [
                ['setId', 'getId', 11],
                ['setManager', 'getManager', $manager],
                ['setLanguage', 'getLanguage', 'fr'],
                ['setTheme', 'getTheme', 'light'],
                ['setEmailNotifications', 'isEmailNotifications', true],
                ['setPushNotifications', 'isPushNotifications', false],
                ['setCertificateExpiryAlerts', 'isCertificateExpiryAlerts', true],
                ['setAlertDaysBefore', 'getAlertDaysBefore', 30],
                ['setDateFormat', 'getDateFormat', 'Y-m-d'],
                ['setCurrency', 'getCurrency', 'EUR'],
                ['setTimezone', 'getTimezone', 'Europe/Paris'],
            ],
        ];

        yield 'Signature' => [
            'Signature',
            new Signature(),
            [
                ['setId', 'getId', 12],
                ['setCertificate', 'getCertificate', $certificate],
                ['setSignatoryName', 'getSignatoryName', 'Director'],
                ['setSignatoryTitle', 'getSignatoryTitle', 'Export Manager'],
                ['setSignedDate', 'getSignedDate', $createdAt],
                ['setDigitalSignature', 'getDigitalSignature', 'signed-hash'],
                ['setType', 'getType', 'digital'],
            ],
        ];
    }

    public function testCertificateSignaturesCollection(): void
    {
        $certificate = new Certificate();
        $signature = new Signature();

        $this->assertCount(0, $certificate->getSignatures());

        $certificate->addSignature($signature);
        $certificate->addSignature($signature);

        $this->assertCount(1, $certificate->getSignatures());
        $this->assertTrue($certificate->getSignatures()->contains($signature));

        $certificate->removeSignature($signature);

        $this->assertCount(0, $certificate->getSignatures());
    }

    public function testCompanyCollections(): void
    {
        $company = new Company();
        $certificate = new Certificate();

        $this->assertCount(0, $company->getProducts());
        $this->assertCount(0, $company->getContactHistory());
        $this->assertCount(0, $company->getCertificates());

        $company->addCertificate($certificate);
        $company->addCertificate($certificate);

        $this->assertCount(1, $company->getCertificates());

        $company->removeCertificate($certificate);

        $this->assertCount(0, $company->getCertificates());
    }

    public function testManagerSecurityAndCollections(): void
    {
        $manager = (new Manager())
            ->setEmail('manager@example.com')
            ->setRoles(['ROLE_ADMIN']);

        $company = new Company();
        $contactHistory = new ContactHistory();
        $notification = new Notification();

        $this->assertSame('manager@example.com', $manager->getUserIdentifier());
        $this->assertContains('ROLE_ADMIN', $manager->getRoles());
        $this->assertContains('ROLE_USER', $manager->getRoles());

        $manager->addCompany($company);
        $manager->addCompany($company);
        $manager->addContactHistory($contactHistory);
        $manager->addContactHistory($contactHistory);
        $manager->addNotification($notification);
        $manager->addNotification($notification);

        $this->assertCount(1, $manager->getCompanies());
        $this->assertCount(1, $manager->getContactHistory());
        $this->assertCount(1, $manager->getNotifications());

        $manager->removeCompany($company);
        $manager->removeContactHistory($contactHistory);
        $manager->removeNotification($notification);

        $this->assertCount(0, $manager->getCompanies());
        $this->assertCount(0, $manager->getContactHistory());
        $this->assertCount(0, $manager->getNotifications());
    }

    public function testMarketCompaniesCollection(): void
    {
        $market = new Market();
        $company = new Company();

        $market->addCompany($company);
        $market->addCompany($company);

        $this->assertCount(1, $market->getCompanies());
        $this->assertTrue($market->getCompanies()->contains($company));

        $market->removeCompany($company);

        $this->assertCount(0, $market->getCompanies());
    }

    public function testPartnershipCollections(): void
    {
        $partnership = new Partnership();
        $collaboration = new Collaboration();

        $this->assertCount(0, $partnership->getCollaborations());

        $partnership->addCollaboration($collaboration);
        $partnership->addCollaboration($collaboration);

        $this->assertCount(1, $partnership->getCollaborations());

        $partnership->removeCollaboration($collaboration);

        $this->assertCount(0, $partnership->getCollaborations());
    }

    public function testProductCategoryProductsCollection(): void
    {
        $category = new ProductCategory();
        $product = new Product();

        $category->addProduct($product);
        $category->addProduct($product);

        $this->assertCount(1, $category->getProducts());
        $this->assertTrue($category->getProducts()->contains($product));

        $category->removeProduct($product);

        $this->assertCount(0, $category->getProducts());
    }
}
