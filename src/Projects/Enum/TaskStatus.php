<?php

declare(strict_types=1);

namespace App\Projects\Enum;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case REVIEW = 'review';
    case DONE = 'done';
}
