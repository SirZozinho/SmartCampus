<?php

namespace App\Tests\Controller\AcquisitionSystem;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListerSystemeAcquisitionTest extends WebTestCase
{
    public function testPageListerAS(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/acquisitionsystem');
        $this->assertResponseIsSuccessful();
    }

    public function testPageASTableau(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/acquisitionsystem');

        // Vérifie que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifie que le tableau existe
        $this->assertSelectorExists('table');

        // Vérifie que le tableau a les en-têtes corrects
        $this->assertSelectorTextContains('table thead tr th:nth-child(2)', 'Nom');
        $this->assertSelectorTextContains('table thead tr th:nth-child(3)', 'État');
        $this->assertSelectorTextContains('table thead tr th:nth-child(4)', 'Salle associée');
        $this->assertSelectorTextContains('table thead tr th:nth-child(5)', 'Actions');

        //Vérifie les nom des SA
        $this->assertSelectorTextContains('table tbody tr:nth-child(1) td:nth-child(2)', 'ESP-004', 'Le capteur ESP-001 doit être présente dans le tableau.');
        $this->assertSelectorTextContains('table tbody tr:nth-child(2) td:nth-child(2)', 'ESP-008', 'Le capteur ESP-002 être présente dans le tableau.');

        //Vérifie l'état des SA
        $this->assertSelectorTextContains('table tbody tr:nth-child(1) td:nth-child(3)', 'FONCTIONNEL', "L'état du SA n'est pas affiché n'est pas le bon");
        $this->assertSelectorTextContains('table tbody tr:nth-child(2) td:nth-child(3)', 'FONCTIONNEL', 'Le capteur ESP-002 être présente dans le tableau.');

        $this->assertSelectorTextContains('table tbody tr:nth-child(1) td:nth-child(4)', 'D205', "Le capteur ESP-001 n'est pas affiché ou ne possède pas de le bon état");
        $this->assertSelectorTextContains('table tbody tr:nth-child(2) td:nth-child(4)', 'D206', "Le capteur ESP-002 n'est pas affiché ou ne possède pas de le bon état");

        // Vérifie que chaque ligne a le bon nombre de colonnes
        $crawler->filter('table tbody tr')->each(function ($tr) {
            $this->assertCount(5, $tr->filter('td'), 'Chaque ligne doit avoir exactement 5 colonnes.');
        });
    }
}
