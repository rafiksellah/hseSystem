<?php

namespace App\Entity;

use App\Repository\InspectionPrisePompierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InspectionPrisePompierRepository::class)]
#[ORM\Table(name: 'inspection_prise_pompier')]
class InspectionPrisePompier
{
    public const CRITERES = [
        'occupe_place' => 'La prise pompier occupe la place',
        'visible_accessible' => 'Elle est visible et accessible',
        'solidement_fixe' => 'La prise est solidement fixée',
        'non_endommagee' => 'La prise n\'est pas endommagée',
        'pas_traces_corrosion' => 'Ne présente pas de traces de corrosion',
        'propre' => 'La prise pompier est propre',
        'etiquette_bon_etat' => 'L\'étiquette existe et en bon état',
        'raccords_presents' => 'Les raccords sont présents et en bon état',
        'vannes_fonctionnelles' => 'Les vannes sont fonctionnelles',
        'pas_fuite' => 'Pas de fuite visible',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PrisePompier $prisePompier = null;

    #[ORM\Column(type: Types::JSON)]
    private array $criteres = [];

    #[ORM\Column]
    private bool $valide = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInspection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $inspectePar = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoObservation = null;

    public function __construct()
    {
        $this->dateInspection = new \DateTime();
        $this->criteres = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrisePompier(): ?PrisePompier
    {
        return $this->prisePompier;
    }

    public function setPrisePompier(?PrisePompier $prisePompier): static
    {
        $this->prisePompier = $prisePompier;
        return $this;
    }

    public function getCriteres(): array
    {
        return $this->criteres;
    }

    public function setCriteres(array $criteres): static
    {
        $this->criteres = $criteres;
        return $this;
    }

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;
        return $this;
    }

    public function getDateInspection(): ?\DateTimeInterface
    {
        return $this->dateInspection;
    }

    public function setDateInspection(\DateTimeInterface $dateInspection): static
    {
        $this->dateInspection = $dateInspection;
        return $this;
    }

    public function getInspectePar(): ?User
    {
        return $this->inspectePar;
    }

    public function setInspectePar(?User $inspectePar): static
    {
        $this->inspectePar = $inspectePar;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function getPhotoObservation(): ?string
    {
        return $this->photoObservation;
    }

    public function setPhotoObservation(?string $photoObservation): static
    {
        $this->photoObservation = $photoObservation;
        return $this;
    }
}

