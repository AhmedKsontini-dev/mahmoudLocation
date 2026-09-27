<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260925201421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_message (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(20) NOT NULL, recipient VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, sent_at DATETIME DEFAULT NULL, attachment_name VARCHAR(255) DEFAULT NULL, attachment_path VARCHAR(255) DEFAULT NULL, contact_id INT NOT NULL, INDEX IDX_2C9211FEE7A1254A (contact_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8');
        $this->addSql('ALTER TABLE contact_message ADD CONSTRAINT FK_2C9211FEE7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact ADD is_archived TINYINT DEFAULT 0 NOT NULL, ADD is_pinned TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact_message DROP FOREIGN KEY FK_2C9211FEE7A1254A');
        $this->addSql('DROP TABLE contact_message');
        $this->addSql('ALTER TABLE contact DROP is_archived, DROP is_pinned');
    }
}
