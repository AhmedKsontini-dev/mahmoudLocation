<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

final class Version20261017162200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add contact read status and allow standalone sent messages in the mailbox.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact ADD is_read TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE contact_message MODIFY contact_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_message ADD recipient_name VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $standaloneMessages = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM contact_message WHERE contact_id IS NULL');

        if ($standaloneMessages > 0) {
            throw new IrreversibleMigration('Standalone sent messages exist and cannot be linked to a contact without data loss.');
        }

        $this->addSql('ALTER TABLE contact_message DROP recipient_name');
        $this->addSql('ALTER TABLE contact_message MODIFY contact_id INT NOT NULL');
        $this->addSql('ALTER TABLE contact DROP is_read');
    }
}
