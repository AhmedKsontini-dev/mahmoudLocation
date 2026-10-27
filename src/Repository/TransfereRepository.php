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
            if ($location !== null && trim($location) !== '') {
                $label = preg_replace('/\s+/u', ' ', trim($location)) ?? trim($location);
                $locations[$this->normalizeDepartureLocation($label)] ??= $label;
            }
        }

        return array_values($locations);
    }

    /**
     * @return string[]
     */
    public function findDistinctDestinationsByDeparture(string $departure, string $locale = 'fr'): array
    {
        $normalizedDeparture = $this->normalizeDepartureLocation($departure);
        if ($normalizedDeparture === '') {
            return [];
        }

        $destinations = [];

        foreach ($this->findAvailableTransfers() as $transfer) {
            $pickupLocation = $transfer->getLieuDepartTranslated($locale) ?? $transfer->getLieuDepart();
            if ($pickupLocation === null || $this->normalizeDepartureLocation($pickupLocation) !== $normalizedDeparture) {
                continue;
            }

            $dropoffLocation = $transfer->getLieuArriveeTranslated($locale) ?? $transfer->getLieuArrivee();
            if ($dropoffLocation === null || trim($dropoffLocation) === '') {
                continue;
            }

            $label = preg_replace('/\s+/u', ' ', trim($dropoffLocation)) ?? trim($dropoffLocation);
            $destinations[$this->normalizeLocation($label)] ??= $label;
        }

        return array_values($destinations);
    }

    public function findAvailableTransferByLocations(string $departure, string $destination, string $locale = 'fr'): ?Transfere
    {
        $normalizedDeparture = $this->normalizeDepartureLocation($departure);
        $normalizedDestination = $this->normalizeLocation($destination);
        if ($normalizedDeparture === '' || $normalizedDestination === '') {
            return null;
        }

        foreach ($this->findAvailableTransfers() as $transfer) {
            $transferDeparture = $transfer->getLieuDepartTranslated($locale) ?? $transfer->getLieuDepart();
            $transferDestination = $transfer->getLieuArriveeTranslated($locale) ?? $transfer->getLieuArrivee();
            if ($transferDeparture !== null
                && $transferDestination !== null
                && $this->normalizeDepartureLocation($transferDeparture) === $normalizedDeparture
                && $this->normalizeLocation($transferDestination) === $normalizedDestination
            ) {
                return $transfer;
            }
        }

        return null;
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

    private function normalizeLocation(string $location): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim($location)) ?? trim($location);

        return mb_strtolower($normalized);
    }

    private function normalizeDepartureLocation(string $location): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($location));
        $normalized = mb_strtolower($normalized === false ? trim($location) : $normalized);
        $normalized = str_replace(["'", '`'], '', $normalized);
        $normalized = preg_replace('/\b(?:aeroport|airport|aireport|aereport)\b/u', ' airport ', $normalized) ?? $normalized;
        $normalized = preg_replace('/[^a-z0-9]+/u', ' ', $normalized) ?? $normalized;

        return preg_replace('/\s+/u', ' ', trim($normalized)) ?? trim($normalized);
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