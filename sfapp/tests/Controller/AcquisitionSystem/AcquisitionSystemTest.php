<?php

namespace App\Tests\Controller\AcquisitionSystem;

use App\Entity\AcquisitionSystem;
use App\Repository\UserRepository;
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
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/acquisitionsystem');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('')->form([
            'name' => 'ESP-004',
        ]);
        $crawler = $client->submit($form);

        $this->assertSelectorTextContains('td:contains("ESP-004")', 'ESP-004', 'The table should contain the text ESP-004.');
        $this->assertSelectorTextNotContains('td', 'ESP-008', 'The table should not contain the text ESP-002.');
    }




}
