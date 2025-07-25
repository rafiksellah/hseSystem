<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }



    /**
     * Recherche des utilisateurs (toutes zones)
     */
    public function searchUsers(string $search, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search OR u.zone LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('u.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les utilisateurs trouvés (toutes zones)
     */
    public function countSearchUsers(string $search): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search OR u.zone LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Recherche des utilisateurs dans une zone spécifique
     */
    public function searchUsersByZone(string $search, string $zone, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.zone = :zone')
            ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search')
            ->setParameter('zone', $zone)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('u.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les utilisateurs trouvés dans une zone spécifique
     */
    public function countSearchUsersByZone(string $search, string $zone): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.zone = :zone')
            ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search')
            ->setParameter('zone', $zone)
            ->setParameter('search', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les utilisateurs par zone
     */
    public function findByZone(string $zone): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.zone = :zone')
            ->setParameter('zone', $zone)
            ->orderBy('u.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques par zone
     */
    public function getStatsByZone(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.zone, COUNT(u.id) as total')
            ->groupBy('u.zone')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les admins par zone
     */
    public function findAdminsByZone(string $zone): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.zone = :zone')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('zone', $zone)
            ->setParameter('role', '%ROLE_ADMIN%')
            ->orderBy('u.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les utilisateurs selon la zone de l'admin connecté
     */
    public function findByZoneForAdmin(?User $currentUser, ?string $search = null, int $page = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.dateCreation', 'DESC');

        // Si l'utilisateur connecté est un admin (pas super admin), filtrer par zone
        if (
            $currentUser &&
            in_array('ROLE_ADMIN', $currentUser->getRoles()) &&
            !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())
        ) {
            $qb->andWhere('u.zone = :zone')
                ->setParameter('zone', $currentUser->getZone());
        }

        // Ajouter la recherche si fournie
        if ($search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(u.nom)', ':search'),
                $qb->expr()->like('LOWER(u.prenom)', ':search'),
                $qb->expr()->like('LOWER(u.email)', ':search'),
                $qb->expr()->like('LOWER(u.codeAgent)', ':search')
            ))
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        // Pagination
        $qb->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compte le nombre total d'utilisateurs pour la pagination
     */
    public function countByZoneForAdmin(?User $currentUser, ?string $search = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        // Si l'utilisateur connecté est un admin (pas super admin), filtrer par zone
        if (
            $currentUser &&
            in_array('ROLE_ADMIN', $currentUser->getRoles()) &&
            !in_array('ROLE_SUPER_ADMIN', $currentUser->getRoles())
        ) {
            $qb->andWhere('u.zone = :zone')
                ->setParameter('zone', $currentUser->getZone());
        }

        // Ajouter la recherche si fournie
        if ($search) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(u.nom)', ':search'),
                $qb->expr()->like('LOWER(u.prenom)', ':search'),
                $qb->expr()->like('LOWER(u.email)', ':search'),
                $qb->expr()->like('LOWER(u.codeAgent)', ':search')
            ))
                ->setParameter('search', '%' . strtolower($search) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Trouve les utilisateurs de la même zone (pour les rapports HSE)
     */
    // public function findByZone(string $zone): array
    // {
    //     return $this->createQueryBuilder('u')
    //         ->andWhere('u.zone = :zone')
    //         ->andWhere('u.roles NOT LIKE :admin')
    //         ->setParameter('zone', $zone)
    //         ->setParameter('admin', '%ROLE_ADMIN%')
    //         ->orderBy('u.nom', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }

    /**
     * Trouve un utilisateur par son code agent dans une zone spécifique
     */
    public function findByCodeAgentAndZone(string $codeAgent, string $zone): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.codeAgent = :codeAgent')
            ->andWhere('u.zone = :zone')
            ->setParameter('codeAgent', $codeAgent)
            ->setParameter('zone', $zone)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Vérifie si un code agent existe déjà dans une zone donnée
     */
    public function isCodeAgentExistsInZone(string $codeAgent, string $zone, ?int $excludeUserId = null): bool
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.codeAgent = :codeAgent')
            ->andWhere('u.zone = :zone')
            ->setParameter('codeAgent', $codeAgent)
            ->setParameter('zone', $zone);

        if ($excludeUserId) {
            $qb->andWhere('u.id != :excludeId')
                ->setParameter('excludeId', $excludeUserId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }


    /**
     * Obtient la répartition des utilisateurs par zone
     */
    public function getUsersParZone(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.zone, COUNT(u.id) as total')
            ->where('u.zone IS NOT NULL')
            ->groupBy('u.zone')
            ->orderBy('total', 'DESC');

        $results = $qb->getQuery()->getResult();

        $data = [];
        foreach ($results as $result) {
            $data[$result['zone']] = $result['total'];
        }

        return $data;
    }

    /**
     * Trouve les utilisateurs par rôle
     */
    public function findUsersByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche avancée pour super admin
     */
    public function searchUsersAdvanced(array $searchParams, int $limit, int $offset): array
    {
        $qb = $this->createQueryBuilder('u');

        // Recherche par nom/prénom/email/code agent
        if (!empty($searchParams['search'])) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search')
                ->setParameter('search', '%' . $searchParams['search'] . '%');
        }

        // Filtre par zone
        if (!empty($searchParams['zone'])) {
            $qb->andWhere('u.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        // Filtre par rôle
        if (!empty($searchParams['role'])) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"' . $searchParams['role'] . '"%');
        }

        return $qb->orderBy('u.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte pour la recherche avancée
     */
    public function countSearchUsersAdvanced(array $searchParams): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)');

        // Mêmes conditions que la recherche
        if (!empty($searchParams['search'])) {
            $qb->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.email LIKE :search OR u.codeAgent LIKE :search')
                ->setParameter('search', '%' . $searchParams['search'] . '%');
        }

        if (!empty($searchParams['zone'])) {
            $qb->andWhere('u.zone = :zone')
                ->setParameter('zone', $searchParams['zone']);
        }

        if (!empty($searchParams['role'])) {
            $qb->andWhere('u.roles LIKE :role')
                ->setParameter('role', '%"' . $searchParams['role'] . '"%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Évolution des utilisateurs par mois - Version compatible
     */
    public function getEvolutionUtilisateurs(int $mois = 12): array
    {
        $dateDebut = new \DateTime('-' . $mois . ' months');

        // Récupérer tous les utilisateurs dans la période
        $qb = $this->createQueryBuilder('u')
            ->where('u.dateCreation >= :dateDebut')
            ->setParameter('dateDebut', $dateDebut)
            ->orderBy('u.dateCreation', 'ASC');

        $users = $qb->getQuery()->getResult();

        // Grouper par mois en PHP
        $data = [];
        foreach ($users as $user) {
            $periode = $user->getDateCreation()->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $data[$periode]++;
        }

        // S'assurer d'avoir tous les mois même avec 0 utilisateur
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
     * Statistiques globales des utilisateurs
     */
    public function getGlobalStats(): array
    {
        $qb = $this->createQueryBuilder('u');

        return [
            'total' => $this->count([]),
            'par_zone' => $this->getUsersParZone(),
            'admins' => count($this->findUsersByRole('ROLE_ADMIN')),
            'super_admins' => count($this->findUsersByRole('ROLE_SUPER_ADMIN')),
            'utilisateurs_simples' => $this->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.roles NOT LIKE :admin AND u.roles NOT LIKE :super_admin')
                ->setParameter('admin', '%ROLE_ADMIN%')
                ->setParameter('super_admin', '%ROLE_SUPER_ADMIN%')
                ->getQuery()
                ->getSingleScalarResult(),
            'derniers_inscrits' => $this->findBy([], ['dateCreation' => 'DESC'], 5)
        ];
    }

    /**
     * Statistiques des utilisateurs pour une zone spécifique
     */
    public function getStatsParZone(string $zone): array
    {
        return [
            'total' => $this->count(['zone' => $zone]),
            'actifs' => $this->getUtilisateursActifs($zone),
            'recent' => $this->findBy(['zone' => $zone], ['dateCreation' => 'DESC'], 5)
        ];
    }

    /**
     * Utilisateurs actifs (ayant créé au moins un rapport)
     */
    public function getUtilisateursActifs(string $zone): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(DISTINCT u.id)')
            ->leftJoin('u.rapportsHSE', 'r')
            ->where('u.zone = :zone')
            ->andWhere('r.id IS NOT NULL')
            ->setParameter('zone', $zone);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Évolution des utilisateurs par mois pour une zone
     */
    public function getEvolutionUtilisateursZone(string $zone, int $mois = 12): array
    {
        $dateDebut = new \DateTime('-' . $mois . ' months');

        $qb = $this->createQueryBuilder('u')
            ->where('u.dateCreation >= :dateDebut')
            ->andWhere('u.zone = :zone')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('zone', $zone)
            ->orderBy('u.dateCreation', 'ASC');

        $users = $qb->getQuery()->getResult();

        $data = [];
        foreach ($users as $user) {
            $periode = $user->getDateCreation()->format('Y-m');
            if (!isset($data[$periode])) {
                $data[$periode] = 0;
            }
            $data[$periode]++;
        }

        // S'assurer d'avoir tous les mois
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
     * Top utilisateurs les plus actifs dans une zone
     */
    public function getTopUtilisateursActifs(string $zone, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.nom, u.prenom, u.codeAgent, COUNT(r.id) as total_rapports')
            ->leftJoin('u.rapportsHSE', 'r')
            ->where('u.zone = :zone')
            ->setParameter('zone', $zone)
            ->groupBy('u.id')
            ->having('total_rapports > 0')
            ->orderBy('total_rapports', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}