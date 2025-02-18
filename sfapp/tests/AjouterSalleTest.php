<?php

namespace App\Tests;

use App\Enum\RoomState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\RoomRepository;
use App\Entity\Room;

class AjouterSalleTest extends WebTestCase
{
    public function testPageAddDisponible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/rooms/add');

        $this->assertResponseIsSuccessful(); // Vérifie que le code de réponse est 200
    }

    public function testAjouterLesDonnéesDansLaBaseDeDonnées(): void
    {
        $client = static::createClient();

        // Récupérer le conteneur de services
        $container = self::getContainer();

        // Créer un mock du RoomRepository
        $roomRepositoryMock = $this->createMock(RoomRepository::class);
        $roomRepositoryMock->method('findAll')->willReturn([
            (new Room())
                ->setId(1)
                ->setName('D005')
                ->setFloor('0')
                ->setState(RoomState::DISPONIBLE),
        ]);

        $rooms = $roomRepositoryMock->findAll();

        $this->assertCount(1, $rooms); // Vérifie qu'il y a deux salles
        $this->assertEquals(1, $rooms[0]->getId()); // Vérifie que l'id de la première salle est 1
        $this->assertEquals('D005', $rooms[0]->getName()); // Vérifie que le nom de la première salle est 'D005'
        $this->assertEquals('0', $rooms[0]->getFloor()); // Vérifie que l'étage de la première salle est '0'
        $this->assertEquals(RoomState::DISPONIBLE, $rooms[0]->getState());
    }

    public function testErreurDuplicationDeNomDeSalle(): void
    {
        $system = new Room();

        $roomRepositoryMock = $this->createMock(RoomRepository::class);
        $roomRepositoryMock->method('findAll')->willReturn([
            (new Room())
                ->setId(1)
                ->setName('D005')
                ->setFloor('0')
                ->setState(RoomState::DISPONIBLE),
        ]);

        $system->setName('D005'); // Nom de la salle qui existe déjà

        // Variable pour vérifier si l'exception est levée
        $isValid = true;

        try {
            // Logique qui vérifie si la salle existe déjà
            $existingRooms = $roomRepositoryMock->findAll();
            foreach ($existingRooms as $existingRoom) {
                if ($existingRoom->getName() === $system->getName()) {
                    throw new \Exception('Cette salle existe déjà.');
                }
            }
            // Si on ne passe pas par l'exception, on met isValid à false
            $isValid = false;
        } catch (\Exception $e) {
            // Si une exception est levée, c'est que la salle existe déjà, donc isValid reste true
            $isValid = true;
        }
        $this->assertTrue($isValid, 'Cette salle existe déjà.');
    }

    public function testFormatNom(): void
    {
        $system = new Room();

        $roomRepositoryMock = $this->createMock(RoomRepository::class);
        $roomRepositoryMock->method('findAll')->willReturn([
            (new Room())
                ->setId(1)
                ->setName('TAFYGSJKQU')
                ->setFloor('0')
                ->setState(RoomState::DISPONIBLE),
        ]);

        $rooms = $roomRepositoryMock->findAll();
        $this->assertEquals('TAFY', $rooms[0]->getName());
    }
}
