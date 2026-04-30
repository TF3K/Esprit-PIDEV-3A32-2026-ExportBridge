<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260416223000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add image_path to products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products ADD image_path VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP image_path');
    }
}
