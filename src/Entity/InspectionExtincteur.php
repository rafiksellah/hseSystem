<?php

namespace App\Entity;

use App\Repository\InspectionExtincteurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InspectionExtincteurRepository::class)]
#[ORM\Table(name: 'inspection_extincteur')]
class InspectionExtincteur
{
    public const CRITERES = [
        'occupe_place' => 'L\'extincteur occupe la place',
        'visible_accessible' => 'Il est visible et accessible',
        'solidement_fixe' => 'Le RIA est solidement fixé',
        'corps_non_endommage' => 'Le corps d\'extincteur n\'est pas endommagé',
        'pas_traces_corrosion' => 'Ne présente pas de traces de corrosion',
        'propre_couleur_rouge' => 'L\'extincteur est propre (couleur rouge)',
        'etiquette_bon_etat' => 'L\'étiquette existe et en bon état',
        'date_verification_ok' => 'La date de la prochaine vérification n\'est pas atteinte',
        'flexible_gachette_ok' => 'Flexible et gâchette sont présents et en bon état',
        'dispositif_fixation_solide' => 'Dispositif de fixation est solide',
        'poignee_robuste' => 'Poignée ou levier est robuste et n\'est pas endommagé',
        'goupille_plomb_presents' => 'Goupille et plomb de verrouillage sont présents'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Extincteur $extincteur = null;

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

    public function __construct()
    {
        $this->dateInspection = new \DateTime();
        $this->criteres = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtincteur(): ?Extincteur
    {
        return $this->extincteur;
    }

    public function setExtincteur(?Extincteur $extincteur): static
    {
        $this->extincteur = $extincteur;
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
}
