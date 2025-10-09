<?php

namespace App\Entity;

use App\Repository\ExtinctionLocaliseeRAMRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExtinctionLocaliseeRAMRepository::class)]
#[ORM\Table(name: 'extinction_localisee_ram')]
class ExtinctionLocaliseeRAM
{
<<<<<<< HEAD
    public const ZONES = [
        'RAM' => 'RAM',
    ];

=======
    // Zones et emplacements en suggestions pour Super Admin
    public const ZONES_RAM_SUGGESTIONS = [
        'RAM' => 'RAM',
    ];

    public const EMPLACEMENTS_RAM_SUGGESTIONS = [
        'RAM 1' => 'RAM 1',
        'RAM 2' => 'RAM 2',
        'RAM 3' => 'RAM 3',
        'RAM 4' => 'RAM 4',
        'RAM 5' => 'RAM 5',
        'RAM 6' => 'RAM 6',
        'RAM 7' => 'RAM 7',
        'RAM 8' => 'RAM 8',
    ];

>>>>>>> 0ae0fcd2966c39ffb2310a5f9f5295022dc200be
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

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $vanne = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'extinctionLocaliseeRAM', targetEntity: InspectionExtinctionRAM::class, orphanRemoval: true)]
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

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        $this->emplacement = $emplacement;
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

    public function getVanne(): ?string
    {
        return $this->vanne;
    }

    public function setVanne(?string $vanne): static
    {
        $this->vanne = $vanne;
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
     * @return Collection<int, InspectionExtinctionRAM>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionExtinctionRAM $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setExtinctionLocaliseeRAM($this);
        }
        return $this;
    }

    public function removeInspection(InspectionExtinctionRAM $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getExtinctionLocaliseeRAM() === $this) {
                $inspection->setExtinctionLocaliseeRAM(null);
            }
        }
        return $this;
    }

    public function getDerniereInspection(): ?InspectionExtinctionRAM
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
        
        return $derniereInspection->isValide() ? 'Conforme' : 'Non conforme';
    }

    public function isConforme(): ?bool
    {
        $derniereInspection = $this->getDerniereInspection();
        
        if (!$derniereInspection) {
            return null;
        }
        
        return $derniereInspection->isValide();
    }

    public function getNombreInspections(): int
    {
        return $this->inspections->count();
    }
}

