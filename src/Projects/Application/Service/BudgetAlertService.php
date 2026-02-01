<?php

declare(strict_types=1);

namespace App\Projects\Application\Service;

use App\Projects\Domain\Entity\Project;
use App\Finance\Domain\ValueObject\Money;

/**
 * Budget Alert Service
 * 
 * Business Logic: Alert if TimeLogs * HourlyRate > Budget
 */
final class BudgetAlertService
{
    /**
     * Check if project budget is exceeded
     */
    public function isBudgetExceeded(Project $project): bool
    {
        return $project->isBudgetExceeded();
    }

    /**
     * Calculate budget usage percentage
     */
    public function getBudgetUsagePercentage(Project $project): float
    {
        $timeLogCost = $project->calculateTimeLogCost();
        $budget = $project->getBudget();

        if ($budget->getAmountInCents() === 0) {
            return 0.0;
        }

        return ($timeLogCost->getAmountInCents() / $budget->getAmountInCents()) * 100;
    }

    /**
     * Get budget alert level
     * 
     * @return 'ok'|'warning'|'critical'
     */
    public function getAlertLevel(Project $project): string
    {
        $usage = $this->getBudgetUsagePercentage($project);

        if ($usage >= 100) {
            return 'critical';
        }

        if ($usage >= 80) {
            return 'warning';
        }

        return 'ok';
    }
}
