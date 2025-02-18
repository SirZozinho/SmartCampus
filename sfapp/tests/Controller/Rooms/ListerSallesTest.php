<?php

namespace App\Tests\Controller\Rooms;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListerSallesTest extends WebTestCase
{
    public function testPageRoomDisponible(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);

        $crawler = $client->request('GET', '/rooms');

        $this->assertResponseIsSuccessful(); // Vérifie que le code de réponse est 200
    }

    public function testPageRoomTableau(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/rooms');

        // Vérifie que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifie que le tableau existe
        $this->assertSelectorExists('table');

        // Vérifie que le tableau a les en-têtes corrects
        $this->assertSelectorTextContains('table thead tr th:nth-child(2)', 'Nom');
        $this->assertSelectorTextContains('table thead tr th:nth-child(3)', 'Étage');
        $this->assertSelectorTextContains('table thead tr th:nth-child(4)', 'Capteur associé');
        $this->assertSelectorTextContains('table thead tr th:nth-child(5)', 'Actions');

        // Vérifie le nombre de lignes dans le tableau (en supposant qu'il y a un en-tête)
        $this->assertGreaterThan(1, $crawler->filter('table tbody tr')->count(), 'Le tableau doit contenir au moins une ligne de données.');

        // Vérifie la présence de certaines données spécifiques dans le tableau
        $this->assertSelectorTextContains('table tbody tr:nth-child(1) td:nth-child(2)', 'C004', 'La salle D005 doit être présente dans le tableau.');
        $this->assertSelectorTextContains('table tbody tr:nth-child(2) td:nth-child(2)', 'C007', 'La salle D302 doit être présente dans le tableau.');

        // Vérifie que chaque ligne a le bon nombre de colonnes
        $crawler->filter('table tbody tr')->each(function ($tr) {
            $this->assertCount(5, $tr->filter('td'), 'Chaque ligne doit avoir exactement 4 colonnes.');
        });
    }
}
