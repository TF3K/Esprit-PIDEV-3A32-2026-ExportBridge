<?php

namespace App\Tests\Controller;

use App\Entity\Market;
use App\Repository\MarketRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MarketControllerTest extends WebTestCase
{
    /**
     * TEST 1: Création réussie
     * Basé sur ton fichier AddMarket.html.twig
     */
    public function testCreateMarketSuccess(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/market/create');
        
        $this->assertResponseIsSuccessful();

        // Extraction du token CSRF
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        // Soumission avec les noms exacts de tes champs HTML
        $client->request('POST', '/market/create', [
            'title'           => 'Marche Test Export', // Doit passer le regex [a-zA-ZÀ-ÿ\s]
            'trade_agreement' => 'Accord de Libre Echange International',
            'region'          => 'UE',
            'country_code'    => 'FR',
            'description'     => 'Description longue de plus de dix caractères pour le test.',
            'featured'        => '1',
            '_token'          => $token,
        ]);

        // Ton contrôleur fait : return $this->redirectToRoute('app_admin_markets');
        // On vérifie la redirection (302)
        $this->assertResponseStatusCodeSame(302);
        
        $client->followRedirect();
        // Dans ton Twig admin : {% for message in app.flashes('success') %} <div class="alert alert-success">
        $this->assertSelectorExists('.alert-success');
    }

    /**
     * TEST 2: Échec de validation (Titre invalide)
     */
    public function testCreateMarketValidationError(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/market/create');
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        // Titre avec chiffres (invalide selon ton regex contrôleur)
        $client->request('POST', '/market/create', [
            'title'           => 'Market 123', 
            'trade_agreement' => 'Test',
            'region'          => 'UE',
            'country_code'    => 'FR',
            'description'     => 'Court',
            '_token'          => $token,
        ]);

        // En cas d'erreur, ton code redirige vers 'app_market_create'
        $this->assertResponseRedirects('/market/create');
        
        $client->followRedirect();
        // Ton Twig affiche : <div class="alert alert-danger"> pour les erreurs
        $this->assertSelectorExists('.alert-danger');
    }

    /**
     * TEST 3: Liste des marchés (Recherche)
     */
    public function testListMarketAndSearch(): void
    {
        $client = static::createClient();
        
        // Test de la liste simple
        $client->request('GET', '/market/list');
        $this->assertResponseIsSuccessful();

        // Test de la recherche (Route: /search?name=...)
        $crawler = $client->request('GET', '/search', ['name' => 'Marche']);
        $this->assertResponseIsSuccessful();
        
        // Vérifie qu'on est bien sur le template de liste
        $this->assertSelectorTextContains('h3', 'My Courses List');
    }

    /**
     * TEST 4: Mise à jour (Update)
     */
    public function testUpdateMarket(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $repo = $container->get(MarketRepository::class);

        $market = $repo->findOneBy([]);
        if (!$market) {
            $this->markTestSkipped('Aucun marché en base pour tester l\'update.');
        }

        $crawler = $client->request('GET', '/market/update/' . $market->getId());
        $token = $crawler->filter('input[name="_token"]')->attr('value');

        $client->request('POST', '/market/update/' . $market->getId(), [
            'title'           => 'Titre Modifie',
            'trade_agreement' => 'Accord modifie',
            'region'          => 'USMCA',
            'country_code'    => 'US',
            'description'     => 'Nouvelle description plus longue.',
            'featured'        => '1',
            '_token'          => $token,
        ]);

        $this->assertResponseRedirects(); // Redirige vers admin après succès
    }
}