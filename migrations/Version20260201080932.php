<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201080932 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clients (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C82E74979B1AD6 ON clients (company_id)');
        $this->addSql('CREATE TABLE invoice_lines (id VARCHAR(36) NOT NULL, description TEXT NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(19, 4) NOT NULL, currency VARCHAR(3) NOT NULL, vat_rate NUMERIC(5, 2) NOT NULL, invoice_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_72DBDC232989F1FD ON invoice_lines (invoice_id)');
        $this->addSql('CREATE TABLE invoices (id VARCHAR(36) NOT NULL, status VARCHAR(50) NOT NULL, invoice_number VARCHAR(100) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, subtotal NUMERIC(19, 4) NOT NULL, total_vat NUMERIC(19, 4) NOT NULL, total NUMERIC(19, 4) NOT NULL, currency VARCHAR(3) NOT NULL, client_id VARCHAR(36) NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6A2F2F952DA68207 ON invoices (invoice_number)');
        $this->addSql('CREATE INDEX IDX_6A2F2F9519EB6921 ON invoices (client_id)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95979B1AD6 ON invoices (company_id)');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E74979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice_lines ADD CONSTRAINT FK_72DBDC232989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F9519EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoices ADD CONSTRAINT FK_6A2F2F95979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clients DROP CONSTRAINT FK_C82E74979B1AD6');
        $this->addSql('ALTER TABLE invoice_lines DROP CONSTRAINT FK_72DBDC232989F1FD');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F9519EB6921');
        $this->addSql('ALTER TABLE invoices DROP CONSTRAINT FK_6A2F2F95979B1AD6');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE invoice_lines');
        $this->addSql('DROP TABLE invoices');
    }
}
