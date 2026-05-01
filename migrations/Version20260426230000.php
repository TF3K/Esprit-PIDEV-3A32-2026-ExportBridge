<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add saved localisation fields to partnerships';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_name')");
        $this->addSql("SET @sql := IF(@col = 0, 'ALTER TABLE partnerships ADD location_name VARCHAR(255) DEFAULT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");

        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_latitude')");
        $this->addSql("SET @sql := IF(@col = 0, 'ALTER TABLE partnerships ADD location_latitude DOUBLE PRECISION DEFAULT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");

        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_longitude')");
        $this->addSql("SET @sql := IF(@col = 0, 'ALTER TABLE partnerships ADD location_longitude DOUBLE PRECISION DEFAULT NULL', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_name')");
        $this->addSql("SET @sql := IF(@col > 0, 'ALTER TABLE partnerships DROP COLUMN location_name', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");

        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_latitude')");
        $this->addSql("SET @sql := IF(@col > 0, 'ALTER TABLE partnerships DROP COLUMN location_latitude', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");

        $this->addSql("SET @col := (SELECT COUNT(1) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'partnerships' AND column_name = 'location_longitude')");
        $this->addSql("SET @sql := IF(@col > 0, 'ALTER TABLE partnerships DROP COLUMN location_longitude', 'SELECT 1')");
        $this->addSql("PREPARE stmt FROM @sql");
        $this->addSql("EXECUTE stmt");
        $this->addSql("DEALLOCATE PREPARE stmt");
    }
}
