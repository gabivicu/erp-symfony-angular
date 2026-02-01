<?php

declare(strict_types=1);

namespace App\CRM\Application\Service;

use App\CRM\Domain\Entity\Estimate;
use App\CRM\Domain\Entity\Lead;
use App\Finance\Domain\Entity\Invoice;
use App\Finance\Domain\ValueObject\Money;
use App\Projects\Domain\Entity\Project;
use App\Projects\Domain\ValueObject\ProjectId;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Estimate Conversion Service
 * 
 * Complex Business Logic: Convert Accepted Estimate to Project + Invoice
 * 
 * Senior-level decision: Service layer for complex cross-module operations
 * Wraps everything in a database transaction for data consistency
 */
final class EstimateConversionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $connection,
        private readonly ProjectFactory $projectFactory,
        private readonly InvoiceFactory $invoiceFactory
    ) {
    }

    /**
     * Convert Accepted Estimate to Project and Invoice
     * 
     * Business Rules:
     * 1. Estimate must be ACCEPTED
     * 2. Creates Project from estimate details
     * 3. Creates initial Invoice (deposit if applicable)
     * 4. Updates Lead status to WON
     * 5. All operations in single transaction
     */
    public function convertEstimateToProject(Estimate $estimate, ?float $depositPercentage = null): ConversionResult
    {
        if (!$estimate->isAccepted()) {
            throw new \DomainException('Only accepted estimates can be converted to projects');
        }

        // Start transaction
        $this->connection->beginTransaction();

        try {
            // 1. Create Project from Estimate
            $project = $this->projectFactory->createFromEstimate($estimate);

            // 2. Create initial Invoice (with deposit if specified)
            $invoice = null;
            if ($depositPercentage !== null && $depositPercentage > 0) {
                $depositAmount = $estimate->getTotal()->multiply((int)($depositPercentage * 100))->divide(100);
                $invoice = $this->invoiceFactory->createDepositInvoice($estimate, $project, $depositAmount);
            } else {
                // Create full invoice
                $invoice = $this->invoiceFactory->createFromEstimate($estimate, $project);
            }

            // 3. Update Lead status to WON
            $lead = $estimate->getLead();
            $lead->markAsWon();

            // 4. Persist all entities
            $this->entityManager->persist($project);
            $this->entityManager->persist($invoice);
            $this->entityManager->persist($lead);
            $this->entityManager->persist($estimate);
            $this->entityManager->flush();

            // Commit transaction
            $this->connection->commit();

            return new ConversionResult($project, $invoice, $lead);

        } catch (\Exception $e) {
            // Rollback on any error
            $this->connection->rollBack();
            throw new \RuntimeException(
                sprintf('Failed to convert estimate to project: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
}

/**
 * Conversion Result DTO
 */
final class ConversionResult
{
    public function __construct(
        public readonly Project $project,
        public readonly Invoice $invoice,
        public readonly Lead $lead
    ) {
    }
}
