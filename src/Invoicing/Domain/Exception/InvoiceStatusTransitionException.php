<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Exception;

use App\Invoicing\Domain\Enum\InvoiceStatus;

final class InvoiceStatusTransitionException extends \DomainException
{
    public static function canOnlyFinalizeDraft(): self
    {
        return new self('Only draft invoices can be finalized');
    }
}
