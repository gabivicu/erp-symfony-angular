<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Repository;

use App\Invoicing\Domain\Entity\Invoice;
use App\Invoicing\Domain\ValueObject\InvoiceId;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): void;

    public function findById(InvoiceId $id): ?Invoice;

    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice;

    /**
     * @return Invoice[]
     */
    public function findAll(): array;

    public function remove(Invoice $invoice): void;
}
