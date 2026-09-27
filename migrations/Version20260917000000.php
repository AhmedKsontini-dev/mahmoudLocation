<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260917000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add configurable frontend currency rates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE currency_rate (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(3) NOT NULL, symbol VARCHAR(10) NOT NULL, rate NUMERIC(12, 6) NOT NULL, UNIQUE INDEX UNIQ_CURRENCY_RATE_CODE (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO currency_rate (code, symbol, rate) VALUES ('TND', 'DT', 1.000000), ('EUR', '€', 0.300000), ('USD', '$', 0.350000)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE currency_rate');
    }
}