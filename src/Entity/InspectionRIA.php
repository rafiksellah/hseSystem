<?php

namespace App\Entity;

use App\Repository\InspectionRIARepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InspectionRIARepository::class)]
#[ORM\Table(name: 'inspection_ria')]
class InspectionRIA
{
    public const CRITERES = [
        'occupe_place' => 'Le RIA occupe la place',
        'visible_accessible' => 'Il est visible et accessible',
        'solidement_fixe' => 'Le RIA est solidement fixé',
        'armoire_bon_etat' => 'L\'armoire est en bon état',
        'pas_traces_corrosion' => 'Ne présente pas de traces de corrosion',
        'propre' => 'Le RIA est propre',
        'etiquette_bon_etat' => 'L\'étiquette existe et en bon état',
        'tuyau_bon_etat' => 'Le tuyau est en bon état (pas de fissures)',
        'robinet_fonctionne' => 'Le robinet fonctionne correctement',
        'lance_presente' => 'La lance est présente et en bon état',
        'pas_fuite' => 'Pas de fuite visible',
        'pression_adequate' => 'La pression est adéquate',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RIA $ria = null;

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

    public function getRia(): ?RIA
    {
        return $this->ria;
    }

    public function setRia(?RIA $ria): static
    {
        $this->ria = $ria;
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

