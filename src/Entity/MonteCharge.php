<?php

namespace App\Entity;

use App\Repository\MonteChargeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MonteChargeRepository::class)]
#[ORM\Table(name: 'monte_charge')]
class MonteCharge
{
    // Les numéros de monte-charge ne sont plus en liste déroulante
    // Ils sont saisis librement (sauf pour les admins normaux)

    public const ZONES = [
        'SIMTIS' => 'SIMTIS',
        'SIMTIS TISSAGE' => 'SIMTIS TISSAGE',
    ];

    public const EMPLACEMENTS_TISSAGE = [
        'RDC TISSAGE-RENTRAGE-OURDISSOIR' => 'RDC TISSAGE-RENTRAGE-OURDISSOIR',
        'RDC TISSAGE-MEZZANINE-PRATO' => 'RDC TISSAGE-MEZZANINE-PRATO',
    ];

    public const EMPLACEMENTS_SIMTIS = [
        'Bâtiment confection' => 'Bâtiment confection',
        'Teinture –préparation-broderie' => 'Teinture –préparation-broderie',
        '2eme étage emballage' => '2eme étage emballage',
        'Zone décathlon' => 'Zone décathlon',
        'Broderie-la stock PF' => 'Broderie-la stock PF',
        'Préparation-diamantine-broderie' => 'Préparation-diamantine-broderie',
    ];

    /**
     * Obtient les emplacements disponibles selon la zone
     */
    public static function getEmplacementsByZone(string $zone): array
    {
        return match ($zone) {
            'SIMTIS' => self::EMPLACEMENTS_SIMTIS,
            'SIMTIS TISSAGE' => self::EMPLACEMENTS_TISSAGE,
            default => []
        };
    }

    // Les numéros de porte ne sont plus en liste déroulante
    // Le nombre de portes sera défini lors de la création

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Le numéro de monte-charge ne peut pas être vide')]
    private ?string $numeroMonteCharge = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'La zone ne peut pas être vide')]
    #[Assert\Choice(choices: self::ZONES, message: 'Zone invalide')]
    private ?string $zone = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'L\'emplacement ne peut pas être vide')]
    private ?string $emplacement = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le numéro de porte ne peut pas être vide')]
    private ?string $numeroPorte = null;

    #[ORM\Column(nullable: true)]
    private ?int $nombrePortes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'monteCharge', targetEntity: InspectionMonteCharge::class, orphanRemoval: true)]
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

    public function getNumeroMonteCharge(): ?string
    {
        return $this->numeroMonteCharge;
    }

    public function setNumeroMonteCharge(string $numeroMonteCharge): static
    {
        $this->numeroMonteCharge = $numeroMonteCharge;
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

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function getNumeroPorte(): ?string
    {
        return $this->numeroPorte;
    }

    public function setNumeroPorte(string $numeroPorte): static
    {
        $this->numeroPorte = $numeroPorte;
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
     * @return Collection<int, InspectionMonteCharge>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionMonteCharge $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setMonteCharge($this);
        }
        return $this;
    }

    public function removeInspection(InspectionMonteCharge $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getMonteCharge() === $this) {
                $inspection->setMonteCharge(null);
            }
        }
        return $this;
    }

    public function getDerniereInspection(): ?InspectionMonteCharge
    {
        // Filtrer uniquement les inspections actives
        $inspections = $this->inspections->filter(fn($inspection) => $inspection->isActive())->toArray();
        usort($inspections, fn($a, $b) => $b->getDateInspection() <=> $a->getDateInspection());
        return $inspections[0] ?? null;
    }

    public function getStatutConformite(): string
    {
        $derniereInspection = $this->getDerniereInspection();
        
        if (!$derniereInspection) {
            return 'Non inspecté';
        }
        
        return $derniereInspection->isConforme() ? 'Conforme' : 'Non conforme';
    }

    public function isConforme(): ?bool
    {
        $derniereInspection = $this->getDerniereInspection();
        
        if (!$derniereInspection) {
            return null;
        }
        
        return $derniereInspection->isConforme();
    }

    public function getNombreInspections(): int
    {
        return $this->inspections->count();
    }

    public function getNombrePortes(): ?int
    {
        return $this->nombrePortes;
    }

    public function setNombrePortes(?int $nombrePortes): static
    {
        $this->nombrePortes = $nombrePortes;
        return $this;
    }

    public function __toString(): string
    {
        return $this->numeroMonteCharge ?? '';
    }
}