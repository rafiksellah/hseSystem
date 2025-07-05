<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email')]
#[UniqueEntity(fields: ['codeAgent'], message: 'Ce code agent est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L\'email ne peut pas être vide')]
    #[Assert\Email(message: 'Veuillez entrer un email valide')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères')]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
    #[Assert\Length(min: 2, max: 100, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères')]
    private ?string $prenom = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: 'Le code agent ne peut pas être vide')]
    #[Assert\Length(min: 3, max: 20, minMessage: 'Le code agent doit contenir au moins {{ limit }} caractères')]
    private ?string $codeAgent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heureCreation = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RapportHSE::class, orphanRemoval: true)]
    private Collection $rapportsHSE;

    public function __construct()
    {
        $this->rapportsHSE = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->heureCreation = new \DateTime();
        $this->roles = ['ROLE_USER'];
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

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getCodeAgent(): ?string
    {
        return $this->codeAgent;
    }

    public function setCodeAgent(string $codeAgent): static
    {
        $this->codeAgent = $codeAgent;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getHeureCreation(): ?\DateTimeInterface
    {
        return $this->heureCreation;
    }

    public function setHeureCreation(\DateTimeInterface $heureCreation): static
    {
        $this->heureCreation = $heureCreation;
        return $this;
    }

    /**
     * @return Collection<int, RapportHSE>
     */
    public function getRapportsHSE(): Collection
    {
        return $this->rapportsHSE;
    }

    public function addRapportsHSE(RapportHSE $rapportsHSE): static
    {
        if (!$this->rapportsHSE->contains($rapportsHSE)) {
            $this->rapportsHSE->add($rapportsHSE);
            $rapportsHSE->setUser($this);
        }
        return $this;
    }

    public function removeRapportsHSE(RapportHSE $rapportsHSE): static
    {
        if ($this->rapportsHSE->removeElement($rapportsHSE)) {
            if ($rapportsHSE->getUser() === $this) {
                $rapportsHSE->setUser(null);
            }
        }
        return $this;
    }

    public function getFullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }
}