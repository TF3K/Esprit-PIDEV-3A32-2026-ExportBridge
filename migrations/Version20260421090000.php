<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260421090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Safely add partnership_id to certificates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'certificates' AND column_name = 'partnership_id')");
        $this->addSql("SET @sql := IF(@col = 0, 'ALTER TABLE certificates ADD partnership_id INT DEFAULT NULL', 'SELECT 1')");
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql("SET @idx := (SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'certificates' AND index_name = 'IDX_CERTIFICATES_PARTNERSHIP')");
        $this->addSql("SET @sql := IF(@idx = 0, 'CREATE INDEX IDX_CERTIFICATES_PARTNERSHIP ON certificates (partnership_id)', 'SELECT 1')");
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        $this->addSql("SET @fk := (SELECT COUNT(1) FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = 'certificates' AND constraint_name = 'FK_CERTIFICATES_PARTNERSHIP')");
        $this->addSql("SET @sql := IF(@fk = 0, 'ALTER TABLE certificates ADD CONSTRAINT FK_CERTIFICATES_PARTNERSHIP FOREIGN KEY (partnership_id) REFERENCES partnerships (id) ON DELETE SET NULL', 'SELECT 1')");
        $this->addSql('PREPARE stmt FROM @sql');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');
    }

    public function down(Schema $schema): void
    {
        // Safe no-op: keeping partnership_id avoids deleting existing links.
    }
}
