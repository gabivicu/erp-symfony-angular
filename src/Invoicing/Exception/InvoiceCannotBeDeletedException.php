<?php

declare(strict_types=1);

namespace App\Invoicing\Exception;

use App\Invoicing\Enum\InvoiceStatus;
use App\Invoicing\ValueObject\InvoiceId;

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
