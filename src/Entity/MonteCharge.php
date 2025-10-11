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

    public const TYPES = [
        'Monte-charge' => 'Monte-charge',
        'Ascenseur' => 'Ascenseur',
        'Élévateur' => 'Élévateur',
    ];

    public const NUMEROS_PORTE = [
        'PORTE 01' => 'PORTE 01',
        'PORTE 02' => 'PORTE 02',
        'PORTE 03' => 'PORTE 03',
        'PORTE 04' => 'PORTE 04',
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

    #[ORM\Column(length: 100, nullable: false)]
    #[Assert\NotBlank(message: 'L\'emplacement ne peut pas être vide')]
    private string $emplacement = '';

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank(message: 'Vous devez sélectionner au moins une porte')]
    #[Assert\Count(min: 1, minMessage: 'Vous devez sélectionner au moins une porte')]
    private array $numeroPorte = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'monteCharge', targetEntity: InspectionMonteCharge::class, orphanRemoval: true)]
    private Collection $inspections;

    public function __construct()
    {
        $this->inspections = new ArrayCollection();
        $this->dateCreation = new \DateTime();
        $this->emplacement = '';
        $this->numeroPorte = [];
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

    public function getEmplacement(): string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        // Protection contre les valeurs null
        $this->emplacement = $emplacement ?? '';
        return $this;
    }

    public function getNumeroPorte(): array
    {
        // Sécurité : s'assurer que c'est toujours un tableau
        if (!is_array($this->numeroPorte)) {
            return [];
        }
        // Si c'est un double tableau, on le corrige
        if (!empty($this->numeroPorte) && is_array($this->numeroPorte[0])) {
            return $this->numeroPorte[0];
        }
        return $this->numeroPorte;
    }

    public function setNumeroPorte(array $numeroPorte): static
    {
        $this->numeroPorte = $numeroPorte;
        return $this;
    }

    /**
     * Retourne les portes sous forme de chaîne de caractères
     */
    public function getNumeroPorteAsString(): string
    {
        $portes = $this->getNumeroPorte();
        return implode(', ', $portes);
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

    public function __toString(): string
    {
        return $this->numeroMonteCharge ?? '';
    }
}