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
     * Recherche et pagination des utilisateurs côté serveur via QueryBuilder.
     *
     * @return array{items: User[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginatedUsers(?string $search = null, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('u');

        if ($search !== null && trim($search) !== '') {
            $searchTrimmed = trim($search);
            if (is_numeric($searchTrimmed)) {
                $qb->andWhere('u.id = :searchId OR LOWER(u.email) LIKE :search')
                   ->setParameter('searchId', (int) $searchTrimmed)
                   ->setParameter('search', '%' . mb_strtolower($searchTrimmed) . '%');
            } else {
                $qb->andWhere('LOWER(u.email) LIKE :search')
                   ->setParameter('search', '%' . mb_strtolower($searchTrimmed) . '%');
            }
        }

        // Requête de comptage total correspondant aux critères de recherche
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = max(1, (int) ceil($total / $limit));
        if ($page > $pages && $total > 0) {
            $page = $pages;
        }

        $firstResult = ($page - 1) * $limit;

        $items = $qb->select('u')
            ->orderBy('u.id', 'DESC')
            ->setFirstResult($firstResult)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
        ];
    }

    /**
     * Compte le nombre total d'utilisateurs.
     */
    public function countTotalUsers(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre d'utilisateurs ayant un rôle spécifique.
     */
    public function countByRole(string $role): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

