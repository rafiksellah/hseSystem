<?php

namespace App\Entity;

use App\Repository\RapportHSERepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RapportHSERepository::class)]
class RapportHSE
{
    public const STATUT_EN_COURS = 'En cours';
    public const STATUT_CLOTURE = 'Clôturé';
    // Constantes pour les zones SIMTIS
    public const ZONES_SIMTIS = [
        'ADMINISTRATION' => 'Administration',
        'BRODERIE' => 'Broderie',
        'BUREAUX_ETUDES' => 'Bureaux d\'études',
        'CALANDRAGE' => 'Calandrage',
        'CHALES_FOULARDS_BC' => 'Châles et foulards BC',
        'CHAUFFERIE' => 'Chaufferie',
        'CONFECTION_DECATHLON' => 'Confection Decathlon',
        'DECHETS_EXTENSION' => 'Déchets extension',
        'DETORTIONNEUSES' => 'Détortionneuses',
        'DIAMANTINE_CONFECTION_HABILLEMENT' => 'Diamantine confection habillement',
        'EMBALLAGE_BC' => 'Emballage BC',
        'FUEL' => 'Fuel',
        'GRATTAGE' => 'Grattage',
        'GRAVURE_BC' => 'Gravure BC',
        'GROUPE_ELECTROGENE' => 'Groupe électrogène',
        'IMPRESSION_NUMERIQUE' => 'Impression numérique',
        'LIVRAISON' => 'Livraison',
        'PREPARATION' => 'Préparation',
        'RAM' => 'RAM',
        'ROTATIVE' => 'Rotative',
        'ROULAGE' => 'Roulage',
        'SIMI' => 'SIMI',
        'STATION_LAVAGE' => 'Station lavage',
        'STOCK_DECATHLON' => 'Stock Decathlon',
        'STOCK_PF' => 'Stock PF',
        'STRASS_BC' => 'Strass BC',
        'TEINTURE' => 'Teinture',
        'PANNEAUX_SOLAIRES' => 'Panneaux solaires',
    ];

