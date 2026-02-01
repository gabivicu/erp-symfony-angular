<?php

declare(strict_types=1);

namespace App\Finance\Application\Service;

use App\Finance\Domain\Entity\RecurringInvoice;
use App\Finance\Domain\Entity\Invoice;
use App\Finance\Domain\ValueObject\InvoiceId;

/**
 * Invoice Generation Service
 * Creates invoices from recurring invoice templates
 */
final class InvoiceGenerationService
{
    public function generateFromRecurring(RecurringInvoice $recurringInvoice): Invoice
    {
        // Create invoice based on recurring invoice template
        // Implementation would:
        // 1. Create Invoice entity
        // 2. Copy amount, description from recurring invoice
        // 3. Set appropriate client/company
        // 4. Generate invoice number
        
        // Simplified implementation
        $invoice = Invoice::create(
            InvoiceId::generate(),
            $recurringInvoice->getCompany()->getClient(), // Would need proper client
            $this->generateInvoiceNumber($recurringInvoice),
            $recurringInvoice->getAmount()->getCurrency()
        );

        return $invoice;
    }

    private function generateInvoiceNumber(RecurringInvoice $recurringInvoice): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        $number = 1; // Would query database
        return sprintf('INV-REC-%s-%04d', $year, $number);
    }
}
