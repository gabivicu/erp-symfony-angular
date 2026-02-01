<?php

declare(strict_types=1);

namespace App\Finance\Domain\Repository;

use App\Finance\Domain\Entity\RecurringInvoice;
use App\Finance\Domain\ValueObject\RecurringInvoiceId;

interface RecurringInvoiceRepositoryInterface
{
    public function save(RecurringInvoice $recurringInvoice): void;

    public function findById(RecurringInvoiceId $id): ?RecurringInvoice;

    /**
     * Find all recurring invoices that are due for generation
     * 
     * @return RecurringInvoice[]
     */
    public function findDueForGeneration(): array;

    /**
     * @return RecurringInvoice[]
     */
    public function findAll(): array;

    public function remove(RecurringInvoice $recurringInvoice): void;
}