    // Constantes pour les zones SIMTIS TISSAGE
    public const ZONES_SIMTIS_TISSAGE = [
        'POSTE_ELECTRIQUE' => 'Poste électrique',
        'CHAUDIERE' => 'Chaudière',
        'COMPRESSEUR_TEFIL' => 'Compresseur tefil',
        'RENTRAGE' => 'Rentrage',
        'PREPARATION' => 'Préparation',
        'ADMINISTRATION' => 'Administration',
        'MAGASIN_TEFIL' => 'Magasin tefil',
        'TISSAGE_RDC' => 'Tissage RDC',
        'MACHINES_JACARD' => 'Les machines jacard',
        'CONTROLE_QUALITE' => 'Contrôle qualité',
        'MACHINE_CAG' => 'Machine cag',
        'MACHINE_AIR' => 'Machine d\'air',
        'OURDISSOIR' => 'Ourdissoir',
        'CANTINE_HOMME_FEMME' => 'Cantine homme et femme',
        'DECHETS_PRATO' => 'Déchets PRATO',
        'PRODUIT_FINI' => 'Produit fini',
        'STOCK_BEILLA' => 'Stock beilla',
        'STOCK_PRATO' => 'Stock prato',
        'MAGASIN_PRATO' => 'Magasin prato',
        'STIFA_RDC' => 'Stifa RDC',
        'MEZZANINE' => 'Mezzanine',
        'COMPRESSEUR_PRATO' => 'Compresseur prato',
        'PANNEAUX_SOLAIRES' => 'Panneaux solaires',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le code AGT ne peut pas être vide')]
    private ?string $codeAgt = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
    private ?string $nom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'La date ne peut pas être vide')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotNull(message: 'L\'heure ne peut pas être vide')]
    private ?\DateTimeInterface $heure = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $zone = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $emplacement = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $equipementProduitConcerne = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descriptionAnomalie = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $causeProbable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoConstat = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $actionCloturee = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCloture = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $heureAction = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoActionCloturee = null;

    #[ORM\ManyToOne(inversedBy: 'rapportsHSE')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = 'En cours';

    #[ORM\Column(length: 50)]
    private ?string $zoneUtilisateur = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
        $this->date = new \DateTime();
        $this->heure = new \DateTime();
    }

    // Méthode pour obtenir les zones selon la zone de l'utilisateur
    public static function getZonesForUserZone(string $userZone): array
    {
        return match ($userZone) {
            'SIMTIS TISSAGE' => self::ZONES_SIMTIS_TISSAGE,
            'SIMTIS' => self::ZONES_SIMTIS,
            default => self::ZONES_SIMTIS,
        };
    }

    // Méthode pour obtenir toutes les zones disponibles (pour super admin)
    public static function getAllZones(): array
    {
        return array_merge(self::ZONES_SIMTIS, self::ZONES_SIMTIS_TISSAGE);
    }

    // Méthode pour vérifier si un utilisateur peut accéder à ce rapport
    public function canBeAccessedByUser(User $user): bool
    {
        // Super admin peut tout voir
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        // L'utilisateur peut voir ses propres rapports
        if ($this->user && $this->user->getId() === $user->getId()) {
            return true;
        }

        // Admin peut voir les rapports de sa zone
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->zoneUtilisateur === $user->getZone();
        }

        return false;
    }

    // Méthode pour vérifier si un utilisateur peut modifier ce rapport
    public function canBeModifiedByUser(User $user): bool
    {
        // Super admin peut tout modifier
        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            return true;
        }

        // L'utilisateur peut modifier ses propres rapports
        if ($this->user && $this->user->getId() === $user->getId()) {
            return true;
        }

        // Admin peut modifier les rapports de sa zone
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->zoneUtilisateur === $user->getZone();
        }

        return false;
    }

    // Getter et setter pour zoneUtilisateur
    public function getZoneUtilisateur(): ?string
    {
        return $this->zoneUtilisateur;
    }

    public function setZoneUtilisateur(string $zoneUtilisateur): static
    {
        $this->zoneUtilisateur = $zoneUtilisateur;
        return $this;
    }

    // Auto-définir la zone utilisateur basée sur l'utilisateur associé
    public function setUser(?User $user): static
    {
        $this->user = $user;
        if ($user) {
            $this->zoneUtilisateur = $user->getZone();
        }
        return $this;
    }

    // Getters et setters existants...
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeAgt(): ?string
    {
        return $this->codeAgt;
    }

    public function setCodeAgt(string $codeAgt): static
    {
        $this->codeAgt = $codeAgt;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;
        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;
        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(?string $emplacement): static
    {
        $this->emplacement = $emplacement;
        return $this;
    }

    public function getEquipementProduitConcerne(): ?string
    {
        return $this->equipementProduitConcerne;
    }

    public function setEquipementProduitConcerne(?string $equipementProduitConcerne): static
    {
        $this->equipementProduitConcerne = $equipementProduitConcerne;
        return $this;
    }

    public function getDescriptionAnomalie(): ?string
    {
        return $this->descriptionAnomalie;
    }

    public function setDescriptionAnomalie(?string $descriptionAnomalie): static
    {
        $this->descriptionAnomalie = $descriptionAnomalie;
        return $this;
    }

    public function getCauseProbable(): ?string
    {
        return $this->causeProbable;
    }

    public function setCauseProbable(?string $causeProbable): static
    {
        $this->causeProbable = $causeProbable;
        return $this;
    }

    public function getPhotoConstat(): ?string
    {
        return $this->photoConstat;
    }

    public function setPhotoConstat(?string $photoConstat): static
    {
        $this->photoConstat = $photoConstat;
        return $this;
    }

    public function getActionCloturee(): ?string
    {
        return $this->actionCloturee;
    }

    public function setActionCloturee(?string $actionCloturee): static
    {
        $this->actionCloturee = $actionCloturee;
        return $this;
    }

    public function getDateCloture(): ?\DateTimeInterface
    {
        return $this->dateCloture;
    }

    public function setDateCloture(?\DateTimeInterface $dateCloture): static
    {
        $this->dateCloture = $dateCloture;
        return $this;
    }

    public function getHeureAction(): ?\DateTimeInterface
    {
        return $this->heureAction;
    }

    public function setHeureAction(?\DateTimeInterface $heureAction): static
    {
        $this->heureAction = $heureAction;
        return $this;
    }

    public function getPhotoActionCloturee(): ?string
    {
        return $this->photoActionCloturee;
    }

    public function setPhotoActionCloturee(?string $photoActionCloturee): static
    {
        $this->photoActionCloturee = $photoActionCloturee;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
}
