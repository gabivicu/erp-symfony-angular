<?php

declare(strict_types=1);

namespace App\Finance\Infrastructure\Scheduler;

use App\Finance\Domain\Repository\RecurringInvoiceRepositoryInterface;
use App\Finance\Application\Service\InvoiceGenerationService;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

/**
 * Recurring Invoice Scheduler
 * 
 * Senior-level decision: Symfony Scheduler for automation
 * Runs daily at 2 AM to check and generate recurring invoices
 */
#[AsPeriodicTask(frequency: '1 day', from: '02:00')]
final class RecurringInvoiceScheduler
{
    public function __construct(
        private readonly RecurringInvoiceRepositoryInterface $recurringInvoiceRepository,
        private readonly InvoiceGenerationService $invoiceGenerationService
    ) {
    }

    public function __invoke(): void
    {
        // Find all active recurring invoices that should generate
        $recurringInvoices = $this->recurringInvoiceRepository->findDueForGeneration();

        foreach ($recurringInvoices as $recurringInvoice) {
            try {
                // Generate invoice
                $invoice = $this->invoiceGenerationService->generateFromRecurring($recurringInvoice);
                
                // Mark as generated
                $recurringInvoice->markAsGenerated();
                
                // Persist
                $this->recurringInvoiceRepository->save($recurringInvoice);
                
            } catch (\Exception $e) {
                // Log error but continue with other invoices
                error_log(sprintf(
                    'Failed to generate invoice from recurring invoice %s: %s',
                    $recurringInvoice->getId()->toString(),
                    $e->getMessage()
                ));
            }
        }
    }
}
