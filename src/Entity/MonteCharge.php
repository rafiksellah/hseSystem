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
    public const TYPES = [
        'CHARGE01' => 'CHARGE01',
        'CHARGE02' => 'CHARGE02'
    ];

    public const ZONES = [
        'RDC TISSAGE-RENTRAGE-OURDISSOIR' => 'RDC TISSAGE-RENTRAGE-OURDISSOIR',
        'RDC TISSAGE-MEZZANINE-PRATO' => 'RDC TISSAGE-MEZZANINE-PRATO'
    ];

    /**
     * Retourne les portes disponibles pour un monte-charge donné
     */
    public static function getPortesForType(string $type): array
    {
        return match ($type) {
            'CHARGE01' => [
                'PORTE 1' => 'PORTE 1',
                'PORTE 2' => 'PORTE 2',
                'PORTE 3' => 'PORTE 3',
            ],
            'CHARGE02' => [
                'PORTE 4' => 'PORTE 4',
                'PORTE 5' => 'PORTE 5',
                'PORTE 6' => 'PORTE 6',
            ],
            default => []
        };
    }

    /**
     * Retourne la zone pour un type de monte-charge
     */
    public static function getZoneForType(string $type): string
    {
        return match ($type) {
            'CHARGE01' => 'RDC TISSAGE-RENTRAGE-OURDISSOIR',
            'CHARGE02' => 'RDC TISSAGE-MEZZANINE-PRATO',
            default => ''
        };
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $type = null;

    #[ORM\Column(length: 100)]
    private ?string $zone = null;

    #[ORM\OneToMany(mappedBy: 'monteCharge', targetEntity: InspectionMonteCharge::class, orphanRemoval: true)]
    private Collection $inspections;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->inspections = new ArrayCollection();
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
}
