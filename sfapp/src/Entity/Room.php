<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use App\Enum\RoomState;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoomRepository::class)]

#[ORM\Table(name: 'room', uniqueConstraints: [
    new ORM\UniqueConstraint(columns: ['name']),
])]

class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15, unique: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $floor = null;

    #[ORM\Column(type: 'string', enumType: RoomState::class)]
    private RoomState $state;

    #[ORM\OneToOne(inversedBy: 'room', cascade: ['persist', 'remove'])]
    private ?AcquisitionSystem $acquisitionSystem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = substr($name, 0, 15);
        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): static
    {
        $this->floor = $floor;

        return $this;
    }

    public function getState(): RoomState
    {
        return $this->state;
    }

    public function setState(RoomState $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getAcquisitionSystem(): ?AcquisitionSystem
    {
        return $this->acquisitionSystem;
    }

    public function setAcquisitionSystem(?AcquisitionSystem $acquisitionSystem): static
    {
        $this->acquisitionSystem = $acquisitionSystem;

        return $this;
    }
}
