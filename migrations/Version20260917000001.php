<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add transfer descriptions and transfer reservations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transfere ADD description LONGTEXT DEFAULT NULL');
        $this->addSql("CREATE TABLE reservation_transfere (id INT AUTO_INCREMENT NOT NULL, transfere_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, telephone VARCHAR(50) NOT NULL, transfere_type VARCHAR(7) NOT NULL, pickup_location VARCHAR(255) NOT NULL, dropoff_location VARCHAR(255) NOT NULL, pickup_date DATE NOT NULL, pickup_time TIME NOT NULL, return_pickup_location VARCHAR(255) DEFAULT NULL, return_dropoff_location VARCHAR(255) DEFAULT NULL, return_pickup_date DATE DEFAULT NULL, return_pickup_time TIME DEFAULT NULL, nombre_passagers INT NOT NULL, nombre_bagages INT NOT NULL, message LONGTEXT DEFAULT NULL, prix_total NUMERIC(12, 2) NOT NULL, devise VARCHAR(3) NOT NULL, status VARCHAR(9) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_RESERVATION_TRANSFERE_TRANSFER (transfere_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
        $this->addSql('ALTER TABLE reservation_transfere ADD CONSTRAINT FK_RESERVATION_TRANSFERE_TRANSFER FOREIGN KEY (transfere_id) REFERENCES transfere (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation_transfere DROP FOREIGN KEY FK_RESERVATION_TRANSFERE_TRANSFER');
        $this->addSql('DROP TABLE reservation_transfere');
        $this->addSql('ALTER TABLE transfere DROP description');
    }
}