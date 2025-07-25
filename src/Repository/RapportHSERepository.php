<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\RapportHSE;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<RapportHSE>
 *
 * @method RapportHSE|null find($id, $lockMode = null, $lockVersion = null)
 * @method RapportHSE|null findOneBy(array $criteria, array $orderBy = null)
 * @method RapportHSE[]    findAll()
 * @method RapportHSE[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RapportHSERepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RapportHSE::class);
    }

    /**
     * Recherche des rapports avec filtres
     */
    public function searchRapports(array $searchParams, int $limit = 10, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->orderBy('r.dateCreation', 'DESC');

        // Filtre par code agent
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        // Filtre par nom
        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        // Filtre par zone de travail (zone du rapport, pas zone utilisateur)
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par zone utilisateur (pour les permissions)
        if (!empty($searchParams['zoneUtilisateur'])) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $searchParams['zoneUtilisateur']);
        }

        // Filtre par date de début
        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        // Filtre par date de fin
        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        // Filtre par date de clôture début
        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        // Filtre par date de clôture fin
        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
        }

        // Filtre par statut
        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        $qb->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre total de rapports correspondant aux critères de recherche
     */
    public function countSearchRapports(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.user', 'u');

        // Appliquer les mêmes filtres que dans searchRapports
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['zoneUtilisateur'])) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $searchParams['zoneUtilisateur']);
        }

        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
        }

        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouve les rapports par zone utilisateur
     */
    public function findByZoneUtilisateur(string $zoneUtilisateur): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
            ->setParameter('zoneUtilisateur', $zoneUtilisateur)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques par zone
     */
    public function getStatistiquesByZone(string $zoneUtilisateur = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as count')
            ->groupBy('r.statut');

        if ($zoneUtilisateur) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $zoneUtilisateur);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Rapports récents par zone
     */
    public function findRecentByZone(string $zoneUtilisateur = null, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit);

        if ($zoneUtilisateur) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $zoneUtilisateur);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Rapports en cours par zone
     */
    public function findEnCoursByZone(string $zoneUtilisateur = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.statut = :statut')
            ->setParameter('statut', 'En cours')
            ->orderBy('r.dateCreation', 'DESC');

        if ($zoneUtilisateur) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $zoneUtilisateur);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Rapports clôturés par zone
     */
    public function findCloturesByZone(string $zoneUtilisateur = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.statut = :statut')
            ->setParameter('statut', 'Clôturé')
            ->orderBy('r.dateCloture', 'DESC');

        if ($zoneUtilisateur) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $zoneUtilisateur);
        }

        return $qb->getQuery()->getResult();
    }

    private function addSearchFilters(QueryBuilder $qb, array $searchParams): void
    {
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone LIKE :zone')
                ->setParameter('zone', '%' . $searchParams['zone'] . '%');
        }

        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        // Filtre par zone utilisateur (nouveau)
        if (!empty($searchParams['zoneUtilisateur'])) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $searchParams['zoneUtilisateur']);
        }

        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
        }
    }

    /**
     * Trouver les rapports accessibles par un utilisateur donné
     */
    public function findRapportsForUser(User $user, array $searchParams = [], int $limit = null, int $offset = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        // Si c'est un super admin, il voit tout
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'r.user = :currentUser', // Ses propres rapports
                    'r.zoneUtilisateur = :userZone' // Rapports de sa zone (pour admin)
                )
            )
                ->setParameter('currentUser', $user)
                ->setParameter('userZone', $user->getZone());
        }

        $this->addSearchFilters($qb, $searchParams);

        $qb->orderBy('r.dateCreation', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Compter les rapports accessibles par un utilisateur donné
     */
    public function countRapportsForUser(User $user, array $searchParams = []): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.user', 'u');

        // Si c'est un super admin, il voit tout
        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $qb->andWhere(
                $qb->expr()->orX(
                    'r.user = :currentUser',
                    'r.zoneUtilisateur = :userZone'
                )
            )
                ->setParameter('currentUser', $user)
                ->setParameter('userZone', $user->getZone());
        }

        $this->addSearchFilters($qb, $searchParams);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouve les rapports paginés pour un utilisateur donné
     */
    public function findPaginatedByUser(User $user, int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;

        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de rapports pour un utilisateur
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les derniers rapports créés par un utilisateur
     */
    public function findRecentByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les rapports par statut pour un utilisateur
     */
    public function findByUserAndStatus(User $user, string $status): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.statut = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques pour le dashboard
     */
    public function getStatsForUser(User $user): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as count')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->groupBy('r.statut');

        $results = $qb->getQuery()->getResult();

        $stats = [
            'total' => 0,
            'en_cours' => 0,
            'cloture' => 0
        ];

        foreach ($results as $result) {
            $stats['total'] += $result['count'];
            if ($result['statut'] === 'En cours') {
                $stats['en_cours'] = $result['count'];
            } elseif ($result['statut'] === 'Clôturé') {
                $stats['cloture'] = $result['count'];
            }
        }

        return $stats;
    }

    /**
     * Récupère un utilisateur par son code agent (utile pour l'admin)
     */
    public function findUserByCodeAgent(string $codeAgent): ?User
    {
        return $this->getEntityManager()
            ->getRepository(User::class)
            ->findOneBy(['codeAgent' => $codeAgent]);
    }

    /**
     * Récupère tous les codes agents disponibles
     */
    public function getAllCodesAgents(): array
    {
        $users = $this->getEntityManager()
            ->getRepository(User::class)
            ->findAll();

        $codesAgents = [];
        foreach ($users as $user) {
            $codesAgents[] = [
                'codeAgent' => $user->getCodeAgent(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom()
            ];
        }

        return $codesAgents;
    }


    /**
     * Obtient le nombre de rapports créés ce mois par un utilisateur
     */
    public function getRapportsCeMoisPourUtilisateur(User $user): int
    {
        $startOfMonth = new \DateTime('first day of this month 00:00:00');
        $endOfMonth = new \DateTime('last day of this month 23:59:59');

        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.dateCreation BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtient le nombre de rapports créés cette année par un utilisateur
     */
    public function getRapportsCetteAnneePourUtilisateur(User $user): int
    {
        $startOfYear = new \DateTime('first day of January this year 00:00:00');
        $endOfYear = new \DateTime('last day of December this year 23:59:59');

        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user')
            ->andWhere('r.dateCreation BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startOfYear)
            ->setParameter('end', $endOfYear)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Obtient les rapports par zone pour un utilisateur
     */
    public function getRapportsParZonePourUtilisateur(User $user): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.zone, COUNT(r.id) as nombre')
            ->where('r.user = :user')
            ->andWhere('r.zone IS NOT NULL')
            ->groupBy('r.zone')
            ->orderBy('nombre', 'DESC')
            ->setParameter('user', $user);

        $results = $qb->getQuery()->getResult();

        $formatted = [];
        foreach ($results as $result) {
            $formatted[$result['zone']] = (int) $result['nombre'];
        }

        return $formatted;
    }

    /**
     * Obtient l'évolution mensuelle des rapports pour un utilisateur
     */
    public function getRapportsParMoisPourUtilisateur(User $user, int $nombreMois = 12): array
    {
        $endDate = new \DateTime();
        $startDate = (clone $endDate)->modify("-{$nombreMois} months");

        // Récupérer tous les rapports de l'utilisateur dans la période
        $rapports = $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->andWhere('r.dateCreation >= :startDate')
            ->setParameter('user', $user)
            ->setParameter('startDate', $startDate)
            ->getQuery()
            ->getResult();

        // Grouper par mois manuellement
        $grouped = [];
        foreach ($rapports as $rapport) {
            $moisKey = $rapport->getDateCreation()->format('Y-m');
            if (!isset($grouped[$moisKey])) {
                $grouped[$moisKey] = 0;
            }
            $grouped[$moisKey]++;
        }

        // Formatter les résultats
        $formatted = [];
        foreach ($grouped as $moisKey => $nombre) {
            $date = \DateTime::createFromFormat('Y-m', $moisKey);
            $formatted[] = [
                'mois' => $date->format('M Y'),
                'nombre' => $nombre
            ];
        }

        // Trier par mois
        usort($formatted, function ($a, $b) {
            return strtotime($a['mois']) - strtotime($b['mois']);
        });

        return $formatted;
    }

    /**
     * Recherche de rapports spécifique pour un utilisateur connecté UNIQUEMENT
     */
    public function searchRapportsForUser(array $searchParams, int $limit, int $offset): array
    {
        // Vérifier que l'utilisateur est bien passé en paramètre
        if (!isset($searchParams['user']) || !$searchParams['user'] instanceof User) {
            throw new \InvalidArgumentException('L\'utilisateur doit être fourni pour cette recherche.');
        }

        $qb = $this->createQueryBuilder('r')
            ->where('r.user = :user') // Utiliser where au lieu de andWhere pour la première condition
            ->setParameter('user', $searchParams['user']);

        // Recherche par zone de travail (pas zone utilisateur)
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone') // Utiliser = au lieu de LIKE pour une correspondance exacte
                ->setParameter('zone', $searchParams['zone']);
        }

        // Recherche par période
        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        // Recherche par statut
        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les rapports pour la recherche utilisateur
     */
    public function countSearchRapportsForUser(array $searchParams): int
    {
        // Vérifier que l'utilisateur est bien passé en paramètre
        if (!isset($searchParams['user']) || !$searchParams['user'] instanceof User) {
            throw new \InvalidArgumentException('L\'utilisateur doit être fourni pour cette recherche.');
        }

        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.user = :user') // Utiliser where au lieu de andWhere
            ->setParameter('user', $searchParams['user']);

        // Mêmes conditions que pour la recherche
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone') // Utiliser = au lieu de LIKE
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Méthode simple pour récupérer tous les rapports d'un utilisateur (pour debug)
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Méthode pour vérifier qu'un rapport appartient bien à un utilisateur
     */
    public function isRapportOwnedByUser(int $rapportId, User $user): bool
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.id = :rapportId')
            ->andWhere('r.user = :user')
            ->setParameter('rapportId', $rapportId)
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }


    /**
     * Recherche de rapports avec filtres étendus pour super admin
     */
    public function searchRapportSuperAdmin(array $searchParams, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        // Recherche par code AGT
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        // Recherche par nom
        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        // Filtre par zone de travail
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par zone utilisateur
        if (!empty($searchParams['zoneUtilisateur'])) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $searchParams['zoneUtilisateur']);
        }

        // Filtre par période de création
        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        // Filtre par période de clôture
        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
        }

        // Filtre par statut
        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        return $qb->orderBy('r.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les rapports pour la recherche
     */
    public function countSearchRapportSuperAdmin(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->leftJoin('r.user', 'u');

        // Mêmes conditions que la recherche
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['zoneUtilisateur'])) {
            $qb->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
                ->setParameter('zoneUtilisateur', $searchParams['zoneUtilisateur']);
        }

        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
        }

        if (!empty($searchParams['statut'])) {
            $qb->andWhere('r.statut = :statut')
                ->setParameter('statut', $searchParams['statut']);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * Évolution des rapports par mois - Version compatible
     */
    public function getRapportsParMois(int $mois = 12): array
    {
        $dateDebut = new \DateTime('-' . $mois . ' months');

        // Récupérer tous les rapports dans la période
        $qb = $this->createQueryBuilder('r')
            ->where('r.dateCreation >= :dateDebut')
            ->setParameter('dateDebut', $dateDebut)
            ->orderBy('r.dateCreation', 'ASC');

        $rapports = $qb->getQuery()->getResult();

        // Grouper par mois en PHP
        $data = [];
        foreach ($rapports as $rapport) {
            $periode = $rapport->getDateCreation()->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $data[$periode]++;
        }

        // S'assurer d'avoir tous les mois même avec 0 rapport
        $current = clone $dateDebut;
        $now = new \DateTime();
        while ($current <= $now) {
            $periode = $current->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $current->modify('+1 month');
        }

        ksort($data); // Trier par ordre chronologique
        return $data;
    }

    /**
     * Répartition des rapports par statut
     */
    public function getRapportsParStatut(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as total')
            ->groupBy('r.statut')
            ->orderBy('total', 'DESC');

        $results = $qb->getQuery()->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[$result['statut']] = $result['total'];
        }

        return $data;
    }

    /**
     * Statistiques par zone utilisateur
     */
    public function getRapportsParZoneUtilisateur(): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.zoneUtilisateur as zone, COUNT(r.id) as total')
            ->where('r.zoneUtilisateur IS NOT NULL')
            ->groupBy('r.zoneUtilisateur')
            ->orderBy('total', 'DESC');

        $results = $qb->getQuery()->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[$result['zone']] = $result['total'];
        }

        return $data;
    }

    /**
     * Top des zones les plus actives
     */
    public function getTopZonesActives(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.zone, COUNT(r.id) as total')
            ->where('r.zone IS NOT NULL')
            ->groupBy('r.zone')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques globales des rapports
     */
    public function getGlobalStats(): array
    {
        return [
            'total' => $this->count([]),
            'en_cours' => $this->count(['statut' => 'En cours']),
            'clotures' => $this->count(['statut' => 'Clôturé']),
            'par_zone_utilisateur' => $this->getRapportsParZoneUtilisateur(),
            'par_statut' => $this->getRapportsParStatut(),
            'recent' => $this->findBy([], ['dateCreation' => 'DESC'], 10),
            'zones_actives' => $this->getTopZonesActives(5)
        ];
    }

    /**
     * Statistiques des rapports par zone utilisateur spécifique
     */
    public function getStatsParZone(string $zone): array
    {
        return [
            'total' => $this->count(['zoneUtilisateur' => $zone]),
            'en_cours' => $this->count(['zoneUtilisateur' => $zone, 'statut' => 'En cours']),
            'clotures' => $this->count(['zoneUtilisateur' => $zone, 'statut' => 'Clôturé']),
            'recent' => $this->findBy(['zoneUtilisateur' => $zone], ['dateCreation' => 'DESC'], 10)
        ];
    }

    /**
     * Évolution des rapports par mois pour une zone spécifique
     */
    public function getRapportsParMoisZone(string $zone, int $mois = 12): array
    {
        $dateDebut = new \DateTime('-' . $mois . ' months');

        $qb = $this->createQueryBuilder('r')
            ->where('r.dateCreation >= :dateDebut')
            ->andWhere('r.zoneUtilisateur = :zone')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('zone', $zone)
            ->orderBy('r.dateCreation', 'ASC');

        $rapports = $qb->getQuery()->getResult();

        $data = [];
        foreach ($rapports as $rapport) {
            $periode = $rapport->getDateCreation()->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $data[$periode]++;
        }

        // S'assurer d'avoir tous les mois même avec 0 rapport
        $current = clone $dateDebut;
        $now = new \DateTime();
        while ($current <= $now) {
            $periode = $current->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $current->modify('+1 month');
        }

        ksort($data);
        return $data;
    }

    /**
     * Répartition des rapports par statut pour une zone
     */
    public function getRapportsParStatutZone(string $zone): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.statut, COUNT(r.id) as total')
            ->where('r.zoneUtilisateur = :zone')
            ->setParameter('zone', $zone)
            ->groupBy('r.statut')
            ->orderBy('total', 'DESC');

        $results = $qb->getQuery()->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[$result['statut']] = $result['total'];
        }

        return $data;
    }

    /**
     * Top des zones de travail les plus actives pour une zone utilisateur
     */
    public function getTopZonesActivesParZone(string $zoneUtilisateur, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.zone, COUNT(r.id) as total')
            ->where('r.zone IS NOT NULL')
            ->andWhere('r.zoneUtilisateur = :zoneUtilisateur')
            ->setParameter('zoneUtilisateur', $zoneUtilisateur)
            ->groupBy('r.zone')
            ->orderBy('total', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Rapports par utilisateur dans une zone
     */
    public function getRapportsParUtilisateurZone(string $zone): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('u.nom, u.prenom, u.codeAgent, COUNT(r.id) as total')
            ->leftJoin('r.user', 'u')
            ->where('r.zoneUtilisateur = :zone')
            ->setParameter('zone', $zone)
            ->groupBy('u.id, u.nom, u.prenom, u.codeAgent')
            ->orderBy('total', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Performance mensuelle d'une zone (taux de clôture)
     */
    public function getPerformanceZone(string $zone, int $mois = 6): array
    {
        $dateDebut = new \DateTime('-' . $mois . ' months');

        $qb = $this->createQueryBuilder('r')
            ->where('r.dateCreation >= :dateDebut')
            ->andWhere('r.zoneUtilisateur = :zone')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('zone', $zone)
            ->orderBy('r.dateCreation', 'ASC');

        $rapports = $qb->getQuery()->getResult();

        $data = [];
        foreach ($rapports as $rapport) {
            $periode = $rapport->getDateCreation()->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = ['total' => 0, 'clotures' => 0];
            }
            $data[$periode]['total']++;
            if ($rapport->getStatut() === 'Clôturé') {
                $data[$periode]['clotures']++;
            }
        }

        // Calculer le taux de clôture pour chaque mois
        foreach ($data as &$monthData) {
            $monthData['taux'] = $monthData['total'] > 0 ?
                round(($monthData['clotures'] / $monthData['total']) * 100, 1) : 0;
        }

        return $data;
    }
}