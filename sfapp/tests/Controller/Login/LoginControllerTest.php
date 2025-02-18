<?php

namespace App\Tests\Controller\Login;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
   public function testPageConnexion(): void {
       $client = static::createClient();

       $crawler = $client->request('GET', '/login');
       $this->assertResponseIsSuccessful("La page de connexion ne réponds pas.");
   }

   public function testFormulaireConnexion(): void {
       $client = static::createClient();
       $crawler = $client->request('GET', '/login');
       $form = $crawler->selectButton('Se connecter')->form([
           '_username' => 'admin',
           '_password' => 'admin',
       ]);
       $client->submit($form);

       $this->assertResponseRedirects('/dashboard');
   }

}
