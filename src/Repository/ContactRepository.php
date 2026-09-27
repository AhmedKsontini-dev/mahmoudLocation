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
}
