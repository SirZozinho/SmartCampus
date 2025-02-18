<?php

namespace App\Tests\Controller\AcquisitionSystem;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use App\Enum\AcquisitionSystemState;
use App\Repository\AcquisitionSystemRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AjouterEtSupprimerSATest extends WebTestCase
{
    public function testReponseFormulaireSA(): void {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);

        $crawler = $client->request('GET', '/acquisitionsystem/add');
        $this->assertResponseIsSuccessful("La page ne réponds pas.");
    }

    public function testAjouterSupprimerSA(): void {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $asRepository = static::getContainer()->get(AcquisitionSystemRepository::class);
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');


        //***************************************
        //Add a new Acquisition system from form
        //***************************************
        $crawler = $client->request('GET', '/acquisitionsystem/add');
        $form = $crawler->selectButton('Confirmer')->form([
            'acquisition_system[name]' => 'ESP-TST',
        ]);
        $crawler = $client->submit($form);
        $client->followRedirect();

        //************************************
        // Testing by getting from repository
        //************************************
        $testAddAs = $asRepository->findBy(['name' => 'ESP-TST']);
        $this->assertCount(1, $testAddAs);
        $this->assertEquals('ESP-TST', $testAddAs[0]->getName());
        $this->assertEquals(AcquisitionSystemState::DEFAULTER , $testAddAs[0]->getState());

        //**********************
        // Remove SA from route
        //**********************
        $crawler = $client->request('POST', '/acquisitionsystem/ESP-TST/delete');

        //******************
        // Check if removed
        //******************
        $testDeleteAs = $asRepository->findBy(['name' => 'ESP-TST']);
        $this->assertCount(0, $testDeleteAs);
    }

    public function testAjouterSAAvecMemeNom(): void {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $asRepository = static::getContainer()->get(AcquisitionSystemRepository::class);
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        //******************
        // Create test data
        //******************
        $asNonDp = new AcquisitionSystem();
        $asNonDp->setName('ESP-TST');
        $asNonDp->setState(AcquisitionSystemState::DEFAULTER);
        $entityManager->persist($asNonDp);
        $entityManager->flush();

        //**********************************
        // Trying add new as with same name
        //**********************************
        $crawler = $client->request('GET', '/acquisitionsystem/add');
        $form = $crawler->selectButton('Confirmer')->form([
            'acquisition_system[name]' => 'ESP-TST',
        ]);
        $crawler = $client->submit($form);

        //******************************
        // Testing if there two same as
        //******************************
        $testAddAs = $asRepository->findBy(['name' => 'ESP-TST']);
        $this->assertCount(1, $testAddAs);

        //******************
        // Database cleanup
        //******************
        $testDeleteAs = $entityManager->find(AcquisitionSystem::class, $testAddAs[0]->getId());
        $entityManager->remove($testDeleteAs);
        $entityManager->flush();
    }


}