<?php

namespace App\Repository;

use App\Entity\Transfere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TransfereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transfere::class);
    }

    /**
     * @return Transfere[]
     */
    public function findAvailableTransfers(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.actif = :active')
            ->setParameter('active', true)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function findDepartureLocations(string $locale = 'fr'): array
    {
        $locations = [];

        foreach ($this->findAvailableTransfers() as $transfer) {
            $location = $transfer->getLieuDepartTranslated($locale) ?? $transfer->getLieuDepart();
            if ($location !== null && $location !== '') {
                $locations[$location] = $location;
            }
        }

        return array_values($locations);
    }

    /**
     * @return array<int, array{label: string, value: string, id: int, slug: string|null, departure: string, destination: string, duration: string, price: float}>
     */
    public function findDestinationsByDeparture(string $departure, string $locale = 'fr'): array
    {
        $departure = trim($departure);
        if ($departure === '') {
            return [];
        }

        $destinations = [];

        foreach ($this->findAvailableTransfers() as $transfer) {
            $pickupLocation = $transfer->getLieuDepartTranslated($locale) ?? $transfer->getLieuDepart();
            if ($pickupLocation !== $departure) {
                continue;
            }

            $dropoffLocation = $transfer->getLieuArriveeTranslated($locale) ?? $transfer->getLieuArrivee();
            if ($dropoffLocation === null || $dropoffLocation === '') {
                continue;
            }

            $destinations[$dropoffLocation] = [
                'label' => $dropoffLocation,
                'value' => $dropoffLocation,
                'id' => $transfer->getId(),
                'slug' => $transfer->getSlug(),
                'departure' => $departure,
                'destination' => $dropoffLocation,
                'duration' => $transfer->getDureeTranslated($locale) ?? '',
                'price' => (float) $transfer->getPrixNormal(),
            ];
        }

        return array_values($destinations);
    }

    /**
     * @return array<int, array{id: int|null, slug: string|null, departure: string, destination: string, duration: string, price: float, price_saison_forte: float}>
     */
    public function findBookingData(string $locale = 'fr'): array
    {
        $data = [];

        foreach ($this->findAvailableTransfers() as $transfer) {
            $departure = $transfer->getLieuDepartTranslated($locale) ?? $transfer->getLieuDepart();
            $destination = $transfer->getLieuArriveeTranslated($locale) ?? $transfer->getLieuArrivee();

            if ($departure === null || $departure === '' || $destination === null || $destination === '') {
                continue;
            }

            $data[] = [
                'id' => $transfer->getId(),
                'slug' => $transfer->getSlug(),
                'departure' => $departure,
                'destination' => $destination,
                'duration' => $transfer->getDureeTranslated($locale) ?? '',
                'price' => (float) $transfer->getPrixNormal(),
                'price_saison_forte' => (float) $transfer->getPrixSaisonForte(),
            ];
        }

        return $data;
    }

    /**
     * Recherche et pagination des transferts côté serveur via QueryBuilder et Doctrine Paginator.
     *
     * @return array{items: Transfere[], total: int, page: int, limit: int, pages: int}
     */
    public function findPaginatedTransfers(?string $search = null, ?string $status = null, int $page = 1, int $limit = 10): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);

        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.translations', 'tr');

        if ($search !== null && trim($search) !== '') {
            $searchTrimmed = trim($search);
            $searchLower = mb_strtolower($searchTrimmed);

            if (is_numeric($searchTrimmed)) {
                $qb->andWhere('t.id = :searchId OR LOWER(t.lieuDepart) LIKE :searchLike OR LOWER(t.lieuArrivee) LIKE :searchLike OR LOWER(tr.lieuDepart) LIKE :searchLike OR LOWER(tr.lieuArrivee) LIKE :searchLike OR LOWER(t.slug) LIKE :searchLike')
                    ->setParameter('searchId', (int) $searchTrimmed)
                    ->setParameter('searchLike', '%' . $searchLower . '%');
            } else {
                $qb->andWhere('LOWER(t.lieuDepart) LIKE :searchLike OR LOWER(t.lieuArrivee) LIKE :searchLike OR LOWER(tr.lieuDepart) LIKE :searchLike OR LOWER(tr.lieuArrivee) LIKE :searchLike OR LOWER(t.slug) LIKE :searchLike')
                    ->setParameter('searchLike', '%' . $searchLower . '%');
            }
        }

        if ($status !== null && trim($status) !== '') {
            $statusTrimmed = trim($status);
            if ($statusTrimmed === 'active' || $statusTrimmed === '1') {
                $qb->andWhere('t.actif = :statusActive')
                    ->setParameter('statusActive', true);
            } elseif ($statusTrimmed === 'inactive' || $statusTrimmed === '0') {
                $qb->andWhere('t.actif = :statusActive')
                    ->setParameter('statusActive', false);
            }
        }

        $qb->orderBy('t.id', 'ASC');

        // Utilisation de Paginator pour éviter les décalages dus aux OneToMany / traductions multiples
        $countPaginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery(), true);
        $total = count($countPaginator);

        $pages = max(1, (int) ceil($total / $limit));
        if ($page > $pages && $total > 0) {
            $page = $pages;
        }

        $firstResult = ($page - 1) * $limit;

        $qb->setFirstResult($firstResult)
           ->setMaxResults($limit);

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery(), true);
        $items = iterator_to_array($paginator);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => $pages,
        ];
    }

    /**
     * Compte le nombre total de transferts selon les critères de recherche et filtrage.
     */
    public function countFilteredTransfers(?string $search = null, ?string $status = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.translations', 'tr');

        if ($search !== null && trim($search) !== '') {
            $searchTrimmed = trim($search);
            $searchLower = mb_strtolower($searchTrimmed);

            if (is_numeric($searchTrimmed)) {
                $qb->andWhere('t.id = :searchId OR LOWER(t.lieuDepart) LIKE :searchLike OR LOWER(t.lieuArrivee) LIKE :searchLike OR LOWER(tr.lieuDepart) LIKE :searchLike OR LOWER(tr.lieuArrivee) LIKE :searchLike OR LOWER(t.slug) LIKE :searchLike')
                    ->setParameter('searchId', (int) $searchTrimmed)
                    ->setParameter('searchLike', '%' . $searchLower . '%');
            } else {
                $qb->andWhere('LOWER(t.lieuDepart) LIKE :searchLike OR LOWER(t.lieuArrivee) LIKE :searchLike OR LOWER(tr.lieuDepart) LIKE :searchLike OR LOWER(tr.lieuArrivee) LIKE :searchLike OR LOWER(t.slug) LIKE :searchLike')
                    ->setParameter('searchLike', '%' . $searchLower . '%');
            }
        }

        if ($status !== null && trim($status) !== '') {
            $statusTrimmed = trim($status);
            if ($statusTrimmed === 'active' || $statusTrimmed === '1') {
                $qb->andWhere('t.actif = :statusActive')
                    ->setParameter('statusActive', true);
            } elseif ($statusTrimmed === 'inactive' || $statusTrimmed === '0') {
                $qb->andWhere('t.actif = :statusActive')
                    ->setParameter('statusActive', false);
            }
        }

        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery(), true);
        return count($paginator);
    }
}