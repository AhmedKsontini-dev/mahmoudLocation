<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916100002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make transfer duration translatable, add seasonal prices and remove image';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transfere ADD prix_normal NUMERIC(10, 2) DEFAULT NULL, ADD prix_saison_forte NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('UPDATE transfere SET prix_normal = prix, prix_saison_forte = prix');
        $this->addSql('ALTER TABLE transfere MODIFY prix_normal NUMERIC(10, 2) NOT NULL, MODIFY prix_saison_forte NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE transfere_translation ADD duree VARCHAR(100) DEFAULT NULL');
        $this->addSql("UPDATE transfere_translation translation INNER JOIN transfere transfer ON transfer.id = translation.transfere_id SET translation.duree = transfer.duree WHERE translation.locale = 'fr'");
        $this->addSql('ALTER TABLE transfere DROP prix, DROP image, DROP duree');
        $this->addSql('CREATE TABLE saison (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, actif TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transfere ADD prix NUMERIC(10, 2) DEFAULT NULL, ADD image VARCHAR(255) DEFAULT NULL, ADD duree VARCHAR(100) DEFAULT NULL');
        $this->addSql('UPDATE transfere SET prix = prix_normal, duree = (SELECT translation.duree FROM transfere_translation translation WHERE translation.transfere_id = transfere.id AND translation.locale = \'fr\' LIMIT 1)');
        $this->addSql('ALTER TABLE transfere MODIFY prix NUMERIC(10, 2) NOT NULL, MODIFY duree VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE transfere_translation DROP duree');
        $this->addSql('ALTER TABLE transfere DROP prix_normal, DROP prix_saison_forte');
        $this->addSql('DROP TABLE saison');
    }
}
