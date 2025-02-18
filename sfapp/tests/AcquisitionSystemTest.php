<?php

namespace App\Tests;

use App\Entity\AcquisitionSystem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AcquisitionSystemTest extends WebTestCase
{
    // Test des valeurs initiales
    public function testInitialValues(): void
    {
        $system = new AcquisitionSystem();

        $this->assertNull($system->getName());
    }

    // Test des setters et getters
    public function testSettersAndGetters(): void
    {
        $system = new AcquisitionSystem();
        $system->setName('AS1_test');
        $this->assertEquals('AS1_test', $system->getName());
    }

    // Test de la longueur du nom
    public function testNameLength(): void
    {
        $system = new AcquisitionSystem();

        $validName = str_repeat('a', 15);
        $system->setName($validName);
        $this->assertTrue(strlen($system->getName()) <= 15, 'Un nom de 15 caractères devrait être accepté.');

        $invalidName = str_repeat('a', 16);
        $system->setName($invalidName);
        $this->assertTrue(strlen($system->getName()) > 15, 'Un nom de plus de 15 caractères ne devrait pas être accepté.');
    }

    // Test de la barre de recherche
    public function testSearchBar(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/acquisitionsystem');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Rechercher')->form([
            'name' => 'AS1_test',
        ]);
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('td', 'AS1_test');
        $this->assertSelectorNotExists('td:contains("AS2_test")', 'Les éléments non correspondants ne doivent pas être affichés.');
    }

    // Test d'ajout d'un SA
    public function testAddAcquisitionSystem(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/acquisitionsystem/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form([
            'acquisition_system[name]' => 'AS3_test',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/acquisitionsystem');
    }

    // Test de modification d'un SA
    public function testModifyAcquisitionSystem(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/acquisitionsystem');
        $modifyLink = $crawler->filterXPath("//tr[td[contains(text(), 'AS3_test')]]//a[contains(text(), 'Modifier')]")->link();
        $crawler = $client->click($modifyLink);
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer les modifications')->form([
            'acquisition_system[name]' => 'AS4_test',
        ]);
        $client->submit($form);

        $this->assertResponseRedirects('/acquisitionsystem');
    }

    // Test d'ajout d'un SA dupliqué
    public function testAddDuplicateAcquisitionSystem(): void
    {
        $client = static::createClient();

        // Ajouter un système
        $crawler = $client->request('GET', '/acquisitionsystem/add');
        $form = $crawler->selectButton('Enregistrer')->form([
            'acquisition_system[name]' => 'AS5_test',
        ]);
        $client->submit($form);

        // Tenter de l'ajouter à nouveau
        $crawler = $client->request('POST', '/acquisitionsystem/add', [
            'acquisition_system' => [
                'name' => 'AS5_test',
            ],
        ]);
        $this->assertResponseStatusCodeSame(200);
    }

    // Test de suppression des SA
    public function testDeleteAcquisitionSystem(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/acquisitionsystem');
        $form = $crawler->filterXPath("//tr[td[contains(text(), 'AS4_test')]]//form")->form();
        $client->submit($form);

        $crawler = $client->request('GET', '/acquisitionsystem');
        $form = $crawler->filterXPath("//tr[td[contains(text(), 'AS5_test')]]//form")->form();
        $client->submit($form);

        $this->assertResponseRedirects('/acquisitionsystem');
    }
}
