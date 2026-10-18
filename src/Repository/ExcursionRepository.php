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

    /**
     * @return Excursion[]
     */
    public function findAvailableExcursions(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', [Excursion::STATUS_ACTIVE, 'published'])
            ->andWhere('e.destination IS NOT NULL')
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Excursion[]
     */
    public function findLatestActive(int $limit = 4): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->setParameter('status', Excursion::STATUS_ACTIVE)
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function findExcursionDestinations(string $locale = 'fr'): array
    {
        $destinations = [];

        foreach ($this->findAvailableExcursions() as $excursion) {
            $destination = $excursion->getDestination();
            if ($destination === null) {
                continue;
            }

            $name = $destination->getNameTranslated($locale)
                ?? $destination->getTranslation('fr')?->getName();

            if ($name === null || $name === '') {
                continue;
            }

            $destinations[$destination->getId()] = [
                'id' => $destination->getId(),
                'name' => $name,
            ];
        }

        return array_values($destinations);
    }

    /**
     * @return array<int, array{id: int|null, title: string, destination: string, price: float, slug: string|null}>
     */
    public function findExcursionsByDestination(?int $destinationId, string $locale = 'fr'): array
    {
        if ($destinationId === null) {
            return [];
        }

        $excursions = $this->createQueryBuilder('e')
            ->where('e.destination = :destination')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('destination', $destinationId)
            ->setParameter('statuses', [Excursion::STATUS_ACTIVE, 'published'])
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($excursions as $excursion) {
            $title = $excursion->getTitleTranslated($locale)
                ?? $excursion->getTranslation('fr')?->getTitle()
                ?? $excursion->getSlug();

            $items[] = [
                'id' => $excursion->getId(),
                'title' => $title,
                'destination' => $excursion->getDestination()?->getNameTranslated($locale) ?? $excursion->getDestination()?->getTranslation('fr')?->getName() ?? '',
                'price' => (float) $excursion->getPriceAdult(),
                'slug' => $excursion->getSlug(),
            ];
        }

        return $items;
    }

    /**
     * @return array<int, array{destinationId: int, destination: string, excursions: array<int, array{id: int|null, title: string, price: float, slug: string|null}>}>
     */
    public function findBookingData(string $locale = 'fr'): array
    {
        $data = [];

        foreach ($this->findExcursionDestinations($locale) as $destination) {
            $destinationId = $destination['id'];
            $data[] = [
                'destinationId' => $destinationId,
                'destination' => $destination['name'],
                'excursions' => $this->findExcursionsByDestination($destinationId, $locale),
            ];
        }

        return $data;
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
     * @param array $filters Keys: destination (int), category (int), duration_minutes (int), min_price (float), max_price (float)
     */
    public function getPaginatedExcursions(int $page, int $limit, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.status IN (:statuses)')
            ->setParameter('statuses', ['active', 'published'])
            ->orderBy('e.id', 'DESC');

        // --- Filtre catégorie (ID) ---
        if (!empty($filters['category'])) {
            $qb->andWhere('e.category = :category')
               ->setParameter('category', (int) $filters['category']);
        }

        // --- Filtre destination (ID) ---
        if (!empty($filters['destination'])) {
            $qb->andWhere('e.destination = :destination')
               ->setParameter('destination', (int) $filters['destination']);
        }

        // --- Filtre excursion spécifique (slug ou ID) ---
        if (!empty($filters['excursion'])) {
            $excursionFilter = trim((string) $filters['excursion']);
            if ($excursionFilter !== '') {
                $qb->andWhere('(e.slug = :excursionSlug OR e.id = :excursionId)')
                   ->setParameter('excursionSlug', $excursionFilter)
                   ->setParameter('excursionId', is_numeric($excursionFilter) ? (int) $excursionFilter : 0);
            }
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
     * Returns Category entities that have at least one active/published excursion.
     * Reuses the same visibility rule as getPaginatedExcursions so the filter
     * only ever offers categories that can actually return results.
     *
     * @return \App\Entity\Category[]
     */
    public function findCategoriesUsedByActiveExcursions(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from(\App\Entity\Category::class, 'c')
            ->join('c.excursions', 'e')
            ->where('e.status IN (:statuses)')
            ->andWhere('c.isActive = :active')
            ->setParameter('statuses', ['active', 'published'])
            ->setParameter('active', true)
            ->groupBy('c.id')
            ->orderBy('c.position', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les excursions visibles (actives ou publiées) de chaque catégorie
     * active : COUNT + GROUP BY avec exactement les mêmes conditions de visibilité
     * que getPaginatedExcursions — les brouillons et inactifs ne sont jamais comptés.
     * COUNT(DISTINCT e.id) évite tout doublon si une même excursion était associée
     * plusieurs fois à une catégorie via les relations Doctrine.
     *
     * @return array<int, int> categoryId => nombre d'excursions publiées
     */
    public function countPublishedExcursionsByCategory(): array
    {
        $rows = $this->getEntityManager()->createQueryBuilder()
            ->select('c.id AS category_id', 'COUNT(DISTINCT e.id) AS total')
            ->from(\App\Entity\Category::class, 'c')
            ->innerJoin('c.excursions', 'e')
            ->where('e.status IN (:statuses)')
            ->andWhere('c.isActive = :active')
            ->setParameter('statuses', ['active', 'published'])
            ->setParameter('active', true)
            ->groupBy('c.id')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['category_id']] = (int) $row['total'];
        }

        return $counts;
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
