<?php

namespace App\Entity;

use App\Repository\DesenfumageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DesenfumageRepository::class)]
#[ORM\Table(name: 'desenfumage')]
class Desenfumage
{
    // Zones et emplacements en suggestions pour Super Admin
    public const ZONES_DESENFUMAGE_SUGGESTIONS = [
        'STOCK PF' => 'STOCK PF',
        'IMPRESSION NUMERIQUE' => 'IMPRESSION NUMERIQUE',
    ];

    public const EMPLACEMENTS_DESENFUMAGE_SUGGESTIONS = [
        'LAVAGE A LA CONTINUE' => 'LAVAGE A LA CONTINUE',
        'Entre Vaporisateur 1 & 2' => 'Entre Vaporisateur 1 & 2',
        'ROTATIVE' => 'ROTATIVE',
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
    private ?string $type = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etatCommande = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $etatSkydome = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'desenfumage', targetEntity: InspectionDesenfumage::class, orphanRemoval: true)]
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

    public function getEtatCommande(): ?string
    {
        return $this->etatCommande;
    }

    public function setEtatCommande(?string $etatCommande): static
    {
        $this->etatCommande = $etatCommande;
        return $this;
    }

    public function getEtatSkydome(): ?string
    {
        return $this->etatSkydome;
    }

    public function setEtatSkydome(?string $etatSkydome): static
    {
        $this->etatSkydome = $etatSkydome;
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
     * @return Collection<int, InspectionDesenfumage>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionDesenfumage $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setDesenfumage($this);
        }
        return $this;
    }

    public function removeInspection(InspectionDesenfumage $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getDesenfumage() === $this) {
                $inspection->setDesenfumage(null);
            }
        }
        return $this;
    }

    public function getDerniereInspection(): ?InspectionDesenfumage
    {
        $inspections = $this->inspections->toArray();
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

