<?php

namespace App\Entity;

use App\Repository\AcquisitionSystemRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\AcquisitionSystemState;

#[ORM\Entity(repositoryClass: AcquisitionSystemRepository::class)]

#[ORM\Table(name: 'acquisition_system', uniqueConstraints: [
    new ORM\UniqueConstraint(columns: ['name']),
])]
class AcquisitionSystem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 15, unique: true)]
    private ?string $name = null;

    #[ORM\OneToOne(mappedBy: 'acquisitionSystem', cascade: ['persist', 'remove'])]
    private ?Room $room = null;

    #[ORM\Column(type: 'string', enumType: AcquisitionSystemState::class)]
    private ?AcquisitionSystemState $state = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setId(?int $id) {
        $this->id = $id;
    }


    public function setRoom(?Room $room): static
    {
        // unset the owning side of the relation if necessary
        if ($room === null && $this->room !== null) {
            $this->room->setAcquisitionSystem(null);
        }

        // set the owning side of the relation if necessary
        if ($room !== null && $room->getAcquisitionSystem() !== $this) {
            $room->setAcquisitionSystem($this);
        }

        $this->room = $room;

        return $this;
    }

    public function getState(): AcquisitionSystemState
    {
        return $this->state;
    }

    public function setState(AcquisitionSystemState $state): self
    {
        $this->state = $state;
        return $this;
    }
}
