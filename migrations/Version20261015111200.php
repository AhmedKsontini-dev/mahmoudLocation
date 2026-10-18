<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261015111200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enforce the excursion reservation foreign key with InnoDB';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excursion ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_excursion ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation_excursion ADD CONSTRAINT FK_814F86B04AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id) ON DELETE RESTRICT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation_excursion DROP FOREIGN KEY FK_814F86B04AB4296F');
    }
}
