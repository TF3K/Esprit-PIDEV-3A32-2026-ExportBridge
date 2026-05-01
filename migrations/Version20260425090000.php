<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Allow multiple partnerships for the same company by removing the unique index on source_company_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("SET @idx := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND index_name = 'IDX_PARTNERSHIPS_SOURCE_COMPANY_MULTI')");
        $this->addSql("SET @sql := IF(@idx = 0, 'CREATE INDEX IDX_PARTNERSHIPS_SOURCE_COMPANY_MULTI ON partnerships (source_company_id)', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");

        $this->addSql("SET @uniq := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND index_name = 'UNIQ_2AE65832EB2F6BF2')");
        $this->addSql("SET @sql := IF(@uniq > 0, 'ALTER TABLE partnerships DROP INDEX UNIQ_2AE65832EB2F6BF2', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");
    }

    public function down(Schema $schema): void
    {
        // The previous unique constraint is intentionally not restored to avoid breaking existing multiple partnerships.
    }
}
