<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201081329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachments (id VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, file_path VARCHAR(500) NOT NULL, mime_type VARCHAR(100) NOT NULL, file_size BIGINT NOT NULL, uploaded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, attachable_type VARCHAR(50) DEFAULT NULL, attachable_id VARCHAR(36) DEFAULT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_47C4FAD6979B1AD6 ON attachments (company_id)');
        $this->addSql('CREATE TABLE comments (id VARCHAR(36) NOT NULL, content TEXT NOT NULL, commentable_type VARCHAR(50) NOT NULL, commentable_id VARCHAR(36) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5F9E962A979B1AD6 ON comments (company_id)');
        $this->addSql('CREATE TABLE expenses (id VARCHAR(36) NOT NULL, description TEXT NOT NULL, amount NUMERIC(19, 4) NOT NULL, currency VARCHAR(3) NOT NULL, status VARCHAR(50) NOT NULL, expense_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, receipt_path VARCHAR(500) DEFAULT NULL, company_id VARCHAR(36) NOT NULL, project_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_2496F35B979B1AD6 ON expenses (company_id)');
        $this->addSql('CREATE INDEX IDX_2496F35B166D1F9C ON expenses (project_id)');
        $this->addSql('CREATE TABLE projects (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, budget NUMERIC(19, 4) NOT NULL, budget_currency VARCHAR(3) NOT NULL, hourly_rate NUMERIC(19, 4) NOT NULL, hourly_rate_currency VARCHAR(3) NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C93B3A477153098 ON projects (code)');
        $this->addSql('CREATE INDEX IDX_5C93B3A4979B1AD6 ON projects (company_id)');
        $this->addSql('CREATE TABLE recurring_invoices (id VARCHAR(36) NOT NULL, frequency VARCHAR(50) NOT NULL, day_of_month INT NOT NULL, day_of_week VARCHAR(20) DEFAULT NULL, amount NUMERIC(19, 4) NOT NULL, currency VARCHAR(3) NOT NULL, description TEXT NOT NULL, is_active BOOLEAN NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_generated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, next_generation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, company_id VARCHAR(36) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FE93E284979B1AD6 ON recurring_invoices (company_id)');
        $this->addSql('CREATE TABLE tasks (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, estimated_hours INT DEFAULT NULL, priority INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, company_id VARCHAR(36) NOT NULL, project_id VARCHAR(36) NOT NULL, parent_task_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_50586597979B1AD6 ON tasks (company_id)');
        $this->addSql('CREATE INDEX IDX_50586597166D1F9C ON tasks (project_id)');
        $this->addSql('CREATE INDEX IDX_50586597FFFE75C0 ON tasks (parent_task_id)');
        $this->addSql('CREATE TABLE time_logs (id VARCHAR(36) NOT NULL, description TEXT NOT NULL, hours NUMERIC(10, 2) NOT NULL, logged_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, company_id VARCHAR(36) NOT NULL, project_id VARCHAR(36) NOT NULL, task_id VARCHAR(36) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5D32CF71979B1AD6 ON time_logs (company_id)');
        $this->addSql('CREATE INDEX IDX_5D32CF71166D1F9C ON time_logs (project_id)');
        $this->addSql('CREATE INDEX IDX_5D32CF718DB60186 ON time_logs (task_id)');
        $this->addSql('ALTER TABLE attachments ADD CONSTRAINT FK_47C4FAD6979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35B166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE projects ADD CONSTRAINT FK_5C93B3A4979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE recurring_invoices ADD CONSTRAINT FK_FE93E284979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597FFFE75C0 FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_logs ADD CONSTRAINT FK_5D32CF71979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_logs ADD CONSTRAINT FK_5D32CF71166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE time_logs ADD CONSTRAINT FK_5D32CF718DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE attachments DROP CONSTRAINT FK_47C4FAD6979B1AD6');
        $this->addSql('ALTER TABLE comments DROP CONSTRAINT FK_5F9E962A979B1AD6');
        $this->addSql('ALTER TABLE expenses DROP CONSTRAINT FK_2496F35B979B1AD6');
        $this->addSql('ALTER TABLE expenses DROP CONSTRAINT FK_2496F35B166D1F9C');
        $this->addSql('ALTER TABLE projects DROP CONSTRAINT FK_5C93B3A4979B1AD6');
        $this->addSql('ALTER TABLE recurring_invoices DROP CONSTRAINT FK_FE93E284979B1AD6');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_50586597979B1AD6');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_50586597166D1F9C');
        $this->addSql('ALTER TABLE tasks DROP CONSTRAINT FK_50586597FFFE75C0');
        $this->addSql('ALTER TABLE time_logs DROP CONSTRAINT FK_5D32CF71979B1AD6');
        $this->addSql('ALTER TABLE time_logs DROP CONSTRAINT FK_5D32CF71166D1F9C');
        $this->addSql('ALTER TABLE time_logs DROP CONSTRAINT FK_5D32CF718DB60186');
        $this->addSql('DROP TABLE attachments');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE expenses');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE recurring_invoices');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE time_logs');
    }
}
