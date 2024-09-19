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
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
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
    private ?bool $isActivated = null;

    #[ORM\Column(nullable: true)]
    private ?float $currentNote = null;

    #[ORM\OneToMany(targetEntity: NoteChange::class, mappedBy: 'student', orphanRemoval: true)]
    private Collection $noteChanges;

    #[ORM\Column(length: 6, nullable: true)]
    private ?string $recoveryCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $recoveryCodeExpireAt = null;

    public function __construct()
    {
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
    public function getPassword(): string
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

    public function isIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): static
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    public function getCurrentNote(): ?float
    {
        return $this->currentNote;
    }

    public function setCurrentNote(?float $currentNote): static
    {
        $this->currentNote = $currentNote;

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

    public function getRecoveryCode(): ?string
    {
        return $this->recoveryCode;
    }

    public function setRecoveryCode(?string $recoveryCode): static
    {
        $this->recoveryCode = $recoveryCode;

        return $this;
    }

    public function getRecoveryCodeExpireAt(): ?\DateTimeInterface
    {
        return $this->recoveryCodeExpireAt;
    }

    public function setRecoveryCodeExpireAt(?\DateTimeInterface $recoveryCodeExpireAt): static
    {
        $this->recoveryCodeExpireAt = $recoveryCodeExpireAt;

        return $this;
    }
}
