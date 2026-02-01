<?php

declare(strict_types=1);

namespace App\CRM\Domain\Enum;

enum EstimateStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
}
