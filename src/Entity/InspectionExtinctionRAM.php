<?php

namespace App\Entity;

use App\Repository\InspectionExtinctionRAMRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionExtinctionRAMRepository::class)]
#[ORM\Table(name: 'inspection_extinction_ram')]
class InspectionExtinctionRAM
{
    public const CONFORMITE = [
        'Oui' => 'Oui',
        'Non' => 'Non',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExtinctionLocaliseeRAM $extinctionLocaliseeRAM = null;

    #[ORM\Column(length: 10)]
    private ?string $conformite = null;

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

    #[ORM\Column]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $resetDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetReason = null;

    public function __construct()
    {
        $this->dateInspection = new \DateTime();
        $this->isActive = true;
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

    public function getConformite(): ?string
    {
        return $this->conformite;
    }

    public function setConformite(?string $conformite): static
    {
        $this->conformite = $conformite;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getResetDate(): ?\DateTimeInterface
    {
        return $this->resetDate;
    }

    public function setResetDate(?\DateTimeInterface $resetDate): static
    {
        $this->resetDate = $resetDate;
        return $this;
    }

    public function getResetReason(): ?string
    {
        return $this->resetReason;
    }

    public function setResetReason(?string $resetReason): static
    {
        $this->resetReason = $resetReason;
        return $this;
    }
}

