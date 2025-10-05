<?php

namespace App\Entity;

use App\Repository\InspectionExtinctionRAMRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionExtinctionRAMRepository::class)]
#[ORM\Table(name: 'inspection_extinction_ram')]
class InspectionExtinctionRAM
{
    public const CRITERES = [
        'systeme_accessible' => 'Le système d\'extinction est accessible',
        'commande_manuelle_ok' => 'La commande manuelle fonctionne',
        'detection_ok' => 'Le système de détection fonctionne',
        'buses_propres' => 'Les buses sont propres et dégagées',
        'pression_ok' => 'La pression est correcte',
        'pas_fuite' => 'Pas de fuite visible',
        'signalisation_presente' => 'La signalisation est présente',
        'extincteur_present' => 'L\'extincteur associé est présent',
        'pas_degradation' => 'Pas de dégradation visible du système',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExtinctionLocaliseeRAM $extinctionLocaliseeRAM = null;

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

    public function getExtinctionLocaliseeRAM(): ?ExtinctionLocaliseeRAM
    {
        return $this->extinctionLocaliseeRAM;
    }

    public function setExtinctionLocaliseeRAM(?ExtinctionLocaliseeRAM $extinctionLocaliseeRAM): static
    {
        $this->extinctionLocaliseeRAM = $extinctionLocaliseeRAM;
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

