<?php

namespace App\Repository;

use App\Entity\ReservationExcursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservationExcursion>
 */
class ReservationExcursionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservationExcursion::class);
    }

    /**
     * Recherche, filtrage et pagination côté serveur des réservations d'excursions.
     *
     * @return array{items: ReservationExcursion[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginatedReservations(
        ?string $search = null,
        ?string $statut = null,
        ?int $excursionId = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
        int $page = 1,
        int $limit = 1
    ): array {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.excursion', 'e')
            ->addSelect('e')
            ->leftJoin('e.translations', 'et')
            ->addSelect('et')
            ->leftJoin('e.images', 'ei')
            ->addSelect('ei');

        // Recherche multi-critères : ID, nom, prénom, email, WhatsApp, nom de l'excursion
        if ($search !== null && trim($search) !== '') {
            $searchTrimmed = trim($search);
            $searchLower = mb_strtolower($searchTrimmed);

            $orX = $qb->expr()->orX(
                'LOWER(r.nom) LIKE :searchLike',
                'LOWER(r.prenom) LIKE :searchLike',
                'LOWER(r.email) LIKE :searchLike',
                'r.whatsapp LIKE :searchRaw',
                'LOWER(et.title) LIKE :searchLike',
                'LOWER(e.slug) LIKE :searchLike'
            );

            if (is_numeric($searchTrimmed)) {
                $orX->add('r.id = :searchId');
                $qb->setParameter('searchId', (int) $searchTrimmed);
            }

            $qb->andWhere($orX)
                ->setParameter('searchLike', '%' . $searchLower . '%')
                ->setParameter('searchRaw', '%' . $searchTrimmed . '%');
        }

        // Filtre par statut
        if ($statut !== null && trim($statut) !== '') {
            $statutTrimmed = trim($statut);
            if (in_array($statutTrimmed, [
                ReservationExcursion::STATUS_PENDING,
                ReservationExcursion::STATUS_CONFIRMED,
                ReservationExcursion::STATUS_CANCELLED,
            ], true)) {
                $qb->andWhere('r.statut = :statut')
                    ->setParameter('statut', $statutTrimmed);
            }
        }

        // Filtre par excursion
        if ($excursionId !== null && $excursionId > 0) {
            $qb->andWhere('e.id = :excursionId')
                ->setParameter('excursionId', $excursionId);
        }

        // Filtre par date début
        if ($dateFrom !== null) {
            $qb->andWhere('r.dateExcursion >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom->format('Y-m-d'));
        }

        // Filtre par date fin
        if ($dateTo !== null) {
            $qb->andWhere('r.dateExcursion <= :dateTo')
                ->setParameter('dateTo', $dateTo->format('Y-m-d'));
        }

        // Calcul du nombre total d'enregistrements distincts
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
     * Compte le nombre de réservations d'excursions par statut.
     */
    public function countByStatut(string $statut): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Statistiques globales des réservations d'excursions.
     *
     * @return array{total: int, pending: int, confirmed: int, cancelled: int}
     */
    public function getStats(): array
    {
        return [
            'total' => (int) $this->count([]),
            'pending' => $this->countByStatut(ReservationExcursion::STATUS_PENDING),
            'confirmed' => $this->countByStatut(ReservationExcursion::STATUS_CONFIRMED),
            'cancelled' => $this->countByStatut(ReservationExcursion::STATUS_CANCELLED),
        ];
    }
}
