<?php

namespace App\Entity;

use App\Repository\InspectionMonteChargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InspectionMonteChargeRepository::class)]
#[ORM\Table(name: 'inspection_monte_charge')]
class InspectionMonteCharge
{
    public const PORTES = [
        'PORTE 1' => 'PORTE 1',
        'PORTE 2' => 'PORTE 2',
        'PORTE 3' => 'PORTE 3',
        'PORTE 4' => 'PORTE 4',
        'PORTE 5' => 'PORTE 5',
        'PORTE 6' => 'PORTE 6'
    ];

    public const QUESTIONS = [
        'portes_monte_charge_fermees' => 'LES PORTES MONTE CHARGE SONT-ELLES FERMÉES ?',
        'consignes_utilisation_respectees' => 'LES CONSIGNES D\'UTILISATION SONT-ELLES RESPECTÉES ?',
        'fins_courses_fonctionnent' => 'LES FINS COURSES FONCTIONNENT CORRECTEMENT ?',
        'essai_vide_realise' => 'UN ESSAI À VIDE A ÉTÉ RÉALISÉ ?'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MonteCharge $monteCharge = null;

    #[ORM\Column(length: 50)]
    private ?string $numeroPorte = null;

    #[ORM\Column(type: Types::JSON)]
    private array $reponses = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoObservation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInspection = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $inspectePar = null;

    #[ORM\Column]
    private bool $valide = false;

    public function __construct()
    {
        $this->dateInspection = new \DateTime();
        $this->reponses = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMonteCharge(): ?MonteCharge
    {
        return $this->monteCharge;
    }

    public function setMonteCharge(?MonteCharge $monteCharge): static
    {
        $this->monteCharge = $monteCharge;
        return $this;
    }

    public function getNumeroPorte(): ?string
    {
        return $this->numeroPorte;
    }

    public function setNumeroPorte(string $numeroPorte): static
    {
        $this->numeroPorte = $numeroPorte;
        return $this;
    }

    public function getReponses(): array
    {
        return $this->reponses;
    }

    public function setReponses(array $reponses): static
    {
        $this->reponses = $reponses;
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

    public function isValide(): bool
    {
        return $this->valide;
    }

    public function setValide(bool $valide): static
    {
        $this->valide = $valide;
        return $this;
    }
}
