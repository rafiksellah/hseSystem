<?php

namespace App\Entity;

use App\Repository\ResetInspectionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResetInspectionRepository::class)]
#[ORM\Table(name: 'reset_inspection')]
class ResetInspection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $equipmentType = null;

    #[ORM\Column]
    private ?int $equipmentId = null;

    #[ORM\Column(length: 100)]
    private ?string $equipmentName = null;

    #[ORM\Column(type: Types::JSON)]
    private array $inspectionData = [];

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $resetDate = null;

    #[ORM\Column(length: 20)]
    private ?string $resetType = null; // 'monthly' ou 'daily'

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetReason = null;

    #[ORM\ManyToOne]
    private ?User $resetBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipmentType(): ?string
    {
        return $this->equipmentType;
    }

    public function setEquipmentType(string $equipmentType): static
    {
        $this->equipmentType = $equipmentType;
        return $this;
    }

    public function getEquipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function setEquipmentId(int $equipmentId): static
    {
        $this->equipmentId = $equipmentId;
        return $this;
    }

    public function getEquipmentName(): ?string
    {
        return $this->equipmentName;
    }

    public function setEquipmentName(string $equipmentName): static
    {
        $this->equipmentName = $equipmentName;
        return $this;
    }

    public function getInspectionData(): array
    {
        return $this->inspectionData;
    }

    public function setInspectionData(array $inspectionData): static
    {
        $this->inspectionData = $inspectionData;
        return $this;
    }

    public function getResetDate(): ?\DateTimeInterface
    {
        return $this->resetDate;
    }

    public function setResetDate(\DateTimeInterface $resetDate): static
    {
        $this->resetDate = $resetDate;
        return $this;
    }

    public function getResetType(): ?string
    {
        return $this->resetType;
    }

    public function setResetType(string $resetType): static
    {
        $this->resetType = $resetType;
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

    public function getResetBy(): ?User
    {
        return $this->resetBy;
    }

    public function setResetBy(?User $resetBy): static
    {
        $this->resetBy = $resetBy;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    // Constantes pour les types d'équipements
    public const EQUIPMENT_TYPES = [
        'extincteur' => 'Extincteur',
        'sirene' => 'Sirène',
        'extinction_ram' => 'Extinction RAM',
        'monte_charge' => 'Monte Charge'
    ];

    // Constantes pour les types de réinitialisation
    public const RESET_TYPES = [
        'monthly' => 'Mensuel',
        'daily' => 'Quotidien',
        'manual' => 'Manuel'
    ];
}
