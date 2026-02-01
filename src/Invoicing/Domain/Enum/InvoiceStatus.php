<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Enum;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::PAID => 'Paid',
        };
    }
}
