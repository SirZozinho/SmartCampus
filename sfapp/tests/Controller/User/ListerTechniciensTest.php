<?php

namespace App\Tests\Controller\User;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ListerTechniciensTest extends WebTestCase
{
    public function testPageListerUtilisateurs() {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/technicians');
        $this->assertResponseIsSuccessful();
    }

    public function testListerUtilisateurs() {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);


        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/technicians');

        $this->assertSelectorExists('table');

        //Checks table header
        $this->assertSelectorTextContains('table thead tr th:nth-child(1)', "Nom du technicien");
        $this->assertSelectorTextContains('table thead tr th:nth-child(2)', 'Rôle');
        $this->assertSelectorTextContains('table thead tr th:nth-child(3)', 'Action');

        //Checks if all technicians appears correctly (require fixtures)
        for ($i = 0; $i < 10; $i++) {
            $this->assertSelectorTextContains('table tbody tr:nth-child('.($i+1).') td:nth-child(1)', "technician".$i, "Le nom du technicien ".$i."n'est pas affiché ou nulle");
            $this->assertSelectorTextContains('table tbody tr:nth-child('.($i+1).') td:nth-child(2)', "Technicien", "Le rôle technicien pour le technicien ".$i."n'est pas affiché ou nulle");
        }

    }

}