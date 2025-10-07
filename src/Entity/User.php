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
    public const ZONE_SIMTIS = 'SIMTIS';
    public const ZONE_SIMTIS_TISSAGE = 'SIMTIS TISSAGE';

    public const ZONES_DISPONIBLES = [
        self::ZONE_SIMTIS => 'SIMTIS',
        self::ZONE_SIMTIS_TISSAGE => 'SIMTIS TISSAGE'
    ];

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


    #[ORM\Column(length: 50, nullable: true)] // Changé de nullable: false à nullable: true
    #[Assert\Choice(
        choices: ['SIMTIS', 'SIMTIS TISSAGE'],
        message: 'Veuillez choisir une zone valide (SIMTIS ou SIMTIS TISSAGE)',
        groups: ['zone_required'] // Ajouter un groupe de validation
    )]
    private ?string $zone = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: RapportHSE::class, orphanRemoval: true)]
    private Collection $rapportsHSE;

    #[ORM\OneToMany(mappedBy: 'validePar', targetEntity: Extincteur::class)]
    private Collection $extincteursValides;

    #[ORM\OneToMany(mappedBy: 'validePar', targetEntity: RIA::class)]
    private Collection $riasValides;

    #[ORM\OneToMany(mappedBy: 'inspectePar', targetEntity: InspectionExtincteur::class)]
    private Collection $inspectionsExtincteurs;

    #[ORM\OneToMany(mappedBy: 'inspecteur', targetEntity: InspectionMonteCharge::class)]
    private Collection $inspectionsMonteCharge;

    public function __construct()
    {
        $this->rapportsHSE = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->heureCreation = new \DateTime();
        $this->roles = ['ROLE_USER'];
        $this->extincteursValides = new ArrayCollection();
        $this->riasValides = new ArrayCollection();
        $this->inspectionsExtincteurs = new ArrayCollection();
        $this->inspectionsMonteCharge = new ArrayCollection();
    }

    /**
     * Vérifie si l'utilisateur a besoin d'une zone
     */
    public function needsZone(): bool
    {
        // Les super admins n'ont pas besoin de zone
        return !$this->isSuperAdmin();
    }

    /**
     * Obtient la zone d'affichage
     */
    public function getDisplayZone(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Toutes les zones';
        }

        return $this->zone ?? 'Non définie';
    }

    /**
     * Obtient les zones que cet utilisateur peut gérer
     */
    public function getManagedZones(): array
    {
        if ($this->isSuperAdmin()) {
            return self::ZONES_DISPONIBLES;
        }

        if ($this->isAdmin() && $this->zone) {
            return [$this->zone => $this->zone];
        }

        return [];
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

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        // Validation seulement si une zone est fournie
        if ($zone !== null && !in_array($zone, ['SIMTIS', 'SIMTIS TISSAGE'])) {
            throw new \InvalidArgumentException('Zone invalide. Les zones autorisées sont : SIMTIS, SIMTIS TISSAGE');
        }

        $this->zone = $zone;
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

    /**
     * Retourne le badge HTML pour afficher la zone
     */
    public function getZoneBadge(): string
    {
        $class = $this->zone === 'SIMTIS' ? 'bg-info' : 'bg-success';
        return sprintf('<span class="badge %s">%s</span>', $class, $this->zone);
    }

    /**
     * Vérifie si l'utilisateur est un super admin
     */
    public function isSuperAdmin(): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $this->getRoles());
    }

    /**
     * Vérifie si l'utilisateur est un admin (mais pas super admin)
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles()) && !$this->isSuperAdmin();
    }

    /**
     * Vérifie si l'utilisateur peut gérer un autre utilisateur
     */
    public function canManageUser(User $targetUser): bool
    {
        // Super admin peut gérer tout le monde
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin peut gérer les utilisateurs de sa zone
        if ($this->isAdmin()) {
            return $this->getZone() === $targetUser->getZone();
        }

        // Utilisateur normal ne peut rien gérer
        return false;
    }

    /**
     * @return Collection<int, Extincteur>
     */
    public function getExtincteursValides(): Collection
    {
        return $this->extincteursValides;
    }

    public function addExtincteurValide(Extincteur $extincteur): static
    {
        if (!$this->extincteursValides->contains($extincteur)) {
            $this->extincteursValides->add($extincteur);
            $extincteur->setValidePar($this);
        }
        return $this;
    }

    public function removeExtincteurValide(Extincteur $extincteur): static
    {
        if ($this->extincteursValides->removeElement($extincteur)) {
            if ($extincteur->getValidePar() === $this) {
                $extincteur->setValidePar(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, RIA>
     */
    public function getRiasValides(): Collection
    {
        return $this->riasValides;
    }

    public function addRiaValide(RIA $ria): static
    {
        if (!$this->riasValides->contains($ria)) {
            $this->riasValides->add($ria);
            $ria->setValidePar($this);
        }
        return $this;
    }

    public function removeRiaValide(RIA $ria): static
    {
        if ($this->riasValides->removeElement($ria)) {
            if ($ria->getValidePar() === $this) {
                $ria->setValidePar(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, InspectionExtincteur>
     */
    public function getInspectionsExtincteurs(): Collection
    {
        return $this->inspectionsExtincteurs;
    }

    public function addInspectionExtincteur(InspectionExtincteur $inspection): static
    {
        if (!$this->inspectionsExtincteurs->contains($inspection)) {
            $this->inspectionsExtincteurs->add($inspection);
            $inspection->setInspectePar($this);
        }
        return $this;
    }

    public function removeInspectionExtincteur(InspectionExtincteur $inspection): static
    {
        if ($this->inspectionsExtincteurs->removeElement($inspection)) {
            if ($inspection->getInspectePar() === $this) {
                $inspection->setInspectePar(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, InspectionMonteCharge>
     */
    public function getInspectionsMonteCharge(): Collection
    {
        return $this->inspectionsMonteCharge;
    }

    public function addInspectionMonteCharge(InspectionMonteCharge $inspection): static
    {
        if (!$this->inspectionsMonteCharge->contains($inspection)) {
            $this->inspectionsMonteCharge->add($inspection);
            $inspection->setInspectePar($this);
        }
        return $this;
    }

    public function removeInspectionMonteCharge(InspectionMonteCharge $inspection): static
    {
        if ($this->inspectionsMonteCharge->removeElement($inspection)) {
            if ($inspection->getInspectePar() === $this) {
                $inspection->setInspectePar(null);
            }
        }
        return $this;
    }

    /**
     * Obtenir le nombre total d'inspections effectuées par l'utilisateur
     */
    public function getTotalInspections(): int
    {
        return $this->inspectionsExtincteurs->count() + $this->inspectionsMonteCharge->count();
    }

    /**
     * Obtenir le nombre total d'équipements validés par l'utilisateur
     */
    public function getTotalValidations(): int
    {
        return $this->extincteursValides->count() + $this->riasValides->count();
    }

    /**
     * Vérifier si l'utilisateur peut gérer les équipements
     */
    public function canManageEquipements(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getRoles());
    }
}
