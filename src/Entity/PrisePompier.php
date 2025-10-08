<?php

namespace App\Entity;

use App\Repository\PrisePompierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PrisePompierRepository::class)]
#[ORM\Table(name: 'prise_pompier')]
class PrisePompier
{
    // Zones, emplacements et diamètres en suggestions pour Super Admin
    public const ZONES_PRISES_SUGGESTIONS = [
        'RAMS-DEGRAISSAGE-DETORTIONNEUSES' => 'RAMS-DEGRAISSAGE-DETORTIONNEUSES',
        'ROULAGE-CALANDRE-ROTATIVE-RAMS SUR DALLES-LAVAGE' => 'ROULAGE-CALANDRE-ROTATIVE-RAMS SUR DALLES-LAVAGE',
        'LIVRAISON' => 'LIVRAISON',
        'CHAUFFERIE/STOCKAGE FIOUL' => 'CHAUFFERIE/STOCKAGE FIOUL',
        'GRATTAGE' => 'GRATTAGE',
        '2EME ETAGE EMBALLAGE' => '2EME ETAGE EMBALLAGE',
        'BRODERIE' => 'BRODERIE',
        'PREPARATION' => 'PREPARATION',
        'RDC STOCK DECATHLON' => 'RDC STOCK DECATHLON',
    ];

    public const EMPLACEMENTS_PRISES_SUGGESTIONS = [
        'PORTE TRANSFERT ENTRE RAM ET SIMI 6 COTE RAM' => 'PORTE TRANSFERT ENTRE RAM ET SIMI 6 COTE RAM',
        'BUREAUX MAINTENANCE' => 'BUREAUX MAINTENANCE',
        'RAM 2 - ENTREE SOUS DALE' => 'RAM 2 - ENTREE SOUS DALE',
        'EN FACE IMPRESSION ROTATIVE' => 'EN FACE IMPRESSION ROTATIVE',
        'EN FACE MONTECHARGE N°1' => 'EN FACE MONTECHARGE N°1',
        'BUREAU LIVRAISON' => 'BUREAU LIVRAISON',
        'A COTE ZONE FIOUL' => 'A COTE ZONE FIOUL',
        'ENTREE GRATTAGE' => 'ENTREE GRATTAGE',
        'ENTREE EMBALLAGE' => 'ENTREE EMBALLAGE',
        'ENTREE BRODERIE' => 'ENTREE BRODERIE',
        'ENTREE PREPARATION' => 'ENTREE PREPARATION',
        'PORTE DE CHARGEMENT' => 'PORTE DE CHARGEMENT',
    ];

    public const DIAMETRES_SUGGESTIONS = [
        '2*45 MM' => '2*45 MM',
        '70 MM' => '70 MM',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La zone ne peut pas être vide')]
    private ?string $zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $dimatere = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'prisePompier', targetEntity: InspectionPrisePompier::class, orphanRemoval: true)]
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

    public function getDimatere(): ?string
    {
        return $this->dimatere;
    }

    public function setDimatere(?string $dimatere): static
    {
        $this->dimatere = $dimatere;
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
     * @return Collection<int, InspectionPrisePompier>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionPrisePompier $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setPrisePompier($this);
        }
        return $this;
    }

    public function removeInspection(InspectionPrisePompier $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getPrisePompier() === $this) {
                $inspection->setPrisePompier(null);
            }
        }
        return $this;
    }

    /**
     * Retourne la dernière inspection
     */
    public function getDerniereInspection(): ?InspectionPrisePompier
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
     * Vérifie si la prise est conforme selon la dernière inspection
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

    /**
     * Retourne un identifiant unique basé sur zone + emplacement
     */
    public function getIdentifiant(): string
    {
        return $this->zone . ' - ' . ($this->emplacement ?? 'Sans emplacement');
    }
}

