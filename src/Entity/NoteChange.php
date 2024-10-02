<?php

namespace App\Entity;

use App\Repository\NoteChangeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NoteChangeRepository::class)]
class NoteChange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $teacher = null;

    #[ORM\ManyToOne(inversedBy: 'noteChanges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $student = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Criteria $criteria = null;

    #[ORM\Column]
    private ?float $impact = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $occuredAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTeacher(): ?User
    {
        return $this->teacher;
    }

    public function setTeacher(?User $teacher): static
    {
        $this->teacher = $teacher;

        return $this;
    }

    public function getStudent(): ?User
    {
        return $this->student;
    }

    public function setStudent(?User $student): static
    {
        $this->student = $student;

        return $this;
    }

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(?Criteria $criteria): static
    {
        $this->criteria = $criteria;

        return $this;
    }

    public function getImpact(): ?float
    {
        return $this->impact;
    }

    public function setImpact(float $impact): static
    {
        $this->impact = $impact;

        return $this;
    }

    public function getOccuredAt(): ?\DateTimeInterface
    {
        return $this->occuredAt;
    }

    public function setOccuredAt(\DateTimeInterface $occuredAt): static
    {
        $this->occuredAt = $occuredAt;

        return $this;
    }
}
