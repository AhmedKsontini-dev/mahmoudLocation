<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Le formulaire d'excursion ne collecte plus l'icône des caractéristiques :
 * une caractéristique peut désormais être enregistrée sans icône. La colonne
 * excursion_feature.icon, jusqu'ici NOT NULL, est donc rendue nullable (NULL =
 * aucune icône). On en profite pour la convertir en utf8mb4 afin que les émojis
 * (🚗, ✈️, 🏖️ …) soient stockés intégralement, comme pour category.icon.
 * Modification non destructive : les icônes déjà enregistrées sont conservées.
 */
final class Version20261020000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make excursion_feature.icon nullable (and utf8mb4) so a feature can be saved without an icon.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excursion_feature CHANGE icon icon VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Les éventuelles lignes sans icône (NULL) sont ramenées à une icône vide
        // pour respecter la contrainte NOT NULL rétablie.
        $this->addSql('UPDATE excursion_feature SET icon = \'\' WHERE icon IS NULL');
        $this->addSql('ALTER TABLE excursion_feature CHANGE icon icon VARCHAR(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL');
    }
}
