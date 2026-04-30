<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260425144745 extends AbstractMigration
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

    private function tableExists(string $tableName): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
            [$tableName],
        );
    }

    private function foreignKeyExists(string $tableName, string $foreignKeyName): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT 1 FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ? LIMIT 1',
            [$tableName, $foreignKeyName],
        );
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        if (!$this->tableExists('reset_password_request')) {
            $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        }

        if (!$this->foreignKeyExists('reset_password_request', 'FK_7CE748AA76ED395')) {
            $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES managers (id)');
        }
        $this->addSql('DROP INDEX IF EXISTS idx_market_id ON certificate_requirements');
        $this->addSql('ALTER TABLE certificate_requirements CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE market_id market_id INT NOT NULL, CHANGE product_category product_category VARCHAR(255) NOT NULL, CHANGE certificate_type certificate_type VARCHAR(255) NOT NULL, CHANGE mandatory mandatory TINYINT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE certificates CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE company_id company_id INT DEFAULT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE certificate_number certificate_number VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE country_of_origin country_of_origin VARCHAR(255) DEFAULT NULL, CHANGE document_file document_file VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_updated last_updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_company_id ON certificates');
        $this->addSql('CREATE INDEX IDX_8D26FB5F979B1AD6 ON certificates (company_id)');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY `collaborations_ibfk_1`');
        $this->addSql('DROP INDEX IF EXISTS idx_start_date ON collaborations');
        $this->addSql('DROP INDEX IF EXISTS idx_status ON collaborations');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY `collaborations_ibfk_1`');
        $this->addSql('ALTER TABLE collaborations CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE partnership_id partnership_id INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_updated last_updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE collaborations ADD CONSTRAINT FK_B9B3849A6AE7F85 FOREIGN KEY (partnership_id) REFERENCES partnerships (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_partnership_id ON collaborations');
        $this->addSql('CREATE INDEX IDX_B9B3849A6AE7F85 ON collaborations (partnership_id)');
        $this->addSql('ALTER TABLE collaborations ADD CONSTRAINT `collaborations_ibfk_1` FOREIGN KEY (partnership_id) REFERENCES partnerships (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY `companies_ibfk_1`');
        $this->addSql('DROP INDEX IF EXISTS idx_company_name ON companies');
        $this->addSql('DROP INDEX IF EXISTS idx_country ON companies');
        $this->addSql('DROP INDEX IF EXISTS tax_number ON companies');
        $this->addSql('DROP INDEX IF EXISTS idx_tax_number ON companies');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY `companies_ibfk_1`');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY `FK_MARKET_COMPANY`');
        $this->addSql('ALTER TABLE companies CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE company_name company_name VARCHAR(255) NOT NULL, CHANGE domain domain VARCHAR(255) DEFAULT NULL, CHANGE tax_number tax_number VARCHAR(255) DEFAULT NULL, CHANGE registration_number registration_number VARCHAR(255) DEFAULT NULL, CHANGE country country VARCHAR(255) DEFAULT NULL, CHANGE address address LONGTEXT DEFAULT NULL, CHANGE contact_email contact_email VARCHAR(255) DEFAULT NULL, CHANGE contact_phone contact_phone VARCHAR(255) DEFAULT NULL, CHANGE rating rating INT DEFAULT NULL, CHANGE warnings warnings INT DEFAULT NULL, CHANGE is_banned is_banned TINYINT DEFAULT NULL, CHANGE company_manager_id company_manager_id INT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_updated last_updated DATETIME NOT NULL, CHANGE market_id market_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3AC5B0DDF5 FOREIGN KEY (company_manager_id) REFERENCES managers (id)');
        $this->addSql('DROP INDEX IF EXISTS company_manager_id ON companies');
        $this->addSql('CREATE INDEX IDX_8244AA3AC5B0DDF5 ON companies (company_manager_id)');
        $this->addSql('DROP INDEX IF EXISTS fk_market_company ON companies');
        $this->addSql('CREATE INDEX IDX_8244AA3A622F3F37 ON companies (market_id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (company_manager_id) REFERENCES managers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT `FK_MARKET_COMPANY` FOREIGN KEY (market_id) REFERENCES markets (id)');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_1`');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_2`');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_3`');
        $this->addSql('DROP INDEX IF EXISTS idx_target_company ON contact_history');
        $this->addSql('DROP INDEX IF EXISTS idx_contact_date ON contact_history');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_1`');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY `contact_history_ibfk_3`');
        $this->addSql('ALTER TABLE contact_history DROP target_company_id, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE source_company_id source_company_id INT DEFAULT NULL, CHANGE contact_date contact_date DATETIME NOT NULL, CHANGE contact_type contact_type VARCHAR(255) NOT NULL, CHANGE notes notes LONGTEXT DEFAULT NULL, CHANGE contacted_by_manager_id contacted_by_manager_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CAEB2F6BF2 FOREIGN KEY (source_company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CA570C3D7A FOREIGN KEY (contacted_by_manager_id) REFERENCES managers (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_source_company ON contact_history');
        $this->addSql('CREATE INDEX IDX_BD9551CAEB2F6BF2 ON contact_history (source_company_id)');
        $this->addSql('DROP INDEX IF EXISTS idx_contacted_by ON contact_history');
        $this->addSql('CREATE INDEX IDX_BD9551CA570C3D7A ON contact_history (contacted_by_manager_id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT `contact_history_ibfk_1` FOREIGN KEY (source_company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT `contact_history_ibfk_3` FOREIGN KEY (contacted_by_manager_id) REFERENCES managers (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE managers DROP FOREIGN KEY `fk_company_manager`');
        $this->addSql('ALTER TABLE managers CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL, CHANGE company_id company_id INT DEFAULT NULL, CHANGE roles roles JSON NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX IF EXISTS email ON managers');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A949E006E7927C74 ON managers (email)');
        $this->addSql('DROP INDEX IF EXISTS fk_company_manager ON managers');
        $this->addSql('CREATE INDEX IDX_A949E006979B1AD6 ON managers (company_id)');
        $this->addSql('ALTER TABLE managers ADD CONSTRAINT `fk_company_manager` FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_region ON markets');
        $this->addSql('DROP INDEX IF EXISTS idx_is_eu ON markets');
        $this->addSql('DROP INDEX IF EXISTS country_code ON markets');
        $this->addSql('DROP INDEX IF EXISTS idx_country_code ON markets');
        $this->addSql('ALTER TABLE markets CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE country_code country_code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE region region VARCHAR(255) DEFAULT NULL, CHANGE is_eu is_eu TINYINT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE trade_agreement trade_agreement VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY `notifications_ibfk_1`');
        $this->addSql('DROP INDEX IF EXISTS idx_is_read ON notifications');
        $this->addSql('DROP INDEX IF EXISTS idx_created_at ON notifications');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY `notifications_ibfk_1`');
        $this->addSql('ALTER TABLE notifications CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE manager_id manager_id INT DEFAULT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE is_read is_read TINYINT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_manager_id ON notifications');
        $this->addSql('CREATE INDEX IDX_6000B0D3783E3463 ON notifications (manager_id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (manager_id) REFERENCES managers (id) ON DELETE CASCADE');
        $this->addSql('DELETE p1 FROM partnerships p1 INNER JOIN partnerships p2 ON p1.source_company_id = p2.source_company_id AND p1.id < p2.id');
        $this->addSql('DROP INDEX IF EXISTS idx_source_company ON partnerships');
        $this->addSql('ALTER TABLE partnerships ADD UNIQUE INDEX UNIQ_2AE65832EB2F6BF2 (source_company_id)');
        $this->addSql('ALTER TABLE partnerships DROP FOREIGN KEY `partnerships_ibfk_1`');
        $this->addSql('ALTER TABLE partnerships DROP FOREIGN KEY `partnerships_ibfk_2`');
        $this->addSql('DROP INDEX IF EXISTS unique_partnership ON partnerships');
        $this->addSql('DROP INDEX IF EXISTS idx_status ON partnerships');
        $this->addSql('DROP INDEX IF EXISTS idx_target_company ON partnerships');
        $this->addSql('ALTER TABLE partnerships DROP target_company_id, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE source_company_id source_company_id INT DEFAULT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL, CHANGE notes notes LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_updated last_updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE partnerships ADD CONSTRAINT FK_2AE65832EB2F6BF2 FOREIGN KEY (source_company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `categories_ibfk_1`');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `products_ibfk_1`');
        $this->addSql('DROP INDEX IF EXISTS idx_hs_code ON products');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `categories_ibfk_1`');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY `products_ibfk_1`');
        $this->addSql('ALTER TABLE products CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE company_id company_id INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE hs_code hs_code VARCHAR(255) DEFAULT NULL, CHANGE quantity quantity NUMERIC(10, 2) DEFAULT NULL, CHANGE unit unit VARCHAR(255) DEFAULT NULL, CHANGE unit_price unit_price NUMERIC(10, 2) DEFAULT NULL, CHANGE currency currency VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE last_updated last_updated DATETIME NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES product_categories (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_company_id ON products');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A979B1AD6 ON products (company_id)');
        $this->addSql('DROP INDEX IF EXISTS idx_category ON products');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A12469DE2 ON products (category_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY `settings_ibfk_1`');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY `settings_ibfk_1`');
        $this->addSql('ALTER TABLE settings CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE manager_id manager_id INT DEFAULT NULL, CHANGE language language VARCHAR(255) DEFAULT NULL, CHANGE theme theme VARCHAR(255) DEFAULT NULL, CHANGE email_notifications email_notifications TINYINT DEFAULT NULL, CHANGE push_notifications push_notifications TINYINT DEFAULT NULL, CHANGE certificate_expiry_alerts certificate_expiry_alerts TINYINT DEFAULT NULL, CHANGE alert_days_before alert_days_before INT DEFAULT NULL, CHANGE date_format date_format VARCHAR(255) DEFAULT NULL, CHANGE currency currency VARCHAR(255) DEFAULT NULL, CHANGE timezone timezone VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('DROP INDEX IF EXISTS manager_id ON settings');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E545A0C5783E3463 ON settings (manager_id)');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (manager_id) REFERENCES managers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY `signatures_ibfk_1`');
        $this->addSql('DROP INDEX IF EXISTS idx_type ON signatures');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY `signatures_ibfk_1`');
        $this->addSql('ALTER TABLE signatures CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE certificate_id certificate_id INT DEFAULT NULL, CHANGE signed_date signed_date DATETIME NOT NULL, CHANGE digital_signature digital_signature LONGTEXT DEFAULT NULL, CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE signatures ADD CONSTRAINT FK_1A7B360C99223FFD FOREIGN KEY (certificate_id) REFERENCES certificates (id)');
        $this->addSql('DROP INDEX IF EXISTS idx_certificate_id ON signatures');
        $this->addSql('CREATE INDEX IDX_1A7B360C99223FFD ON signatures (certificate_id)');
        $this->addSql('ALTER TABLE signatures ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (certificate_id) REFERENCES certificates (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F979B1AD6');
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F979B1AD6');
        $this->addSql('ALTER TABLE certificates CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE certificate_number certificate_number VARCHAR(100) NOT NULL, CHANGE status status VARCHAR(20) DEFAULT \'VALID\' NOT NULL, CHANGE country_of_origin country_of_origin VARCHAR(100) DEFAULT NULL, CHANGE document_file document_file VARCHAR(500) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE last_updated last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE company_id company_id BIGINT NOT NULL');
        $this->addSql('DROP INDEX idx_8d26fb5f979b1ad6 ON certificates');
        $this->addSql('CREATE INDEX idx_company_id ON certificates (company_id)');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE certificate_requirements CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE market_id market_id BIGINT UNSIGNED NOT NULL, CHANGE product_category product_category VARCHAR(50) NOT NULL, CHANGE certificate_type certificate_type VARCHAR(50) NOT NULL, CHANGE mandatory mandatory TINYINT DEFAULT 1, CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('CREATE INDEX idx_market_id ON certificate_requirements (market_id)');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY FK_B9B3849A6AE7F85');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY FK_B9B3849A6AE7F85');
        $this->addSql('ALTER TABLE collaborations CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE status status VARCHAR(20) DEFAULT \'PLANNED\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE last_updated last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE partnership_id partnership_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE collaborations ADD CONSTRAINT `collaborations_ibfk_1` FOREIGN KEY (partnership_id) REFERENCES partnerships (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_start_date ON collaborations (start_date)');
        $this->addSql('CREATE INDEX idx_status ON collaborations (status)');
        $this->addSql('DROP INDEX idx_b9b3849a6ae7f85 ON collaborations');
        $this->addSql('CREATE INDEX idx_partnership_id ON collaborations (partnership_id)');
        $this->addSql('ALTER TABLE collaborations ADD CONSTRAINT FK_B9B3849A6AE7F85 FOREIGN KEY (partnership_id) REFERENCES partnerships (id)');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3AC5B0DDF5');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3AC5B0DDF5');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3A622F3F37');
        $this->addSql('ALTER TABLE companies CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE company_name company_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_general_ci`, CHANGE domain domain VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE tax_number tax_number VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE registration_number registration_number VARCHAR(100) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE country country VARCHAR(100) DEFAULT \'Tunisia\' COLLATE `utf8mb4_general_ci`, CHANGE address address TEXT DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE contact_email contact_email VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE contact_phone contact_phone VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE rating rating INT DEFAULT 1, CHANGE warnings warnings INT DEFAULT 0, CHANGE is_banned is_banned TINYINT DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE last_updated last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE company_manager_id company_manager_id BIGINT DEFAULT NULL, CHANGE market_id market_id BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (company_manager_id) REFERENCES managers (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_company_name ON companies (company_name)');
        $this->addSql('CREATE INDEX idx_country ON companies (country)');
        $this->addSql('CREATE UNIQUE INDEX tax_number ON companies (tax_number)');
        $this->addSql('CREATE INDEX idx_tax_number ON companies (tax_number)');
        $this->addSql('DROP INDEX idx_8244aa3a622f3f37 ON companies');
        $this->addSql('CREATE INDEX FK_MARKET_COMPANY ON companies (market_id)');
        $this->addSql('DROP INDEX idx_8244aa3ac5b0ddf5 ON companies');
        $this->addSql('CREATE INDEX company_manager_id ON companies (company_manager_id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3AC5B0DDF5 FOREIGN KEY (company_manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3A622F3F37 FOREIGN KEY (market_id) REFERENCES markets (id)');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CAEB2F6BF2');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CA570C3D7A');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CAEB2F6BF2');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CA570C3D7A');
        $this->addSql('ALTER TABLE contact_history ADD target_company_id BIGINT NOT NULL, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE contact_date contact_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE contact_type contact_type VARCHAR(50) NOT NULL, CHANGE notes notes TEXT DEFAULT NULL, CHANGE source_company_id source_company_id BIGINT NOT NULL, CHANGE contacted_by_manager_id contacted_by_manager_id BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT `contact_history_ibfk_1` FOREIGN KEY (source_company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT `contact_history_ibfk_2` FOREIGN KEY (target_company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT `contact_history_ibfk_3` FOREIGN KEY (contacted_by_manager_id) REFERENCES managers (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX idx_target_company ON contact_history (target_company_id)');
        $this->addSql('CREATE INDEX idx_contact_date ON contact_history (contact_date)');
        $this->addSql('DROP INDEX idx_bd9551ca570c3d7a ON contact_history');
        $this->addSql('CREATE INDEX idx_contacted_by ON contact_history (contacted_by_manager_id)');
        $this->addSql('DROP INDEX idx_bd9551caeb2f6bf2 ON contact_history');
        $this->addSql('CREATE INDEX idx_source_company ON contact_history (source_company_id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CAEB2F6BF2 FOREIGN KEY (source_company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CA570C3D7A FOREIGN KEY (contacted_by_manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE managers DROP FOREIGN KEY FK_A949E006979B1AD6');
        $this->addSql('ALTER TABLE managers CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE first_name first_name VARCHAR(100) NOT NULL, CHANGE last_name last_name VARCHAR(100) NOT NULL, CHANGE roles roles LONGTEXT DEFAULT \'["ROLE_USER"]\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE company_id company_id BIGINT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_a949e006979b1ad6 ON managers');
        $this->addSql('CREATE INDEX fk_company_manager ON managers (company_id)');
        $this->addSql('DROP INDEX uniq_a949e006e7927c74 ON managers');
        $this->addSql('CREATE UNIQUE INDEX email ON managers (email)');
        $this->addSql('ALTER TABLE managers ADD CONSTRAINT FK_A949E006979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE markets CHANGE id id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE name name VARCHAR(100) NOT NULL COLLATE `utf8mb4_general_ci`, CHANGE country_code country_code VARCHAR(3) NOT NULL COLLATE `utf8mb4_general_ci`, CHANGE region region VARCHAR(50) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE is_eu is_eu TINYINT DEFAULT 0, CHANGE description description TEXT DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE trade_agreement trade_agreement VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_general_ci`, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE INDEX idx_region ON markets (region)');
        $this->addSql('CREATE INDEX idx_is_eu ON markets (is_eu)');
        $this->addSql('CREATE UNIQUE INDEX country_code ON markets (country_code)');
        $this->addSql('CREATE INDEX idx_country_code ON markets (country_code)');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3783E3463');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3783E3463');
        $this->addSql('ALTER TABLE notifications CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE message message TEXT NOT NULL, CHANGE is_read is_read TINYINT DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE manager_id manager_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (manager_id) REFERENCES managers (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_is_read ON notifications (is_read)');
        $this->addSql('CREATE INDEX idx_created_at ON notifications (created_at)');
        $this->addSql('DROP INDEX idx_6000b0d3783e3463 ON notifications');
        $this->addSql('CREATE INDEX idx_manager_id ON notifications (manager_id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE partnerships DROP INDEX UNIQ_2AE65832EB2F6BF2, ADD INDEX idx_source_company (source_company_id)');
        $this->addSql('ALTER TABLE partnerships DROP FOREIGN KEY FK_2AE65832EB2F6BF2');
        $this->addSql('ALTER TABLE partnerships ADD target_company_id BIGINT NOT NULL, CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE status status VARCHAR(20) DEFAULT \'PENDING\' NOT NULL, CHANGE type type VARCHAR(50) DEFAULT NULL, CHANGE notes notes TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE last_updated last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE source_company_id source_company_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE partnerships ADD CONSTRAINT `partnerships_ibfk_1` FOREIGN KEY (source_company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE partnerships ADD CONSTRAINT `partnerships_ibfk_2` FOREIGN KEY (target_company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX unique_partnership ON partnerships (source_company_id, target_company_id)');
        $this->addSql('CREATE INDEX idx_status ON partnerships (status)');
        $this->addSql('CREATE INDEX idx_target_company ON partnerships (target_company_id)');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A979B1AD6');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A979B1AD6');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE products CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE hs_code hs_code VARCHAR(20) DEFAULT NULL, CHANGE quantity quantity NUMERIC(15, 2) DEFAULT NULL, CHANGE unit unit VARCHAR(20) DEFAULT NULL, CHANGE unit_price unit_price NUMERIC(15, 2) DEFAULT NULL, CHANGE currency currency VARCHAR(3) DEFAULT \'TND\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE last_updated last_updated DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE company_id company_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (category_id) REFERENCES product_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_hs_code ON products (hs_code)');
        $this->addSql('DROP INDEX idx_b3ba5a5a12469de2 ON products');
        $this->addSql('CREATE INDEX idx_category ON products (category_id)');
        $this->addSql('DROP INDEX idx_b3ba5a5a979b1ad6 ON products');
        $this->addSql('CREATE INDEX idx_company_id ON products (company_id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES product_categories (id)');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5783E3463');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5783E3463');
        $this->addSql('ALTER TABLE settings CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE language language VARCHAR(10) DEFAULT \'fr\', CHANGE theme theme VARCHAR(20) DEFAULT \'light\', CHANGE email_notifications email_notifications TINYINT DEFAULT 1, CHANGE push_notifications push_notifications TINYINT DEFAULT 1, CHANGE certificate_expiry_alerts certificate_expiry_alerts TINYINT DEFAULT 1, CHANGE alert_days_before alert_days_before INT DEFAULT 30, CHANGE date_format date_format VARCHAR(20) DEFAULT \'DD/MM/YYYY\', CHANGE currency currency VARCHAR(3) DEFAULT \'TND\', CHANGE timezone timezone VARCHAR(50) DEFAULT \'Africa/Tunis\', CHANGE manager_id manager_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (manager_id) REFERENCES managers (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX uniq_e545a0c5783e3463 ON settings');
        $this->addSql('CREATE UNIQUE INDEX manager_id ON settings (manager_id)');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY FK_1A7B360C99223FFD');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY FK_1A7B360C99223FFD');
        $this->addSql('ALTER TABLE signatures CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE signed_date signed_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE digital_signature digital_signature TEXT DEFAULT NULL, CHANGE type type VARCHAR(50) NOT NULL, CHANGE certificate_id certificate_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE signatures ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (certificate_id) REFERENCES certificates (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_type ON signatures (type)');
        $this->addSql('DROP INDEX idx_1a7b360c99223ffd ON signatures');
        $this->addSql('CREATE INDEX idx_certificate_id ON signatures (certificate_id)');
        $this->addSql('ALTER TABLE signatures ADD CONSTRAINT FK_1A7B360C99223FFD FOREIGN KEY (certificate_id) REFERENCES certificates (id)');
    }
}
