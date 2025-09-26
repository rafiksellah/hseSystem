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
        'Monte charge 01' => 'Monte charge 01',
        'Monte charge 02' => 'Monte charge 02'
    ];

    public const ZONES = [
        'RDC TISSAGE-RENTRAGE-OURDISSOIR' => 'RDC TISSAGE-RENTRAGE-OURDISSOIR',
        'RDC TISSAGE-MEZZANINE-PRATO' => 'RDC TISSAGE-MEZZANINE-PRATO'
    ];

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
