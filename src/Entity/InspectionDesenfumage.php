<?php

namespace App\Entity;

use App\Repository\InspectionDesenfumageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionDesenfumageRepository::class)]
#[ORM\Table(name: 'inspection_desenfumage')]
class InspectionDesenfumage
{
    public const CRITERES = [
        'visible_accessible' => 'Le système de désenfumage est visible et accessible',
        'mecanisme_fonctionne' => 'Le mécanisme d\'ouverture fonctionne',
        'commande_manuelle_ok' => 'La commande manuelle fonctionne',
        'ouvrants_degages' => 'Les ouvrants sont dégagés',
        'pas_obstacle' => 'Pas d\'obstacle bloquant l\'ouverture',
        'joints_bon_etat' => 'Les joints sont en bon état',
        'pas_degradation' => 'Pas de dégradation visible',
        'signalisation_presente' => 'La signalisation est présente',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Desenfumage $desenfumage = null;

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

    public function getDesenfumage(): ?Desenfumage
    {
        return $this->desenfumage;
    }

    public function setDesenfumage(?Desenfumage $desenfumage): static
    {
        $this->desenfumage = $desenfumage;
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

