<?php

namespace App\DataFixtures;

use App\Entity\Market;
use App\Entity\Company;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // -------------------------------------------------------------------------
        // Markets
        // -------------------------------------------------------------------------

        $marketsData = [
            [
                'name'            => 'European Union Market',
                'country_code'    => 'Ed',
                'region'          => 'Europe',
                'is_eu'           => true,
                'description'     => 'Le marché européen unifié regroupant les 27 États membres de l Union Européenne.',
                'trade_agreement' => 'Single Market Agreement',
            ],
            [
                'name'            => 'North America Market',
                'country_code'    => 'Eg',
                'region'          => 'North America',
                'is_eu'           => false,
                'description'     => 'Le marché nord-américain couvrant les États-Unis, le Canada et le Mexique.',
                'trade_agreement' => 'USMCA',
            ],
            [
                'name'            => 'Asian Pacific Market',
                'country_code'    => 'Ey',
                'region'          => 'Asia Pacific',
                'is_eu'           => false,
                'description'     => 'Le marché asiatique et pacifique avec une forte croissance économique.',
                'trade_agreement' => 'RCEP',
            ],
            [
                'name'            => 'African Market',
                'country_code'    => 'Ei',
                'region'          => 'Africa',
                'is_eu'           => false,
                'description'     => 'Le marché africain en plein développement avec de nombreuses opportunités.',
                'trade_agreement' => 'AfCFTA',
            ],
            [
                'name'            => 'Middle East Market',
                'country_code'    => 'Ek',
                'region'          => 'Middle East',
                'is_eu'           => false,
                'description'     => 'Le marché du Moyen-Orient avec un fort potentiel commercial.',
                'trade_agreement' => 'GCC Agreement',
            ],
        ];

        $markets = [];

        foreach ($marketsData as $data) {
            $market = new Market();
            $market->setName($data['name']);
            $market->setCountryCode($data['country_code']);
            $market->setRegion($data['region']);
            $market->setIsEu($data['is_eu']);
            $market->setDescription($data['description']);
            $market->setTradeAgreement($data['trade_agreement']);
            $market->setCreatedAt(new \DateTime());

            $manager->persist($market);
            $markets[] = $market;
        }

        // -------------------------------------------------------------------------
        // Companies
        // -------------------------------------------------------------------------

        $companiesData = [
            [
                'company_name'        => 'TechCorp Solutions',
                'domain'              => 'techcorp.com',
                'tax_number'          => 'FR123456788',
                'registration_number' => 'REG-2020-001',
                'country'             => 'France',
                'address'             => '12 Rue de la Paix, 75001 Paris',
                'contact_email'       => 'contact@techcorp.com',
                'contact_phone'       => '+33 1 23 45 67 89',
                'rating'              => 5,
                'warnings'            => 0,
                'is_banned'           => false,
                'contract_hash'       => 'abc123def456',
                'market_index'        => 0, // EU Market
            ],
            [
                'company_name'        => 'Global Trade Inc',
                'domain'              => 'globaltrade.com',
                'tax_number'          => 'US987654301',
                'registration_number' => 'REG-2019-002',
                'country'             => 'United States',
                'address'             => '500 Fifth Avenue, New York, NY 10110',
                'contact_email'       => 'info@globaltrade.com',
                'contact_phone'       => '+1 212 555 0100',
                'rating'              => 4,
                'warnings'            => 1,
                'is_banned'           => false,
                'contract_hash'       => 'xyz789uvw012',
                'market_index'        => 1, // North America Market
            ],
            [
                'company_name'        => 'Asia Pacific Exports',
                'domain'              => 'apexports.cn',
                'tax_number'          => 'CN456989123',
                'registration_number' => 'REG-2021-003',
                'country'             => 'China',
                'address'             => '88 Century Avenue, Shanghai 200120',
                'contact_email'       => 'export@apexports.cn',
                'contact_phone'       => '+86 21 5888 8888',
                'rating'              => 4,
                'warnings'            => 0,
                'is_banned'           => false,
                'contract_hash'       => 'mnp345qrs678',
                'market_index'        => 2, // Asian Pacific Market
            ],
            [
                'company_name'        => 'AfriTrade Group',
                'domain'              => 'afritrade.co.za',
                'tax_number'          => 'ZA321254987',
                'registration_number' => 'REG-2022-004',
                'country'             => 'South Africa',
                'address'             => '10 Sandton Drive, Johannesburg 2196',
                'contact_email'       => 'hello@afritrade.co.za',
                'contact_phone'       => '+27 11 123 4567',
                'rating'              => 3,
                'warnings'            => 2,
                'is_banned'           => false,
                'contract_hash'       => 'efg901hij234',
                'market_index'        => 3, // African Market
            ],
            [
                'company_name'        => 'Gulf Commerce LLC',
                'domain'              => 'gulfcommerce.ae',
                'tax_number'          => 'AE654989321',
                'registration_number' => 'REG-2020-005',
                'country'             => 'UAE',
                'address'             => 'DIFC Gate Building, Dubai',
                'contact_email'       => 'trade@gulfcommerce.ae',
                'contact_phone'       => '+971 4 123 4567',
                'rating'              => 5,
                'warnings'            => 0,
                'is_banned'           => false,
                'contract_hash'       => 'klm567nop890',
                'market_index'        => 4, // Middle East Market
            ],
            [
                'company_name'        => 'EuroLogistics SA',
                'domain'              => 'eurologistics.eu',
                'tax_number'          => 'DE789023556',
                'registration_number' => 'REG-2018-006',
                'country'             => 'Germany',
                'address'             => 'Unter den Linden 10, 10117 Berlin',
                'contact_email'       => 'logistics@eurologistics.eu',
                'contact_phone'       => '+49 30 1234 5678',
                'rating'              => 2,
                'warnings'            => 3,
                'is_banned'           => true,
                'contract_hash'       => 'rst123uvw456',
                'market_index'        => 0, // EU Market
            ],
        ];

        foreach ($companiesData as $data) {
            $company = new Company();
            $company->setCompanyName($data['company_name']);
            $company->setDomain($data['domain']);
            $company->setTaxNumber($data['tax_number']);
            $company->setRegistrationNumber($data['registration_number']);
            $company->setCountry($data['country']);
            $company->setAddress($data['address']);
            $company->setContactEmail($data['contact_email']);
            $company->setContactPhone($data['contact_phone']);
            $company->setRating($data['rating']);
            $company->setWarnings($data['warnings']);
            $company->setIsBanned($data['is_banned']);
            $company->setContractHash($data['contract_hash']);
            $company->setMarket($markets[$data['market_index']]);
            $company->setCreatedAt(new \DateTime());
            $company->setLastUpdated(new \DateTime());

            $manager->persist($company);
        }

        // -------------------------------------------------------------------------
        // Flush
        // -------------------------------------------------------------------------

        $manager->flush();

        echo "\n✅ Fixtures chargées avec succès !\n";
        echo "   → 5 Markets créés\n";
        echo "   → 6 Companies créées\n\n";
    }
}