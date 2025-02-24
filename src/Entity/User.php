<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $avatar = "default_avatar.svg";

    #[ORM\Column]
    private ?bool $isActivated = false;

    #[ORM\ManyToOne]
    private ?School $school = null;

    #[ORM\ManyToOne(inversedBy: 'students')]
    private ?Promotion $promotion = null;

    /**
     * @var Collection<int, CurrentNote>
     */
    #[ORM\OneToMany(targetEntity: CurrentNote::class, mappedBy: 'student')]
    private Collection $currentNotes;

    /**
     * @var Collection<int, NoteChange>
     */
    #[ORM\OneToMany(targetEntity: NoteChange::class, mappedBy: 'student')]
    private Collection $noteChanges;

    public function __construct()
    {
        $this->currentNotes = new ArrayCollection();
        $this->noteChanges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function isActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setActivated(bool $isActivated): static
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getPromotion(): ?Promotion
    {
        return $this->promotion;
    }

    public function setPromotion(?Promotion $promotion): static
    {
        $this->promotion = $promotion;

        return $this;
    }

    /**
     * @return Collection<int, CurrentNote>
     */
    public function getCurrentNotes(): Collection
    {
        return $this->currentNotes;
    }

    public function addCurrentNote(CurrentNote $currentNote): static
    {
        if (!$this->currentNotes->contains($currentNote)) {
            $this->currentNotes->add($currentNote);
            $currentNote->setStudent($this);
        }

        return $this;
    }

    public function removeCurrentNote(CurrentNote $currentNote): static
    {
        if ($this->currentNotes->removeElement($currentNote)) {
            // set the owning side to null (unless already changed)
            if ($currentNote->getStudent() === $this) {
                $currentNote->setStudent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, NoteChange>
     */
    public function getNoteChanges(): Collection
    {
        return $this->noteChanges;
    }

    public function addNoteChange(NoteChange $noteChange): static
    {
        if (!$this->noteChanges->contains($noteChange)) {
            $this->noteChanges->add($noteChange);
            $noteChange->setStudent($this);
        }

        return $this;
    }

    public function removeNoteChange(NoteChange $noteChange): static
    {
        if ($this->noteChanges->removeElement($noteChange)) {
            // set the owning side to null (unless already changed)
            if ($noteChange->getStudent() === $this) {
                $noteChange->setStudent(null);
            }
        }

        return $this;
    }
}
