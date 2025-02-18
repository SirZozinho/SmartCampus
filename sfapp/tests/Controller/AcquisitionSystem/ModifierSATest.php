<?php

namespace App\Tests\Controller\AcquisitionSystem;

use App\Entity\AcquisitionSystem;
use App\Enum\AcquisitionSystemState;
use App\Repository\AcquisitionSystemRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModifierSATest extends WebTestCase
{

    public function testModifierSA(): void {
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

        //*************
        // Modify data
        //*************
        $crawler = $client->request('GET', '/acquisitionsystem/ESP-TST/modify');
        $form = $crawler->selectButton('Confirmer')->form([
            'acquisition_system[name]' => 'ESP-MOD',
        ]);
        $crawler = $client->submit($form);

        //********************
        // Check modification
        //********************
        $testedSAS = $asRepository->findBy(['name' => 'ESP-MOD']);
        $this->assertCount(1, $testedSAS);
        $this->assertEquals('ESP-MOD', $testedSAS[0]->getName());

        //******************
        // Cleanup database
        //******************
        $rmAs = $entityManager->find(AcquisitionSystem::class, $testedSAS[0]->getId());
        $entityManager->remove($rmAs);
    }

    public function testModifierSAAvecNomDuplique(): void {
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

        $asDp = new AcquisitionSystem();
        $asDp->setName('ESP-MOD');
        $asDp->setState(AcquisitionSystemState::DEFAULTER);
        $entityManager->persist($asNonDp);
        $entityManager->flush();

        //**************************
        // Modify data to be a copy
        //**************************
        $crawler = $client->request('GET', '/acquisitionsystem/ESP-MOD/modify');
        $form = $crawler->selectButton('Confirmer')->form([
            'acquisition_system[name]' => 'ESP-TST',
        ]);
        $crawler = $client->submit($form);

        //********************
        // Check modification
        //********************
        $testedFirstAS = $asRepository->findBy(['name' => 'ESP-MOD']);
        $this->assertCount(1, $testedFirstAS);

        $testedSecondAS = $asRepository->findBy(['name' => 'ESP-TST']);
        $this->assertCount(1, $testedSecondAS);

        //******************
        // Database cleanup
        //******************
        $esp1 = $entityManager->find(AcquisitionSystem::class, $testedFirstAS[0]->getId());
        $esp2 = $entityManager->find(AcquisitionSystem::class, $testedSecondAS[0]->getId());
        $entityManager->remove($esp1);
        $entityManager->remove($esp2);
        $entityManager->flush();
    }




}