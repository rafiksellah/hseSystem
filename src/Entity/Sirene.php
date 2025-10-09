<?php

namespace App\Entity;

use App\Repository\SireneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SireneRepository::class)]
#[ORM\Table(name: 'sirene')]
class Sirene
{
    public const ZONES = [
        '1ER ETAGE STARSS' => '1ER ETAGE STARSS',
        '2EME ETAGE EMBALLAGE' => '2EME ETAGE EMBALLAGE',
        '3EME ETAGE CHALES ET FOULARDS' => '3EME ETAGE CHALES ET FOULARDS',
        'ADMINISTRATION' => 'ADMINISTRATION',
        'BRODERIE' => 'BRODERIE',
        'BUREAU DES ETUDES' => 'BUREAU DES ETUDES',
        'CALANDRE' => 'CALANDRE',
        'CHAUFFERIE' => 'CHAUFFERIE',
        'CONFECTION DECATHLON' => 'CONFECTION DECATHLON',
        'DIMANTINE' => 'DIMANTINE',
        'ENTREE ADMINISTRATION' => 'ENTREE ADMINISTRATION',
        'GRATTAGE' => 'GRATTAGE',
        'IMPRESSION NUMERIQUE' => 'IMPRESSION NUMERIQUE',
        'LIVRAISON' => 'LIVRAISON',
        'PREPARATION' => 'PREPARATION',
        'RAM' => 'RAM',
        'ROTATIVE' => 'ROTATIVE',
        'ROULAGE' => 'ROULAGE',
        'SIMI' => 'SIMI',
        'STOCK DECATHLON' => 'STOCK DECATHLON',
        'STOCK PF' => 'STOCK PF',
        'TEINTURE' => 'TEINTURE',
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
    #[Assert\Choice(choices: self::ZONES, message: 'Zone invalide')]
    private ?string $zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'sirene', targetEntity: InspectionSirene::class, orphanRemoval: true)]
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
     * @return Collection<int, InspectionSirene>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionSirene $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setSirene($this);
        }
        return $this;
    }

    public function removeInspection(InspectionSirene $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getSirene() === $this) {
                $inspection->setSirene(null);
            }
        }
        return $this;
    }

    public function getDerniereInspection(): ?InspectionSirene
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

