<?php

namespace App\Entity;

use App\Repository\ExtincteurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExtincteurRepository::class)]
class Extincteur
{
    public const NUMEROTATIONS_DISPONIBLES = [
        '4' => '4',
        '5' => '5',
        '7' => '7',
        '8' => '8',
        '9' => '9',
        '3' => '3',
        '1' => '1',
        '2' => '2',
        '6' => '6',
        '133' => '133',
        '143' => '143',
        '144' => '144',
        '58C' => '58C',
        '132' => '132',
        '114C' => '114C',
        '97B' => '97B',
        '114B' => '114B',
    ];

    public const EMPLACEMENTS_DISPONIBLES = [
        'ACCUEIL' => 'ACCUEIL',
        'BUREAUX RH' => 'BUREAUX RH',
        'ENTREE ADMINISTRATION' => 'ENTREE ADMINISTRATION',
        'POSTE HSE' => 'POSTE HSE',
        'ENTREE ADMINISTRATION (usine)' => 'ENTREE ADMINISTRATION (usine)',
        'CAFETERIE' => 'CAFETERIE',
        'CANTINE BRODERIE' => 'CANTINE BRODERIE',
    ];

    public const AGENTS_EXTINCTEUR = [
        'CO2' => 'CO2',
        'Poudre ABC' => 'Poudre ABC',
        'EAU PULVIRISEE AVEC ADDITIF' => 'EAU PULVIRISEE AVEC ADDITIF',
    ];

    public const TYPES_DISPONIBLES = [
        'Portatif P. permanente' => 'Portatif P. permanente',
        'Portatif auxiliaire' => 'Portatif auxiliaire',
    ];

    public const CAPACITES_DISPONIBLES = [
        '2KG' => '2KG',
        '5KG' => '5KG',
        '9 kg' => '9 kg',
        '6L' => '6L',
        '9L' => '9L',
    ];


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro ne peut pas être vide')]
    private ?string $numerotation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La zone ne peut pas être vide')]
    private ?string $zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $agentExtincteur = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $capacite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFabrication = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEpreuve = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFinDeVie = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroSerie = null;

    #[ORM\Column]
    private bool $valide = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(inversedBy: 'extincteursValides')]
    private ?User $validePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'extincteur', targetEntity: InspectionExtincteur::class, orphanRemoval: true)]
    private Collection $inspections;

    public function __construct()
    {
        $this->inspections = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }
    public static function getZonesForUser(User $user): array
    {
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return array_merge(RapportHSE::ZONES_SIMTIS, RapportHSE::ZONES_SIMTIS_TISSAGE);
        }

        return RapportHSE::getZonesForUserZone($user->getZone());
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumerotation(): ?string
    {
        return $this->numerotation;
    }

    public function setNumerotation(string $numerotation): static
    {
        $this->numerotation = $numerotation;
        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(string $zone): static
    {
        $this->zone = $zone;
        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function getAgentExtincteur(): ?string
    {
        return $this->agentExtincteur;
    }

    public function setAgentExtincteur(?string $agentExtincteur): static
    {
        $this->agentExtincteur = $agentExtincteur;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCapacite(): ?string
    {
        return $this->capacite;
    }

    public function setCapacite(?string $capacite): static
    {
        $this->capacite = $capacite;
        return $this;
    }

    public function getDateFabrication(): ?\DateTimeInterface
    {
        return $this->dateFabrication;
    }

    public function setDateFabrication(?\DateTimeInterface $dateFabrication): static
    {
        $this->dateFabrication = $dateFabrication;
        return $this;
    }

    public function getDateEpreuve(): ?\DateTimeInterface
    {
        return $this->dateEpreuve;
    }

    public function setDateEpreuve(?\DateTimeInterface $dateEpreuve): static
    {
        $this->dateEpreuve = $dateEpreuve;
        return $this;
    }

    public function getDateFinDeVie(): ?\DateTimeInterface
    {
        return $this->dateFinDeVie;
    }

    public function setDateFinDeVie(?\DateTimeInterface $dateFinDeVie): static
    {
        $this->dateFinDeVie = $dateFinDeVie;
        return $this;
    }

    public function getNumeroSerie(): ?string
    {
        return $this->numeroSerie;
    }

    public function setNumeroSerie(?string $numeroSerie): static
    {
        $this->numeroSerie = $numeroSerie;
        return $this;
    }

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;
        return $this;
    }

    public function getDateValidation(): ?\DateTimeInterface
    {
        return $this->dateValidation;
    }

    public function setDateValidation(?\DateTimeInterface $dateValidation): static
    {
        $this->dateValidation = $dateValidation;
        return $this;
    }

    public function getValidePar(): ?User
    {
        return $this->validePar;
    }

    public function setValidePar(?User $validePar): static
    {
        $this->validePar = $validePar;
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

    /**
     * @return Collection<int, InspectionExtincteur>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionExtincteur $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setExtincteur($this);
        }
        return $this;
    }

    public function removeInspection(InspectionExtincteur $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getExtincteur() === $this) {
                $inspection->setExtincteur(null);
            }
        }
        return $this;
    }
}
