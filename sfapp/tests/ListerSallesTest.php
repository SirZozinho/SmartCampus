<?php

namespace App\Tests;

use App\Enum\RoomState;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\RoomRepository;
use App\Entity\Room;

class ListerSallesTest extends WebTestCase
{
    public function testPageRoomDisponible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/rooms');

        $this->assertResponseIsSuccessful(); // Vérifie que le code de réponse est 200
    }

    public function testPageRoomTableau(): void
    {
        $room = static::createClient();
        $crawler = $room->request('GET', '/rooms');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('table');
    }

    public function testPageRoomAfficherSalle(): void
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
            (new Room())
                ->setId(2)
                ->setName('D302')
                ->setFloor('3')
                ->setState(RoomState::DISPONIBLE),
        ]);

        $rooms = $roomRepositoryMock->findAll();

        // Remplacer le service RoomRepository par le mock
        $container->set(RoomRepository::class, $roomRepositoryMock);

        // Effectuer la requête GET vers la page
        $crawler = $client->request('GET', '/rooms');

        // Vérifier que la réponse est réussie
        $this->assertResponseIsSuccessful();

        $this->assertCount(2, $rooms);
    }
}
