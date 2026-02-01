<?php

declare(strict_types=1);

namespace App\Invoicing\Exception;

use App\Invoicing\Enum\InvoiceStatus;

final class InvoiceStatusTransitionException extends \DomainException
{
    public static function canOnlyFinalizeDraft(): self
    {
        return new self('Only draft invoices can be finalized');
    }
}
