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
<<<<<<< HEAD
    public const ZONES = [
=======
    // Zones et emplacements en suggestions pour Super Admin
    public const ZONES_SIRENE_SUGGESTIONS = [
>>>>>>> 0ae0fcd2966c39ffb2310a5f9f5295022dc200be
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

<<<<<<< HEAD
=======
    public const EMPLACEMENTS_SIRENE_SUGGESTIONS = [
        'En face montecharge N°2' => 'En face montecharge N°2',
        'Montecharge N°2' => 'Montecharge N°2',
        'Au milieu' => 'Au milieu',
        'CANTINE FEMME' => 'CANTINE FEMME',
        'Au fond entre issue de secours et monte charge auxiliaire' => 'Au fond entre issue de secours et monte charge auxiliaire',
        'Au dessus de l\'issue de secours BED2' => 'Au dessus de l\'issue de secours BED2',
        'Issue de secours T4' => 'Issue de secours T4',
        'Issue de secours T5' => 'Issue de secours T5',
        'A coté de l\'issue de secours T6' => 'A coté de l\'issue de secours T6',
        'Atelier mécanique' => 'Atelier mécanique',
        'Montecharge N°5' => 'Montecharge N°5',
        'En face chaine Serviette' => 'En face chaine Serviette',
        'En face de table de lancement' => 'En face de table de lancement',
        'Issue de secours ST.D 02' => 'Issue de secours ST.D 02',
        'Au dessus de table de coupe' => 'Au dessus de table de coupe',
        'Porte Montecharge N°5' => 'Porte Montecharge N°5',
        'Porte Montecharge N°6' => 'Porte Montecharge N°6',
        'ISSUE DE SECOURS ( cote escalier)' => 'ISSUE DE SECOURS ( cote escalier)',
        'Entre les machine sde rasage 1 et 2' => 'Entre les machine sde rasage 1 et 2',
        'Au milieu de mur de séparation avec DIMANTINE' => 'Au milieu de mur de séparation avec DIMANTINE',
        'A coté W.C' => 'A coté W.C',
        'SORTIE RAM SUR DALLE' => 'SORTIE RAM SUR DALLE',
        'SORTIE RAM' => 'SORTIE RAM',
        'Au dessus machine biancalani' => 'Au dessus machine biancalani',
        'RAM 3' => 'RAM 3',
        'RAM 2 SUR DALLE' => 'RAM 2 SUR DALLE',
        'A l\'entrée cuisine rotative' => 'A l\'entrée cuisine rotative',
        'AU DESSUS DES ADOUCISSEURS' => 'AU DESSUS DES ADOUCISSEURS',
        'DMS1' => 'DMS1',
        'MACHINES TG' => 'MACHINES TG',
        'MACHINE SCV' => 'MACHINE SCV',
        'CUISINE COLORANT' => 'CUISINE COLORANT',
        'A COTE LABO' => 'A COTE LABO',
        'CANTINE HOMME' => 'CANTINE HOMME',
        'Monte charge N°5' => 'Monte charge N°5',
        'En face porte sectionneur N°1' => 'En face porte sectionneur N°1',
        'Au milieu du stock' => 'Au milieu du stock',
        'A coté Magasin PDR' => 'A coté Magasin PDR',
        'A coté Monte charge auxiliaire' => 'A coté Monte charge auxiliaire',
        'MACHINE TEINTURE DE SOIE' => 'MACHINE TEINTURE DE SOIE',
    ];

    public const TYPES_SIRENE_SUGGESTIONS = [
        'Sirène' => 'Sirène',
        'Diffuseur sonore' => 'Diffuseur sonore',
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

