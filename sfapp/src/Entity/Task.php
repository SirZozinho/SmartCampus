<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TaskPriorityState;
use App\Enum\TaskState;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 7, enumType: TaskPriorityState::class)]
    private ?TaskPriorityState $priority = null;

    #[ORM\Column(length: 14, enumType: TaskState::class)]
    private ?TaskState $advancement = null;

    #[ORM\ManyToOne(inversedBy: 'task', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Room $room = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?AcquisitionSystem $acquisitionSystem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getPriority(): ?TaskPriorityState
    {
        return $this->priority;
    }

    public function setPriority(TaskPriorityState $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getAdvancement(): ?TaskState
    {
        return $this->advancement;
    }

    public function setAdvancement(TaskState $advancement): self
    {
        $this->advancement = $advancement;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getAcquisitionSystem(): ?AcquisitionSystem
    {
        return $this->acquisitionSystem;
    }

    public function setAcquisitionSystem(AcquisitionSystem $acquisitionSystem): static
    {
        $this->acquisitionSystem = $acquisitionSystem;

        return $this;
    }
}
