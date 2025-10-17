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
    public const QUESTIONS = [
        'portes_fermees' => 'Les portes monte-charge sont-elles fermées ?',
        'consignes_respectees' => 'Les consignes d\'utilisation sont-elles respectées ?',
        'fins_courses_fonctionnent' => 'Les fins de course fonctionnent correctement ?',
        'essai_vide_realise' => 'Un essai à vide a été réalisé ?',
    ];

    public const REPONSES = [
        'Oui' => 'Oui',
        'Non' => 'Non',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MonteCharge::class, inversedBy: 'inspections')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MonteCharge $monteCharge = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inspectionsMonteCharge')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $inspecteur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateInspection = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La conformité ne peut pas être vide')]
    #[Assert\Choice(choices: self::REPONSES, message: 'Conformité invalide')]
    private ?string $portesFermees = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La conformité ne peut pas être vide')]
    #[Assert\Choice(choices: self::REPONSES, message: 'Conformité invalide')]
    private ?string $consignesRespectees = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La conformité ne peut pas être vide')]
    #[Assert\Choice(choices: self::REPONSES, message: 'Conformité invalide')]
    private ?string $finsCoursesFonctionnent = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: 'La conformité ne peut pas être vide')]
    #[Assert\Choice(choices: self::REPONSES, message: 'Conformité invalide')]
    private ?string $essaiVideRealise = null;

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

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le numéro de porte ne peut pas être vide')]
    private ?string $numeroPorte = null;

    public function __construct()
    {
        $this->dateInspection = new \DateTime();
        $this->isActive = true;
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

    public function getInspecteur(): ?User
    {
        return $this->inspecteur;
    }

    public function setInspecteur(?User $inspecteur): static
    {
        $this->inspecteur = $inspecteur;
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

    public function getPortesFermees(): ?string
    {
        return $this->portesFermees;
    }

    public function setPortesFermees(string $portesFermees): static
    {
        $this->portesFermees = $portesFermees;
        return $this;
    }

    public function getConsignesRespectees(): ?string
    {
        return $this->consignesRespectees;
    }

    public function setConsignesRespectees(string $consignesRespectees): static
    {
        $this->consignesRespectees = $consignesRespectees;
        return $this;
    }

    public function getFinsCoursesFonctionnent(): ?string
    {
        return $this->finsCoursesFonctionnent;
    }

    public function setFinsCoursesFonctionnent(string $finsCoursesFonctionnent): static
    {
        $this->finsCoursesFonctionnent = $finsCoursesFonctionnent;
        return $this;
    }

    public function getEssaiVideRealise(): ?string
    {
        return $this->essaiVideRealise;
    }

    public function setEssaiVideRealise(string $essaiVideRealise): static
    {
        $this->essaiVideRealise = $essaiVideRealise;
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

    public function getNumeroPorte(): ?string
    {
        return $this->numeroPorte;
    }

    public function setNumeroPorte(string $numeroPorte): static
    {
        $this->numeroPorte = $numeroPorte;
        return $this;
    }

    public function isConforme(): bool
    {
        return $this->portesFermees === 'Oui' && 
               $this->consignesRespectees === 'Oui' && 
               $this->finsCoursesFonctionnent === 'Oui' && 
               $this->essaiVideRealise === 'Oui';
    }

    public function __toString(): string
    {
        return sprintf(
            'Inspection %s - %s (%s)',
            $this->monteCharge?->getNumeroMonteCharge() ?? 'N/A',
            $this->conformite ?? 'N/A',
            $this->dateInspection?->format('d/m/Y H:i') ?? 'N/A'
        );
    }
}