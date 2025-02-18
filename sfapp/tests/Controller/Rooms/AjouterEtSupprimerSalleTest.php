<?php

namespace App\Tests\Controller\Rooms;

use App\Entity\Room;
use App\Enum\RoomState;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AjouterEtSupprimerSalleTest extends WebTestCase
{


    public function testPageAddDisponible(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);

        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/rooms/add');

        $this->assertResponseIsSuccessful(); // Vérifie que le code de réponse est 200
    }

    public function testAjouterEtSupprimerUneSalle(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $container = $client->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $randomRoomName = 'Room_' . bin2hex(random_bytes(2)); //Generates random room name

        //Do the request to add room page
        $crawler = $client->request('GET', '/rooms/add');

        //Add room information into the form
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => $randomRoomName,
            'room[floor]' => 0,
            'room[state]' => 0,
        ]);
        $client->submit($form);

        //Check by the repository if room exists
        $roomRepository = $entityManager->getRepository(Room::class);
        $rooms = $roomRepository->findBy(['name' => $randomRoomName]);
        $this->assertEquals($randomRoomName, $rooms[0]->getName());
        $this->assertEquals(0, $rooms[0]->getFloor());
        $this->assertEquals(RoomState::AVAILABLE, $rooms[0]->getState());

        //Delete room from route
        $client->loginUser($adminUser);
        $crawler = $client->request('POST', '/rooms/delete/' . $rooms[0]->getId());


        //Checks if room has been deleted correctly
        $roomsAfterDeletion = $roomRepository->findBy(['name' => $randomRoomName]);
        $this->assertCount(0, $roomsAfterDeletion);

    }

    public function testErreurDuplicationDeNomDeSalle(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $roomRepository = static::getContainer()->get(RoomRepository::class);

        // Essayer d'ajouter une autre salle avec le même nom via le formulaire
        $crawler = $client->request('GET', '/rooms/add');
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'D005',
            'room[floor]' => 1,
            'room[state]' => 0,
        ]);

        $client->submit($form);

        // Vérifier que la salle n'a pas été ajoutée
        $rooms = $roomRepository->findBy(['name' => 'D005']);
        $this->assertCount(1, $rooms, 'Cette salle existe déjà.');

    }

    public function testFormatNomViaFormulaire(): void
    {
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');


        $client->loginUser($adminUser);
        $crawler = $client->request('GET', '/rooms/add');

        $this->assertResponseIsSuccessful(); // Vérifie que le code de réponse est 200

        // Remplir le formulaire et le soumettre
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'TAFYGSJKQUJEUSKASA',
            'room[floor]' => 0,
            'room[state]' => 0,
        ]);
        $client->submit($form);

        // Vérifier que la salle a été ajoutée avec le nom formaté
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $rooms = $roomRepository->findBy(['name' => 'TAFYGSJKQUJEUSK']);

        $this->assertCount(1, $rooms);
        $this->assertEquals('TAFYGSJKQUJEUSK', $rooms[0]->getName());


        // Supprimer la salle après le test
        $testRoom = $entityManager->find(Room::class, $rooms[0]->getId()); //Get managed data to delete the test room
        $entityManager->remove($testRoom);
        $entityManager->flush();
    }
}
