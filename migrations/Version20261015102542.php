<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015102542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create excursion booking requests table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE reservation_excursion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, whatsapp VARCHAR(50) NOT NULL, nombre_adultes INT NOT NULL, nombre_enfants INT NOT NULL, date_excursion DATE NOT NULL, heure_depart TIME NOT NULL, lieu_prise_en_charge LONGTEXT NOT NULL, statut VARCHAR(20) DEFAULT 'en_attente' NOT NULL, created_at DATETIME NOT NULL, excursion_id INT NOT NULL, INDEX IDX_814F86B04AB4296F (excursion_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8");
        $this->addSql('ALTER TABLE reservation_excursion ADD CONSTRAINT FK_814F86B04AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE reservation_excursion');
    }
}
