<?php

namespace App\Tests\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AjouterSupprimerTechnicienTest extends WebTestCase
{
    public function testAjouterEtSupprimerTechnicien(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $container = $client->getContainer();
        $userRepository = $container->get(UserRepository::class);

        $client->request('GET', '/technicians/add');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Confirmer', [
            'registration_form[login]' => 'testTechnician',
            'registration_form[plainPassword]' => 'password',
        ]);

        //******************
        // Testing if added
        //******************
        $targetTechnician = $userRepository->findBy(['login'=>'testTechnician']);
        $this->assertCount(1, $targetTechnician);

        //*******************
        // Delete technician
        //*******************
        $targetTechId = $targetTechnician[0]->getId();
        $client->request('POST', '/technicians/remove/'.$targetTechId);

        //********************
        // Testing if deleted
        //********************
        $responseAfterDeletion = $userRepository->findBy(['login'=>'testTechnician']);
        $this->assertCount(0, $responseAfterDeletion);
    }

    public function testAjouterMemeTechnicien(): void {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $container = $client->getContainer();
        $userRepository = $container->get(UserRepository::class);

        //************************************
        // Try adding user with same username
        //************************************
        $client->request('GET', '/technicians/add');
        $this->assertResponseIsSuccessful();

        $client->submitForm('Confirmer', [
            'registration_form[login]' => 'technician0',
            'registration_form[plainPassword]' => 'password',
        ]);

        //*************************
        // Check duplication error
        //*************************
        $targetTechnician = $userRepository->findBy(['login'=>'technician0']);
        $this->assertCount(1, $targetTechnician);

    }
}
