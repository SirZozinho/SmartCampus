<?php

namespace App\Tests\Controller\Rooms;

use App\Entity\Room;
use App\Enum\RoomState;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModifierSalleTest extends WebTestCase
{
    public function testModifierNomSalle(): void
    {
        //Connect http client to the website
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $roomRepository = static::getContainer()->get(RoomRepository::class);

        //Get entity manager
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        //*************************
        //Create original testRoom
        //*************************
        $room = new Room();
        $room->setName('TestRoom'); // Nom initial de la salle
        $room->setFloor('0');
        $room->setState(RoomState::AVAILABLE);

        $entityManager->persist($room);
        $entityManager->flush();

        $roomId = $room->getId();

        //************
        //Modify name
        //************
        $crawler = $client->request('GET', '/rooms/modify/' . $roomId);
        // Remplir le formulaire de modification et le soumettre
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'TestRoomMd',
            'room[floor]' => '0',
            'room[state]' => 0,
        ]);
        $client->submit($form);

        //************************
        // Test name modification
        //************************
        $rooms = $roomRepository->findBy(['name' => 'TestRoomMd']);
        $this->assertCount(1, $rooms); // Il devrait y avoir une seule salle
        $this->assertEquals($roomId, $rooms[0]->getId()); // L'ID de la salle reste inchangé
        $this->assertEquals('TestRoomMd', $rooms[0]->getName()); // Le nom de la salle doit être changé à 'D302'
        $this->assertEquals('0', $rooms[0]->getFloor()); // L'étage de la salle reste le même
        $this->assertEquals(RoomState::AVAILABLE, $rooms[0]->getState()); // L'état reste inchangé

        //*******************
        // Database clean up
        //*******************
        $room = $entityManager->find(Room::class, $roomId);
        $entityManager->remove($room);
        $entityManager->flush();


    }

    public function testModifierEtageSalle(): void {
        //Connect http client to the website
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $roomRepository = static::getContainer()->get(RoomRepository::class);

        //Get entity manager
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        //*************************
        //Create original testRoom
        //*************************
        $roomEt = new Room();
        $roomEt->setName('TestRoomEt'); // Nom initial de la salle
        $roomEt->setFloor('0');
        $roomEt->setState(RoomState::AVAILABLE);

        $entityManager->persist($roomEt);
        $entityManager->flush();

        $roomIdEt = $roomEt->getId();

        //*************
        //Modify floor
        //*************
        $crawler = $client->request('GET', '/rooms/modify/' . $roomIdEt);
        // Remplir le formulaire de modification et le soumettre
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'TestRoomEt',
            'room[floor]' => '2',
            'room[state]' => 0,
        ]);
        $client->submit($form);

        //*************************
        // Test floor modification
        //*************************
        $rooms = $roomRepository->findBy(['name' => 'TestRoomEt']);
        $this->assertCount(1, $rooms); // Il devrait y avoir une seule salle
        $this->assertEquals($roomIdEt, $rooms[0]->getId()); // L'ID de la salle reste inchangé
        $this->assertEquals('TestRoomEt', $rooms[0]->getName()); // Le nom de la salle doit être changé à 'D302'
        $this->assertEquals('2', $rooms[0]->getFloor()); // L'étage de la salle reste le même
        $this->assertEquals(RoomState::AVAILABLE, $rooms[0]->getState()); // L'état reste inchangé

        //*******************
        // Database clean up
        //*******************
        $roomEt = $entityManager->find(Room::class, $roomIdEt);
        $entityManager->remove($roomEt);
        $entityManager->flush();

    }

    public function testModifierState(): void {
        //Connect http client to the website
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $roomRepository = static::getContainer()->get(RoomRepository::class);

        //Get entity manager
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        //*************************
        //Create original testRoom
        //*************************
        $roomSt = new Room();
        $roomSt->setName('TestRoomEt'); // Nom initial de la salle
        $roomSt->setFloor('0');
        $roomSt->setState(RoomState::AVAILABLE);

        $entityManager->persist($roomSt);
        $entityManager->flush();

        $roomIdSt = $roomSt->getId();


        //*******************
        // Modify room state
        //*******************
        $crawler = $client->request('GET', '/rooms/modify/' . $roomIdSt);
        // Remplir le formulaire de modification et le soumettre
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'TestRoomEt',
            'room[floor]' => '0',
            'room[state]' => 1,
        ]);
        $client->submit($form);

        //******************************
        // Test room state modification
        //******************************
        $rooms = $roomRepository->findBy(['name' => 'TestRoomEt']);
        $this->assertCount(1, $rooms); // Il devrait y avoir une seule salle
        $this->assertEquals($roomIdSt, $rooms[0]->getId()); // L'ID de la salle reste inchangé
        $this->assertEquals('TestRoomEt', $rooms[0]->getName()); // Le nom de la salle doit être changé à 'D302'
        $this->assertEquals('0', $rooms[0]->getFloor()); // L'étage de la salle reste le même
        $this->assertEquals(RoomState::UNAVAILABLE, $rooms[0]->getState()); // L'état reste inchangé

        //*******************
        // Database clean up
        //*******************
        $roomEt = $entityManager->find(Room::class, $roomIdSt);
        $entityManager->remove($roomEt);
        $entityManager->flush();
    }



    public function testErreurDuplicationDeNomDeSalle(): void
    {
        //Connect http client to the website
        $client = static::createClient();
        $adminUser = static::getContainer()->get(UserRepository::class)->find(1);
        $client->loginUser($adminUser);
        $roomRepository = static::getContainer()->get(RoomRepository::class);

        //Get entity manager
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        //*************************
        //Create original testRoom
        //*************************
        $roomDp = new Room();
        $roomDp->setName('TestNomDp'); // Nom initial de la salle
        $roomDp->setFloor('0');
        $roomDp->setState(RoomState::AVAILABLE);

        $entityManager->persist($roomDp);

        $roomNonDp = new Room();
        $roomNonDp->setName('TestNom'); // Nom initial de la salle
        $roomNonDp->setFloor('0');
        $roomNonDp->setState(RoomState::AVAILABLE);

        $entityManager->persist($roomNonDp);
        $entityManager->flush();

        //****************************
        // Trying modify another room
        //****************************
        $crawler = $client->request('GET', '/rooms/modify/' . $roomNonDp->getId());
        // Remplir le formulaire de modification et le soumettre
        $form = $crawler->selectButton('Confirmer')->form([
            'room[name]' => 'TestNomDp',
            'room[floor]' => '0',
            'room[state]' => 0,
        ]);
        $client->submit($form);

        //**************************************************************
        // Check if name of non duplicated room is the same than before
        //**************************************************************
        $rooms = $roomRepository->findBy(['name' => 'TestNom']);
        $this->assertCount(1, $rooms);

        //*******************
        // Database clean up
        //*******************
        $roomDel1 = $entityManager->find(Room::class, $roomDp->getId());
        $roomDel2 = $entityManager->find(Room::class, $roomNonDp->getId());
        $entityManager->remove($roomDel1);
        $entityManager->remove($roomDel2);
        $entityManager->flush();


    }

}
