<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Exception;

use App\Invoicing\Domain\Enum\InvoiceStatus;
use App\Invoicing\Domain\ValueObject\InvoiceId;

final class InvoiceCannotBeDeletedException extends \DomainException
{
    public static function invoiceAlreadySent(InvoiceId $invoiceId, InvoiceStatus $status): self
    {
        return new self(
            sprintf(
                'Invoice %s cannot be deleted because it is already %s',
                $invoiceId->toString(),
                $status->label()
            )
        );
    }
}
