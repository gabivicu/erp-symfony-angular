<?php

declare(strict_types=1);

namespace App\Invoicing\Application\Command;

final readonly class FinalizeInvoiceCommand
{
    public function __construct(
        public string $invoiceId,
        public string $userId
    ) {
    }
}
