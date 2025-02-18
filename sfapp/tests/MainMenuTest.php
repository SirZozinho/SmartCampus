<?php

namespace App\Tests;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use App\Enum\RoomState;
use App\Repository\AcquisitionSystemRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainMenuTest extends WebTestCase
{


    public function testMainMenuResponseOk(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testRoomCardDisplaysCorrectData(): void
    {

        //Defines the client that is connecting to the app
        $client = static::createClient();
        $container = static::getContainer();

        //Create a test acquisition system
        $testAS = new AcquisitionSystem();
        $testAS->setName('AS');
        $testAS->setTemperature(21);
        $testAS->setAirQuality(1501);
        $testAS->setHumidity(81);

        //Create a test room
        $testRoom = new Room();
        $testRoom->setName('test');
        $testRoom->setState(RoomState::EQUIPED);
        $testRoom->setAcquisitionSystem($testAS);


        $testASRepo = $this->createMock(AcquisitionSystemRepository::class);
        $testASRepo->expects($this->any())
            ->method('find')
            ->willReturn($testAS);

        $testRoomRepo = $this->createMock(RoomRepository::class);
        $testRoomRepo->expects($this->any())
            ->method('findBy')
            ->willReturn([$testRoom]);

        $container->set(RoomRepository::class, $testRoomRepo);
        $container->set(AcquisitionSystemRepository::class, $testASRepo);


        // Accéder à la page principale
        $crawler = $client->request('GET', '/');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        // Vérifier l'existence des cartes
        $roomCards = $crawler->filter('.widget-component-minimised');
        $this->assertGreaterThan(0, $roomCards->count(), 'Les cartes des salles sont affichées.');

        // Vérifier les données d'une carte spécifique
        $firstCard = $roomCards->first();

        // Vérifier le nom de la salle
        $this->assertStringContainsString('test', $firstCard->filter('.widget-component-title h3')->text(), 'Le nom de la salle est affiché.');

        // Vérifier la température
        $this->assertStringContainsString('21', $firstCard->filter('.widget-info-div')->eq(0)->text(), 'La température est affichée.');

        // Vérifier la qualité de l'air
        $this->assertStringContainsString('1501', $firstCard->filter('.widget-info-div')->eq(1)->text(), 'La qualité de l\'air est affichée.');

        // Vérifier l'humidité
        $this->assertStringContainsString('81', $firstCard->filter('.widget-info-div')->eq(2)->text(), 'L\'humidité est affichée.');
    }






}
