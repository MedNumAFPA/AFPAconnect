<?php

namespace App\Entity;

use App\Repository\WorkshopRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkshopRepository::class)]
class Workshop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $maxSlots = null;

    /**
     * @var Collection<int, TimeSlot>
     */
    #[ORM\OneToMany(targetEntity: TimeSlot::class, mappedBy: 'workshop', cascade: ['persist'])]
    private Collection $timeSlots;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roomNumber = null;

    public function __construct()
    {
        $this->timeSlots = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getMaxSlots(): ?int
    {
        return $this->maxSlots;
    }

    public function setMaxSlots(int $maxSlots): static
    {
        $this->maxSlots = $maxSlots;
        return $this;
    }
    /**
     * @return Collection<int, TimeSlot>
     */
    public function getTimeSlots(): Collection
    {
        return $this->timeSlots;
    }

    public function addTimeSlot(TimeSlot $timeSlot): static
    {
        if (!$this->timeSlots->contains($timeSlot)) {
            $this->timeSlots->add($timeSlot);
            $timeSlot->setWorkshop($this);
        }
        return $this;
    }

    public function removeTimeSlot(TimeSlot $timeSlot): static
    {
        if ($this->timeSlots->removeElement($timeSlot)) {
            if ($timeSlot->getWorkshop() === $this) {
                $timeSlot->setWorkshop(null);
            }
        }
        return $this;
    }

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(?string $roomNumber): static
    {
        $this->roomNumber = $roomNumber;

        return $this;
    }
}
