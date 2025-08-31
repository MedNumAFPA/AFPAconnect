<?php

namespace App\Entity;

use App\Repository\TimeSlotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeSlotRepository::class)]
class TimeSlot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $fromTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $toTime = null;

    #[ORM\ManyToOne(inversedBy: 'timeSlots')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Workshop $workshop = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'timeslot', orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getFromTime(): ?\DateTime
    {
        return $this->fromTime;
    }

    public function setFromTime(\DateTime $fromTime): static
    {
        $this->fromTime = $fromTime;
        return $this;
    }

    public function getToTime(): ?\DateTime
    {
        return $this->toTime;
    }

    public function setToTime(\DateTime $toTime): static
    {
        $this->toTime = $toTime;
        return $this;
    }

    public function getWorkshop(): ?Workshop
    {
        return $this->workshop;
    }

    public function setWorkshop(?Workshop $workshop): static
    {
        $this->workshop = $workshop;
        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setTimeslot($this);
        }
        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getTimeslot() === $this) {
                $reservation->setTimeslot(null);
            }
        }
        return $this;
    }

    public function getChoiceLabel(): string
    {
        // On récupère le nom de l'atelier et on formate la date et l'heure
        $workshopName = $this->getWorkshop()->getName();
        $date = $this->getDate()->format('d/m/Y');
        $fromTime = $this->getFromTime()->format('H:i');
        $toTime = $this->getToTime()->format('H:i');

        // On retourne une chaîne de caractères complète et claire
        return sprintf(
            '%s - %s (%s - %s)',
            $workshopName,
            $date,
            $fromTime,
            $toTime
        );
    }
}
