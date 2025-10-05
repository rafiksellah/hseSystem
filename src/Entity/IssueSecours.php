<?php

namespace App\Entity;

use App\Repository\IssueSecoursRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IssueSecoursRepository::class)]
#[ORM\Table(name: 'issue_secours')]
class IssueSecours
{
    public const ZONES_ISSUES = [
        'GRATTAGE' => 'GRATTAGE',
        'CONFECTION DECATHLON' => 'CONFECTION DECATHLON',
        'STOCK DECATHLON' => 'STOCK DECATHLON',
        'BRODERIE' => 'BRODERIE',
        'PREPARATION' => 'PREPARATION',
        'BUREAU D\'ETUDES' => 'BUREAU D\'ETUDES',
        'NUMERIQUE' => 'NUMERIQUE',
        'TEINTURE' => 'TEINTURE',
        'SIMI' => 'SIMI',
        'SIMI - CANTINE' => 'SIMI - CANTINE',
        'RAM' => 'RAM',
        '1ER ETAGE STARSS' => '1ER ETAGE STARSS',
        '2EME ETAGE EMBALLAGE' => '2EME ETAGE EMBALLAGE',
        '3EME ETAGE CHALES ET FOULARDS' => '3EME ETAGE CHALES ET FOULARDS',
        'STOCK PF' => 'STOCK PF',
        'BRODERIE - VESTIAIRE FEMME' => 'BRODERIE - VESTIAIRE FEMME',
        'DIAMANTINE' => 'DIAMANTINE',
        'ADMINISTRATION' => 'ADMINISTRATION',
    ];

    public const NUMEROTATIONS_ISSUES = [
        'G01' => 'G01',
        'G02' => 'G02',
        'CF.D 01' => 'CF.D 01',
        'CF.D 02' => 'CF.D 02',
        'ST.D 02' => 'ST.D 02',
        'ST.D 03' => 'ST.D 03',
        'BR 01' => 'BR 01',
        'BR 02' => 'BR 02',
        'PR02' => 'PR02',
        'BE 01' => 'BE 01',
        'BE 02' => 'BE 02',
        'N01' => 'N01',
        'N02' => 'N02',
        'N03' => 'N03',
        'N04' => 'N04',
        'T01' => 'T01',
        'T02' => 'T02',
        'T03' => 'T03',
        'T04' => 'T04',
        'T05' => 'T05',
        'T06' => 'T06',
        'S01' => 'S01',
        'S02' => 'S02',
        'RAM 01' => 'RAM 01',
        'RAM 02' => 'RAM 02',
        'ST.D 01' => 'ST.D 01',
        'ST.D 04' => 'ST.D 04',
        'ST.PF 01' => 'ST.PF 01',
        'ST.PF 02' => 'ST.PF 02',
        'EMB 01' => 'EMB 01',
        'EMB 02' => 'EMB 02',
        'CF 01' => 'CF 01',
        'CF 02' => 'CF 02',
        'CP 01' => 'CP 01',
        'BR 03' => 'BR 03',
        'PR01' => 'PR01',
        'DIAM 01' => 'DIAM 01',
        'ADM 01' => 'ADM 01',
        'ADM 02' => 'ADM 02',
    ];

    public const TYPES_ISSUES = [
        'Coupe feu' => 'Coupe feu',
        'Issue sans porte' => 'Issue sans porte',
        'Porte normale' => 'Porte normale',
        'Porte de passage transparente' => 'Porte de passage transparente',
    ];

    public const ETAT_BARRE = [
        'ok' => 'OK',
        'Nok' => 'NOK',
        'NA' => 'N/A',
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

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $barreAntipanique = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\OneToMany(mappedBy: 'issueSecours', targetEntity: InspectionIssueSecours::class, orphanRemoval: true)]
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getBarreAntipanique(): ?string
    {
        return $this->barreAntipanique;
    }

    public function setBarreAntipanique(?string $barreAntipanique): static
    {
        $this->barreAntipanique = $barreAntipanique;
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
     * @return Collection<int, InspectionIssueSecours>
     */
    public function getInspections(): Collection
    {
        return $this->inspections;
    }

    public function addInspection(InspectionIssueSecours $inspection): static
    {
        if (!$this->inspections->contains($inspection)) {
            $this->inspections->add($inspection);
            $inspection->setIssueSecours($this);
        }
        return $this;
    }

    public function removeInspection(InspectionIssueSecours $inspection): static
    {
        if ($this->inspections->removeElement($inspection)) {
            if ($inspection->getIssueSecours() === $this) {
                $inspection->setIssueSecours(null);
            }
        }
        return $this;
    }

    public function getDerniereInspection(): ?InspectionIssueSecours
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

