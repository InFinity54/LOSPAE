<?php

namespace App\Entity;

use App\Repository\StudentNoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentNoteRepository::class)]
class StudentNote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'note', cascade: ['persist', 'remove'])]
    private ?User $student = null;

    #[ORM\Column]
    private ?float $currentNote = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCurrentNote(): ?float
    {
        return $this->currentNote;
    }

    public function setCurrentNote(float $currentNote): static
    {
        $this->currentNote = $currentNote;

        return $this;
    }
}
