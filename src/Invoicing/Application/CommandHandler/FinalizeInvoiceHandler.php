<?php

declare(strict_types=1);

namespace App\Invoicing\Application\CommandHandler;

use App\Invoicing\Application\Command\FinalizeInvoiceCommand;
use App\Invoicing\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoicing\Domain\ValueObject\InvoiceId;

final class FinalizeInvoiceHandler
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository
    ) {
    }

    public function __invoke(FinalizeInvoiceCommand $command): void
    {
        $invoice = $this->invoiceRepository->findById(
            InvoiceId::fromString($command->invoiceId)
        );

        if ($invoice === null) {
            throw new \InvalidArgumentException('Invoice not found');
        }

        // Domain method handles business logic
        $invoice->finalize();

        $this->invoiceRepository->save($invoice);
    }
}
