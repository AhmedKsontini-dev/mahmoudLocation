<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add multilingual translations for transfers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE transfere_translation (id INT AUTO_INCREMENT NOT NULL, transfere_id INT NOT NULL, locale VARCHAR(5) NOT NULL, lieu_depart VARCHAR(255) DEFAULT NULL, lieu_arrivee VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_TRANSFERE_TRANSLATION_LOCALE (transfere_id, locale), INDEX IDX_TRANSFERE_TRANSLATION_TRANSFERE (transfere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transfere_translation ADD CONSTRAINT FK_TRANSFERE_TRANSLATION_TRANSFERE FOREIGN KEY (transfere_id) REFERENCES transfere (id) ON DELETE CASCADE');
        $this->addSql("INSERT INTO transfere_translation (transfere_id, locale, lieu_depart, lieu_arrivee) SELECT id, 'fr', lieu_depart, lieu_arrivee FROM transfere");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE transfere_translation');
    }
}
