<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261031085843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add profile details and nullable reservation ownership links, converting legacy MyISAM tables to InnoDB.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user` ENGINE=InnoDB');
        $this->addSql('ALTER TABLE reservation ENGINE=InnoDB');
        $this->addSql('ALTER TABLE reservation_transfere ENGINE=InnoDB');
        $this->addSql('ALTER TABLE reservation ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_42C84955A76ED395 ON reservation (user_id)');
        $this->addSql('ALTER TABLE reservation_excursion ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation_excursion ADD CONSTRAINT FK_814F86B0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_814F86B0A76ED395 ON reservation_excursion (user_id)');
        $this->addSql('ALTER TABLE reservation_transfere ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reservation_transfere ADD CONSTRAINT FK_6EB9EDD6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_6EB9EDD6A76ED395 ON reservation_transfere (user_id)');
        $this->addSql('ALTER TABLE `user` ADD first_name VARCHAR(100) DEFAULT NULL, ADD last_name VARCHAR(100) DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955A76ED395');
        $this->addSql('DROP INDEX IDX_42C84955A76ED395 ON reservation');
        $this->addSql('ALTER TABLE reservation DROP user_id');
        $this->addSql('ALTER TABLE reservation_excursion DROP FOREIGN KEY FK_814F86B0A76ED395');
        $this->addSql('DROP INDEX IDX_814F86B0A76ED395 ON reservation_excursion');
        $this->addSql('ALTER TABLE reservation_excursion DROP user_id');
        $this->addSql('ALTER TABLE reservation_transfere DROP FOREIGN KEY FK_6EB9EDD6A76ED395');
        $this->addSql('DROP INDEX IDX_6EB9EDD6A76ED395 ON reservation_transfere');
        $this->addSql('ALTER TABLE reservation_transfere DROP user_id');
        $this->addSql('ALTER TABLE `user` DROP first_name, DROP last_name, DROP phone');
    }
}
