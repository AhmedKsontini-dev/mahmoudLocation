<?php

namespace App\Repository;

use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    /**
     * Messages reçus non archivés (boîte de réception), épinglés en premier.
     *
     * @return Contact[]
     */
    public function findInbox(): array
    {
        return $this->findBy(['isArchived' => false], ['isPinned' => 'DESC', 'createdAt' => 'DESC']);
    }

    /**
     * Messages reçus archivés, épinglés en premier.
     *
     * @return Contact[]
     */
    public function findArchived(): array
    {
        return $this->findBy(['isArchived' => true], ['isPinned' => 'DESC', 'createdAt' => 'DESC']);
    }

    /**
     * Messages reçus épinglés (archivés ou non).
     *
     * @return Contact[]
     */
    public function findPinned(): array
    {
        return $this->findBy(['isPinned' => true], ['createdAt' => 'DESC']);
    }

    /**
     * Recent recipient addresses for the compose form autocomplete.
     *
     * @return list<string>
     */
    public function findRecentRecipientAddresses(int $limit = 30): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.email, MAX(c.createdAt) AS HIDDEN latest')
            ->groupBy('c.email')
            ->orderBy('latest', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();

        return array_values(array_map(static fn (array $row): string => (string) $row['email'], $rows));
    }

    public function countUnread(): int
    {
        return $this->count(['isArchived' => false, 'isRead' => false]);
    }
}
