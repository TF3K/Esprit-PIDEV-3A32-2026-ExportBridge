<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416201029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certificate_requirements (id INT AUTO_INCREMENT NOT NULL, market_id INT NOT NULL, product_category VARCHAR(255) NOT NULL, certificate_type VARCHAR(255) NOT NULL, mandatory TINYINT DEFAULT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE certificates (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, certificate_number VARCHAR(255) NOT NULL, issue_date DATETIME DEFAULT NULL, expiry_date DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL, country_of_origin VARCHAR(255) DEFAULT NULL, issuing_authority VARCHAR(255) DEFAULT NULL, document_file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_updated DATETIME NOT NULL, company_id INT DEFAULT NULL, INDEX IDX_8D26FB5F979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE collaborations (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, last_updated DATETIME NOT NULL, partnership_id INT DEFAULT NULL, INDEX IDX_B9B3849A6AE7F85 (partnership_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE companies (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) NOT NULL, domain VARCHAR(255) DEFAULT NULL, tax_number VARCHAR(255) DEFAULT NULL, registration_number VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, address LONGTEXT DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, contact_phone VARCHAR(255) DEFAULT NULL, rating INT DEFAULT NULL, warnings INT DEFAULT NULL, is_banned TINYINT DEFAULT NULL, created_at DATETIME NOT NULL, last_updated DATETIME NOT NULL, company_manager_id INT DEFAULT NULL, market_id INT DEFAULT NULL, INDEX IDX_8244AA3AC5B0DDF5 (company_manager_id), INDEX IDX_8244AA3A622F3F37 (market_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact_history (id INT AUTO_INCREMENT NOT NULL, contact_date DATETIME NOT NULL, contact_type VARCHAR(255) NOT NULL, notes LONGTEXT DEFAULT NULL, source_company_id INT DEFAULT NULL, contacted_by_manager_id INT DEFAULT NULL, INDEX IDX_BD9551CAEB2F6BF2 (source_company_id), INDEX IDX_BD9551CA570C3D7A (contacted_by_manager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE managers (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL, created_at DATETIME NOT NULL, last_login DATETIME DEFAULT NULL, company_id INT DEFAULT NULL, INDEX IDX_A949E006979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE markets (id INT AUTO_INCREMENT NOT NULL, country_code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, region VARCHAR(255) DEFAULT NULL, is_eu TINYINT DEFAULT NULL, description LONGTEXT DEFAULT NULL, trade_agreement VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, is_read TINYINT DEFAULT NULL, created_at DATETIME NOT NULL, manager_id INT DEFAULT NULL, INDEX IDX_6000B0D3783E3463 (manager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE partnerships (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(255) NOT NULL, type VARCHAR(255) DEFAULT NULL, established_date DATE DEFAULT NULL, terminated_date DATE DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, last_updated DATETIME NOT NULL, source_company_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_2AE65832EB2F6BF2 (source_company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, hs_code VARCHAR(255) DEFAULT NULL, quantity NUMERIC(15, 2) DEFAULT NULL, unit VARCHAR(255) DEFAULT NULL, unit_price NUMERIC(15, 2) DEFAULT NULL, currency VARCHAR(255) DEFAULT NULL, origin_criteria VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, last_updated DATETIME NOT NULL, company_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_B3BA5A5A979B1AD6 (company_id), INDEX IDX_B3BA5A5A12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, language VARCHAR(255) DEFAULT NULL, theme VARCHAR(255) DEFAULT NULL, email_notifications TINYINT DEFAULT NULL, push_notifications TINYINT DEFAULT NULL, certificate_expiry_alerts TINYINT DEFAULT NULL, alert_days_before INT DEFAULT NULL, date_format VARCHAR(255) DEFAULT NULL, currency VARCHAR(255) DEFAULT NULL, timezone VARCHAR(255) DEFAULT NULL, manager_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_E545A0C5783E3463 (manager_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE signatures (id INT AUTO_INCREMENT NOT NULL, signatory_name VARCHAR(255) NOT NULL, signatory_title VARCHAR(255) DEFAULT NULL, signed_date DATETIME NOT NULL, digital_signature LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, certificate_id INT DEFAULT NULL, INDEX IDX_1A7B360C99223FFD (certificate_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certificates ADD CONSTRAINT FK_8D26FB5F979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE collaborations ADD CONSTRAINT FK_B9B3849A6AE7F85 FOREIGN KEY (partnership_id) REFERENCES partnerships (id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3AC5B0DDF5 FOREIGN KEY (company_manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE companies ADD CONSTRAINT FK_8244AA3A622F3F37 FOREIGN KEY (market_id) REFERENCES markets (id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CAEB2F6BF2 FOREIGN KEY (source_company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE contact_history ADD CONSTRAINT FK_BD9551CA570C3D7A FOREIGN KEY (contacted_by_manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE managers ADD CONSTRAINT FK_A949E006979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE partnerships ADD CONSTRAINT FK_2AE65832EB2F6BF2 FOREIGN KEY (source_company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A12469DE2 FOREIGN KEY (category_id) REFERENCES product_categories (id)');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5783E3463 FOREIGN KEY (manager_id) REFERENCES managers (id)');
        $this->addSql('ALTER TABLE signatures ADD CONSTRAINT FK_1A7B360C99223FFD FOREIGN KEY (certificate_id) REFERENCES certificates (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificates DROP FOREIGN KEY FK_8D26FB5F979B1AD6');
        $this->addSql('ALTER TABLE collaborations DROP FOREIGN KEY FK_B9B3849A6AE7F85');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3AC5B0DDF5');
        $this->addSql('ALTER TABLE companies DROP FOREIGN KEY FK_8244AA3A622F3F37');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CAEB2F6BF2');
        $this->addSql('ALTER TABLE contact_history DROP FOREIGN KEY FK_BD9551CA570C3D7A');
        $this->addSql('ALTER TABLE managers DROP FOREIGN KEY FK_A949E006979B1AD6');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3783E3463');
        $this->addSql('ALTER TABLE partnerships DROP FOREIGN KEY FK_2AE65832EB2F6BF2');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A979B1AD6');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A12469DE2');
        $this->addSql('ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5783E3463');
        $this->addSql('ALTER TABLE signatures DROP FOREIGN KEY FK_1A7B360C99223FFD');
        $this->addSql('DROP TABLE certificate_requirements');
        $this->addSql('DROP TABLE certificates');
        $this->addSql('DROP TABLE collaborations');
        $this->addSql('DROP TABLE companies');
        $this->addSql('DROP TABLE contact_history');
        $this->addSql('DROP TABLE managers');
        $this->addSql('DROP TABLE markets');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE partnerships');
        $this->addSql('DROP TABLE product_categories');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE signatures');
    }
}
