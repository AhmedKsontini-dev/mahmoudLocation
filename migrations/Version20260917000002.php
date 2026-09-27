<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align reservation and currency indexes with Doctrine metadata';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE currency_rate RENAME INDEX uniq_currency_rate_code TO UNIQ_555B7C4D77153098');
        $this->addSql('ALTER TABLE reservation_transfere CHANGE transfere_type transfere_type VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reservation_transfere RENAME INDEX idx_reservation_transfere_transfer TO IDX_6EB9EDD6D4397B57');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE currency_rate RENAME INDEX UNIQ_555B7C4D77153098 TO uniq_currency_rate_code');
        $this->addSql('ALTER TABLE reservation_transfere CHANGE transfere_type transfere_type VARCHAR(7) NOT NULL, CHANGE status status VARCHAR(9) NOT NULL');
        $this->addSql('ALTER TABLE reservation_transfere RENAME INDEX IDX_6EB9EDD6D4397B57 TO idx_reservation_transfere_transfer');
    }
}