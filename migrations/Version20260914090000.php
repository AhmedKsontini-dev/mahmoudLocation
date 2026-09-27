<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260914090000 extends AbstractMigration
{
    public function getDescription(): string { return 'Create transferes with unique slugs'; }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE transfere (id INT AUTO_INCREMENT NOT NULL, lieu_depart VARCHAR(255) NOT NULL, lieu_arrivee VARCHAR(255) NOT NULL, prix NUMERIC(10, 2) NOT NULL, image VARCHAR(255) DEFAULT NULL, duree VARCHAR(100) NOT NULL, actif TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_TRANSFERE_SLUG (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void { $this->addSql('DROP TABLE transfere'); }
}