<?php

namespace App\Entity;

use App\Repository\InspectionSireneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionSireneRepository::class)]
#[ORM\Table(name: 'inspection_sirene')]
class InspectionSirene
{
    public const CRITERES = [
        'visible_accessible' => 'La sirène est visible et accessible',
        'solidement_fixee' => 'Elle est solidement fixée',
        'non_endommagee' => 'La sirène n\'est pas endommagée',
        'propre' => 'La sirène est propre',
        'cable_bon_etat' => 'Le câblage est en bon état',
        'test_sonore_ok' => 'Test sonore effectué et OK',
        'volume_adequat' => 'Le volume sonore est adéquat',
        'pas_corrosion' => 'Pas de traces de corrosion',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sirene $sirene = null;

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

    public function getSirene(): ?Sirene
    {
        return $this->sirene;
    }

    public function setSirene(?Sirene $sirene): static
    {
        $this->sirene = $sirene;
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

