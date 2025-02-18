<?php

namespace App\DataFixtures;

use App\Entity\AcquisitionSystem;
use App\Entity\Room;
use App\Entity\Task;
use App\Entity\User;
use App\Enum\AcquisitionSystemState;
use App\Enum\RoomState;
use App\Enum\TaskPriorityState;
use App\Enum\TaskState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("test@gmail.com");
        $user->setPassword("test");
        $user->setRoles(["ROLE_USER"]);
        $user->setLogin("test");
        $user->setId(999);

        $manager->persist($user);

        $room = new Room();
        $room->setName("Room Test");
        $room->setId(999);
        $room->setState(RoomState::AVAILABLE);
        $room->setAcquisitionSystem(null);
        $room->setFloor(1);

        $manager->persist($room);

        $SA = new AcquisitionSystem();
        $SA->setName("SA_Test");
        $SA->setState(AcquisitionSystemState::FUNCTIONAL);
        $SA->setRoom($room);

        $manager->persist($SA);

        $task = new Task();
        $task->setLabel("Task 1");
        $task->setUser($user);
        $task->setAdvancement(TaskState::DOING);
        $task->setPriority(TaskPriorityState::HIGH);
        $task->setRoom($room);
        $task->setAcquisitionSystem($SA);
        $manager->persist($task);

        $manager->flush();
    }
}
