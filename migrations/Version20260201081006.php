<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201081006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Note: clients, invoices, invoice_lines were already created in Version20260201080932
        $this->addSql('CREATE TABLE leads (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(50) DEFAULT NULL, company_name VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, source VARCHAR(100) DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, converted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_17904552979B1AD6 ON leads (company_id)');
        $this->addSql('CREATE TABLE estimates (id VARCHAR(36) NOT NULL, estimate_number VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, subtotal NUMERIC(19, 4) NOT NULL, total_vat NUMERIC(19, 4) NOT NULL, total NUMERIC(19, 4) NOT NULL, currency VARCHAR(3) NOT NULL, validity_days INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, lines JSON NOT NULL, company_id VARCHAR(36) NOT NULL, lead_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_85B8B0EE9D3C8144 ON estimates (estimate_number)');
        $this->addSql('CREATE INDEX IDX_85B8B0EE979B1AD6 ON estimates (company_id)');
        $this->addSql('CREATE INDEX IDX_85B8B0EE55458D ON estimates (lead_id)');
        $this->addSql('ALTER TABLE estimates ADD CONSTRAINT FK_85B8B0EE979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE estimates ADD CONSTRAINT FK_85B8B0EE55458D FOREIGN KEY (lead_id) REFERENCES leads (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE leads ADD CONSTRAINT FK_17904552979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE estimates DROP CONSTRAINT FK_85B8B0EE979B1AD6');
        $this->addSql('ALTER TABLE estimates DROP CONSTRAINT FK_85B8B0EE55458D');
        $this->addSql('ALTER TABLE leads DROP CONSTRAINT FK_17904552979B1AD6');
        $this->addSql('DROP TABLE estimates');
        $this->addSql('DROP TABLE leads');
    }
}
