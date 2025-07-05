<?php

namespace App\Repository;

use App\Entity\RapportHSE;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function searchRapports(array $searchParams, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u');

        // Recherche par code agent
        if (!empty($searchParams['codeAgt'])) {
            $qb->andWhere('r.codeAgt LIKE :codeAgt')
                ->setParameter('codeAgt', '%' . $searchParams['codeAgt'] . '%');
        }

        // Recherche par nom
        if (!empty($searchParams['nom'])) {
            $qb->andWhere('r.nom LIKE :nom')
                ->setParameter('nom', '%' . $searchParams['nom'] . '%');
        }

        // Recherche par zone
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone LIKE :zone')
                ->setParameter('zone', '%' . $searchParams['zone'] . '%');
        }

        // Recherche par période de création
        if (!empty($searchParams['dateDebut'])) {
            $qb->andWhere('r.date >= :dateDebut')
                ->setParameter('dateDebut', new \DateTime($searchParams['dateDebut']));
        }

        if (!empty($searchParams['dateFin'])) {
            $qb->andWhere('r.date <= :dateFin')
                ->setParameter('dateFin', new \DateTime($searchParams['dateFin']));
        }

        // Recherche par période de clôture
        if (!empty($searchParams['dateClotureDebut'])) {
            $qb->andWhere('r.dateCloture >= :dateClotureDebut')
                ->setParameter('dateClotureDebut', new \DateTime($searchParams['dateClotureDebut']));
        }

        if (!empty($searchParams['dateClotureFin'])) {
            $qb->andWhere('r.dateCloture <= :dateClotureFin')
                ->setParameter('dateClotureFin', new \DateTime($searchParams['dateClotureFin']));
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

    public function countSearchRapports(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)');

        // Mêmes conditions que pour la recherche
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
     * Récupère les rapports par mois pour un utilisateur donné
     */
    public function getRapportsParMoisPourUtilisateur(User $user, int $nombreMois = 6): array
    {
        $dateDebut = new \DateTime();
        $dateDebut->modify('-' . $nombreMois . ' months');

        // Récupérer tous les rapports dans la période
        $rapports = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.date >= :dateDebut')
            ->setParameter('user', $user)
            ->setParameter('dateDebut', $dateDebut)
            ->orderBy('r.date', 'ASC')
            ->getQuery()
            ->getResult();

        // Créer un tableau avec tous les mois, même ceux sans rapport
        $rapportsParMois = [];
        for ($i = $nombreMois - 1; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify('-' . $i . ' months');
            $mois = $date->format('Y-m');
            $rapportsParMois[$mois] = 0;
        }

        // Compter les rapports par mois
        foreach ($rapports as $rapport) {
            $mois = $rapport->getDate()->format('Y-m');
            if (isset($rapportsParMois[$mois])) {
                $rapportsParMois[$mois]++;
            }
        }

        return $rapportsParMois;
    }

    /**
     * Récupère le nombre de rapports de ce mois pour un utilisateur
     */
    public function getRapportsCeMoisPourUtilisateur(User $user): int
    {
        $debutMois = new \DateTime('first day of this month');
        $finMois = new \DateTime('last day of this month');

        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.user = :user')
            ->andWhere('r.date >= :debutMois')
            ->andWhere('r.date <= :finMois')
            ->setParameter('user', $user)
            ->setParameter('debutMois', $debutMois)
            ->setParameter('finMois', $finMois)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère le nombre de rapports de cette année pour un utilisateur
     */
    public function getRapportsCetteAnneePourUtilisateur(User $user): int
    {
        $debutAnnee = new \DateTime('first day of January this year');
        $finAnnee = new \DateTime('last day of December this year');

        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.user = :user')
            ->andWhere('r.date >= :debutAnnee')
            ->andWhere('r.date <= :finAnnee')
            ->setParameter('user', $user)
            ->setParameter('debutAnnee', $debutAnnee)
            ->setParameter('finAnnee', $finAnnee)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les rapports par zone pour un utilisateur donné
     */
    public function getRapportsParZonePourUtilisateur(User $user): array
    {
        // Récupérer tous les rapports avec zone non vide
        $rapports = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.zone IS NOT NULL')
            ->andWhere('r.zone != :empty')
            ->setParameter('user', $user)
            ->setParameter('empty', '')
            ->getQuery()
            ->getResult();

        // Compter les rapports par zone
        $rapportsParZone = [];
        foreach ($rapports as $rapport) {
            $zone = $rapport->getZone();
            if (!isset($rapportsParZone[$zone])) {
                $rapportsParZone[$zone] = 0;
            }
            $rapportsParZone[$zone]++;
        }

        // Trier par nombre décroissant
        arsort($rapportsParZone);

        return $rapportsParZone;
    }

    /**
     * Recherche de rapports spécifique pour un utilisateur
     */
    public function searchRapportsForUser(array $searchParams, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->setParameter('user', $searchParams['user']);

        // Recherche par zone
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone LIKE :zone')
                ->setParameter('zone', '%' . $searchParams['zone'] . '%');
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
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.user = :user')
            ->setParameter('user', $searchParams['user']);

        // Mêmes conditions que pour la recherche
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('r.zone LIKE :zone')
                ->setParameter('zone', '%' . $searchParams['zone'] . '%');
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
}
