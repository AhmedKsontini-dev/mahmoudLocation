<?php

namespace App\Repository;

use App\Entity\Excursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Excursion>
 */
class ExcursionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Excursion::class);
    }

    public function save(Excursion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Excursion $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', Excursion::STATUS_ACTIVE)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Excursion
    {
        return $this->createQueryBuilder('e')
            ->where('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retrieves paginated excursions with optional filters.
     *
     * @param int   $page
     * @param int   $limit
     * @param array $filters Keys: destination (int), duration_minutes (int), min_price (float), max_price (float)
     */
    public function getPaginatedExcursions(int $page, int $limit, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'published'])
            ->orderBy('e.id', 'DESC');

        // --- Filtre destination (ID) ---
        if (!empty($filters['destination'])) {
            $qb->andWhere('e.destination = :destination')
               ->setParameter('destination', (int) $filters['destination']);
        }

        // --- Filtre durée totale en minutes ---
        if (isset($filters['duration_minutes']) && $filters['duration_minutes'] !== '' && $filters['duration_minutes'] !== null) {
            $qb->andWhere('(COALESCE(e.durationHours, 0) * 60 + COALESCE(e.durationMinutes, 0)) = :dur_min')
               ->setParameter('dur_min', (int) $filters['duration_minutes']);
        }

        // --- Filtre prix minimum ---
        if (isset($filters['min_price']) && $filters['min_price'] !== '' && $filters['min_price'] !== null) {
            $qb->andWhere('e.priceAdult >= :min_price')
               ->setParameter('min_price', (float) $filters['min_price']);
        }

        // --- Filtre prix maximum ---
        if (isset($filters['max_price']) && $filters['max_price'] !== '' && $filters['max_price'] !== null) {
            $qb->andWhere('e.priceAdult <= :max_price')
               ->setParameter('max_price', (float) $filters['max_price']);
        }

        $qb->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb->getQuery());
    }

    /**
     * Returns Destination entities that have at least one active/published excursion.
     *
     * @return \App\Entity\Destination[]
     */
    public function findDestinationsUsedByActiveExcursions(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('d')
            ->from(\App\Entity\Destination::class, 'd')
            ->join('d.excursions', 'e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'published'])
            ->groupBy('d.id')
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns distinct durations from active excursions.
     * Each row: ['hours' => int|null, 'minutes' => int|null, 'total_minutes' => int]
     *
     * @return array<array{hours: int|null, minutes: int|null, total_minutes: int}>
     */
    public function findDistinctDurations(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select(
                'e.durationHours AS hours',
                'e.durationMinutes AS minutes',
                '(COALESCE(e.durationHours, 0) * 60 + COALESCE(e.durationMinutes, 0)) AS total_minutes'
            )
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'published'])
            ->andWhere('e.durationHours IS NOT NULL OR e.durationMinutes IS NOT NULL')
            ->groupBy('e.durationHours', 'e.durationMinutes')
            ->orderBy('total_minutes', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Deduplicate by total_minutes
        $seen   = [];
        $unique = [];
        foreach ($rows as $row) {
            $key = (int) $row['total_minutes'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[]   = $row;
            }
        }

        return $unique;
    }

    /**
     * Returns [min, max] of priceAdult for active/published excursions.
     *
     * @return array{min: float, max: float}
     */
    public function findPriceRange(): array
    {
        $result = $this->createQueryBuilder('e')
            ->select('MIN(e.priceAdult) AS min_price, MAX(e.priceAdult) AS max_price')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'published'])
            ->getQuery()
            ->getSingleResult();

        return [
            'min' => (float) ($result['min_price'] ?? 0),
            'max' => (float) ($result['max_price'] ?? 1000),
        ];
    }
}

