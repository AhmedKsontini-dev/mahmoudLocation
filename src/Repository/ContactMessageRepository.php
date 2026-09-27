<?php

namespace App\Repository;

use App\Entity\Contact;
use App\Entity\ContactMessage;
use App\Enum\ContactMessageStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactMessage>
 */
class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    /**
     * Messages envoyés par l'administrateur, du plus récent au plus ancien.
     *
     * @return ContactMessage[]
     */
    public function findSent(): array
    {
        return $this->findBy(['status' => ContactMessageStatus::SENT], ['sentAt' => 'DESC']);
    }

    /**
     * Brouillons, du plus récemment modifié au plus ancien.
     *
     * @return ContactMessage[]
     */
    public function findDrafts(): array
    {
        return $this->findBy(['status' => ContactMessageStatus::DRAFT], ['updatedAt' => 'DESC']);
    }

    /**
     * Dernier brouillon enregistré pour un contact donné.
     */
    public function findLatestDraftForContact(Contact $contact): ?ContactMessage
    {
        return $this->findOneBy(
            ['contact' => $contact, 'status' => ContactMessageStatus::DRAFT],
            ['updatedAt' => 'DESC']
        );
    }
}
