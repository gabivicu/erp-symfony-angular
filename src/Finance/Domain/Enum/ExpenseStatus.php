<?php

declare(strict_types=1);

namespace App\Finance\Domain\Enum;

enum ExpenseStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PAID = 'paid';
}
