<?php

namespace App\Repository;

use App\Entity\ReservationTransfere;
use App\Enum\ReservationStatus;
use App\Enum\ReservationTransfereType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationTransfere>
 */
class ReservationTransfereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationTransfere::class);
    }

    /**
     * Recherche et pagination des réservations de transferts côté serveur via QueryBuilder.
     *
     * @return array{items: ReservationTransfere[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginatedReservations(?string $search = null, ?string $status = null, ?string $type = null, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.transfere', 't')
            ->addSelect('t');

        // Recherche textuelle multi-critères
        if ($search !== null && trim($search) !== '') {
            $searchTrimmed = trim($search);
            $searchLower = mb_strtolower($searchTrimmed);

            if (is_numeric($searchTrimmed)) {
                $qb->andWhere('r.id = :searchId OR LOWER(r.nom) LIKE :searchLike OR LOWER(r.prenom) LIKE :searchLike OR LOWER(r.email) LIKE :searchLike OR r.telephone LIKE :searchRaw')
                    ->setParameter('searchId', (int) $searchTrimmed)
                    ->setParameter('searchLike', '%' . $searchLower . '%')
                    ->setParameter('searchRaw', '%' . $searchTrimmed . '%');
            } else {
                $qb->andWhere('LOWER(r.nom) LIKE :searchLike OR LOWER(r.prenom) LIKE :searchLike OR LOWER(r.email) LIKE :searchLike OR r.telephone LIKE :searchRaw OR LOWER(r.pickupLocation) LIKE :searchLike OR LOWER(r.dropoffLocation) LIKE :searchLike')
                    ->setParameter('searchLike', '%' . $searchLower . '%')
                    ->setParameter('searchRaw', '%' . $searchTrimmed . '%');
            }
        }

        // Filtre par statut
        if ($status !== null && trim($status) !== '') {
            $statusEnum = ReservationStatus::tryFrom(trim($status));
            if ($statusEnum !== null) {
                $qb->andWhere('r.status = :status')
                    ->setParameter('status', $statusEnum);
            }
        }

        // Filtre par type de trajet
        if ($type !== null && trim($type) !== '') {
            $typeEnum = ReservationTransfereType::tryFrom(trim($type));
            if ($typeEnum !== null) {
                $qb->andWhere('r.transfereType = :transfereType')
                    ->setParameter('transfereType', $typeEnum);
            }
        }

        // Calcul du nombre total d'enregistrements correspondant aux filtres
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(DISTINCT r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = max(1, (int) ceil($total / $limit));
        if ($page > $pages && $total > 0) {
            $page = $pages;
        }

        $firstResult = ($page - 1) * $limit;

        $items = $qb->orderBy('r.createdAt', 'DESC')
            ->addOrderBy('r.id', 'DESC')
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
     * Compte le nombre de réservations par statut.
     */
    public function countByStatus(ReservationStatus $status): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}