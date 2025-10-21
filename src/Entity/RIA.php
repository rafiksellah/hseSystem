<?php

namespace App\Entity;

use App\Repository\RIARepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RIARepository::class)]
#[ORM\Table(name: 'ria')]
class RIA
{
    public const ZONES = [
        '1ER ETAGE STARSS' => '1ER ETAGE STARSS',
        '2EME ETAGE EMBALLAGE' => '2EME ETAGE EMBALLAGE',
        '3EME ETAGE CHALES ET FOULARDS' => '3EME ETAGE CHALES ET FOULARDS',
        'BRODERIE' => 'BRODERIE',
        'BUREAUX D\'ETUDES' => 'BUREAUX D\'ETUDES',
    ];

    /**
     * Obtient toutes les zones disponibles (statiques + dynamiques de la BDD)
     */
    public static function getAllZones(): array
    {
        return self::ZONES;
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro ne peut pas être vide')]
    private ?string $numerotation = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La zone ne peut pas être vide')]
    #[Assert\Choice(choices: self::ZONES, message: 'Zone invalide')]
    private ?string $zone = null;


    #[ORM\Column(length: 50, nullable: true)]
    private ?string $agentExtincteur = null;

    #[ORM\Column(nullable: true)]
    private ?int $dimatere = null;

    #[ORM\Column(nullable: true)]
    private ?int $longueur = null;

    #[ORM\Column]
    private bool $valide = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateValidation = null;

    #[ORM\ManyToOne(inversedBy: 'riasValides')]
    private ?User $validePar = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'ria', targetEntity: InspectionRIA::class, orphanRemoval: true)]
    private Collection $inspections;

    public function __construct()
    {
        $this->inspections = new ArrayCollection();
        $this->dateCreation = new \DateTime();
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


    public function getAgentExtincteur(): ?string
    {
        return $this->agentExtincteur;
    }

    public function setAgentExtincteur(?string $agentExtincteur): static
    {
        $this->agentExtincteur = $agentExtincteur;
        return $this;
    }

    public function getDimatere(): ?int
    {
        return $this->dimatere;
    }

    public function setDimatere(?int $dimatere): static
    {
        $this->dimatere = $dimatere;
        return $this;
    }

    public function getLongueur(): ?int
    {
        return $this->longueur;
    }

    public function setLongueur(?int $longueur): static
    {
        $this->longueur = $longueur;
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
     * @return Collection<int, InspectionRIA>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionRIA $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setRia($this);
        }
        return $this;
    }

    public function removeInspection(InspectionRIA $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getRia() === $this) {
                $inspection->setRia(null);
            }
        }
        return $this;
    }

    /**
     * Retourne la dernière inspection
     */
    public function getDerniereInspection(): ?InspectionRIA
    {
        $inspections = $this->inspections->toArray();
        usort($inspections, fn($a, $b) => $b->getDateInspection() <=> $a->getDateInspection());
        return $inspections[0] ?? null;
    }

    /**
     * Retourne le statut de conformité basé sur la dernière inspection
     */
    public function getStatutConformite(): string
    {
        $derniereInspection = $this->getDerniereInspection();
        
        if (!$derniereInspection) {
            return 'Non inspecté';
        }
        
        return $derniereInspection->isValide() ? 'Conforme' : 'Non conforme';
    }

    /**
     * Vérifie si le RIA est conforme selon la dernière inspection
     */
    public function isConforme(): ?bool
    {
        $derniereInspection = $this->getDerniereInspection();
        
        if (!$derniereInspection) {
            return null;
        }
        
        return $derniereInspection->isValide();
    }

    /**
     * Retourne le nombre total d'inspections
     */
    public function getNombreInspections(): int
    {
        return $this->inspections->count();
    }
}
