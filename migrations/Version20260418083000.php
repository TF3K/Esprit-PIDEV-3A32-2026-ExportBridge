<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260418083000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add partnership document URL and fix product decimal precision for quantity and unit price';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partnerships ADD document_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE products CHANGE quantity quantity NUMERIC(10, 2) DEFAULT NULL, CHANGE unit_price unit_price NUMERIC(10, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE partnerships DROP document_url');
        $this->addSql('ALTER TABLE products CHANGE quantity quantity DOUBLE PRECISION DEFAULT NULL, CHANGE unit_price unit_price DOUBLE PRECISION DEFAULT NULL');
    }
}
