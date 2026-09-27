<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916100001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align the transfer translation foreign-key index name with Doctrine';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transfere_translation RENAME INDEX idx_transfere_translation_transfere TO IDX_992FF35BD4397B57');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transfere_translation RENAME INDEX IDX_992FF35BD4397B57 TO idx_transfere_translation_transfere');
    }
}
