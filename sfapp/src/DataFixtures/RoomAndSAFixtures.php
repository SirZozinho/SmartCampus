<?php

namespace App\DataFixtures;

use App\Entity\AcquisitionSystem;
use App\Entity\room;
use App\Enum\AcquisitionSystemState;
use App\Enum\RoomState;

use App\Repository\AcquisitionSystemRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoomAndSAFixtures extends Fixture
{
    /**
     * @brief Creates room examples
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $associations = [
            ['ESP-004', 'D205', '2'],
            ['ESP-008', 'D206', '2'],
            ['ESP-006', 'D207', '2'],
            ['ESP-014', 'D204', '2'],
            ['ESP-012', 'D203', '2'],
            ['ESP-005', 'D303', '3'],
            ['ESP-011', 'D304', '3'],
            ['ESP-007', 'C101', '1'],
            ['ESP-024', 'D109', '1'],
            ['ESP-026', 'Secrétariat', '0'],
            ['ESP-030', 'D001', '0'],
            ['ESP-028', 'D002', '0'],
            ['ESP-020', 'D004', '0'],
            ['ESP-021', 'C004', '0'],
            ['ESP-022', 'C007', '0'],
        ];

        foreach ($associations as $association) {
            // Création du système d'acquisition
            $AcquisitionSystem = new AcquisitionSystem();
            $AcquisitionSystem->setName($association[0]);
            $AcquisitionSystem->setState(AcquisitionSystemState::FUNCTIONAL);
            $manager->persist($AcquisitionSystem);

            // Création de la salle
            $Room = new Room();
            $Room->setName($association[1]);
            $Room->setFloor($association[2]);
            $Room->setState(RoomState::EQUIPED);
            $Room->setAcquisitionSystem($AcquisitionSystem); // Association avec le SA
            $manager->persist($Room);
        }

        $manager->flush();
    }
}
