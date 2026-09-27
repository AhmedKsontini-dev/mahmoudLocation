<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Correctif : le moteur de stockage par défaut du serveur MySQL est MyISAM,
 * qui ignore silencieusement les clés étrangères. Les tables contact et
 * contact_message sont converties en InnoDB afin que la contrainte de clé
 * étrangère avec ON DELETE CASCADE soit réellement appliquée.
 */
final class Version20260925202015 extends AbstractMigration
{
    private const FOREIGN_KEY_NAME = 'FK_2C9211FEE7A1254A';

    public function getDescription(): string
    {
        return 'Conversion de contact et contact_message en InnoDB et activation de la clé étrangère avec cascade.';
    }

    public function up(Schema $schema): void
    {
        // Supprime les éventuelles lignes orphelines créées lorsque la clé
        // étrangère n'était pas effective (tables MyISAM).
        $this->addSql('DELETE cm FROM contact_message cm LEFT JOIN contact c ON cm.contact_id = c.id WHERE c.id IS NULL');

        $this->addSql('ALTER TABLE contact ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact_message ENGINE = InnoDB');
        $this->addSql(sprintf(
            'ALTER TABLE contact_message ADD CONSTRAINT %s FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE',
            self::FOREIGN_KEY_NAME
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql(sprintf('ALTER TABLE contact_message DROP FOREIGN KEY %s', self::FOREIGN_KEY_NAME));
        $this->addSql('ALTER TABLE contact_message ENGINE = MyISAM');
        $this->addSql('ALTER TABLE contact ENGINE = MyISAM');
    }
}
