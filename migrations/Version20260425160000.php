<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260425160000 extends AbstractMigration
{
    protected function addSql(string $sql, array $params = [], array $types = []): void
    {
        try {
            $this->connection->executeStatement($sql);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();

            if (
                str_contains($message, 'already exists')
                || str_contains($message, 'Duplicate key on write or update')
                || str_contains($message, 'Duplicate key name')
                || str_contains($message, 'Can\'t DROP INDEX')
                || str_contains($message, 'needed in a foreign key constraint')
                || str_contains($message, 'Can\'t DROP FOREIGN KEY')
                || str_contains($message, 'Can\'t DROP COLUMN')
                || str_contains($message, 'Duplicate column name')
                || str_contains($message, 'Base table or view already exists')
            ) {
                return;
            }

            throw $exception;
        }
    }

    public function getDescription(): string
    {
        return 'Drop legacy foreign keys and indexes that remain after the schema reconciliation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_1`');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_3`');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY `signatures_ibfk_1`');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `categories_ibfk_1`');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `products_ibfk_1`');
        $this->addSql('DROP INDEX idx_source_company ON partnerships');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY `notifications_ibfk_1`');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY `settings_ibfk_1`');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY `companies_ibfk_1`');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY `collaborations_ibfk_1`');
    }

    public function down(Schema $schema): void
    {
        // Intentionally left empty.
    }
}
