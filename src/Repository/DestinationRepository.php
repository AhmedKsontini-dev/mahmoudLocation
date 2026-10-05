<?php

namespace App\Repository;

use App\Entity\Destination;
use App\Entity\Excursion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Destination>
 */
class DestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Destination::class);
    }

    public function save(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Destination $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<array{destination: Destination, excursionCount: int}>
     */
    public function findActiveWithPublicExcursionCounts(): array
    {
        $statuses = [Excursion::STATUS_ACTIVE, 'published'];
        $countRows = $this->getEntityManager()->createQueryBuilder()
            ->select('d.id AS destinationId', 'COUNT(e.id) AS excursionCount')
            ->from(Destination::class, 'd')
            ->innerJoin('d.excursions', 'e')
            ->where('d.isActive = :active')
            ->andWhere('e.status IN (:statuses)')
            ->setParameter('active', true)
            ->setParameter('statuses', $statuses)
            ->groupBy('d.id')
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        if ($countRows === []) {
            return [];
        }

        $destinationIds = [];
        $countsByDestination = [];
        foreach ($countRows as $row) {
            $destinationId = (int) $row['destinationId'];
            $destinationIds[] = $destinationId;
            $countsByDestination[$destinationId] = (int) $row['excursionCount'];
        }

        $destinations = $this->createQueryBuilder('d')
            ->leftJoin('d.translations', 'translation')
            ->addSelect('translation')
            ->where('d.id IN (:destinationIds)')
            ->setParameter('destinationIds', $destinationIds)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($destinations as $destination) {
            $destinationId = $destination->getId();
            $result[] = [
                'destination' => $destination,
                'excursionCount' => $countsByDestination[$destinationId],
            ];
        }

        return $result;
    }
}
