<?php

declare(strict_types=1);

namespace App\Projects\Application\Service;

use App\Projects\Entity\Project;
use App\Finance\ValueObject\Money;
use App\Finance\Domain\Repository\InvoiceRepositoryInterface;
use App\Finance\Domain\Repository\ExpenseRepositoryInterface;

/**
 * Project Profitability Service
 * 
 * Business Logic: Calculate Project Profitability
 * Formula: InvoicedAmount - (TimeLogCost + Expenses)
 */
final class ProjectProfitabilityService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly ExpenseRepositoryInterface $expenseRepository
    ) {
    }

    /**
     * Calculate project profitability
     * 
     * @return array{profit: Money, revenue: Money, costs: Money, margin: float}
     */
    public function calculateProfitability(Project $project): array
    {
        // 1. Calculate total invoiced amount for this project
        $invoices = $this->invoiceRepository->findByProject($project);
        $revenue = Money::zero($project->getBudget()->getCurrency());
        
        foreach ($invoices as $invoice) {
            if ($invoice->getStatus()->value === 'paid') {
                $revenue = $revenue->add($invoice->getTotal());
            }
        }

        // 2. Calculate time log costs
        $timeLogCost = $project->calculateTimeLogCost();

        // 3. Calculate expenses linked to project
        $expenses = $this->expenseRepository->findByProject($project);
        $expenseTotal = Money::zero($project->getBudget()->getCurrency());
        
        foreach ($expenses as $expense) {
            if ($expense->getStatus()->value === 'approved' || $expense->getStatus()->value === 'paid') {
                $expenseTotal = $expenseTotal->add($expense->getAmount());
            }
        }

        // 4. Calculate total costs
        $totalCosts = $timeLogCost->add($expenseTotal);

        // 5. Calculate profit
        $profit = $revenue->subtract($totalCosts);

        // 6. Calculate margin percentage
        $margin = $revenue->getAmount() > 0 
            ? ($profit->getAmount() / $revenue->getAmount()) * 100 
            : 0.0;

        return [
            'profit' => $profit,
            'revenue' => $revenue,
            'costs' => $totalCosts,
            'margin' => round($margin, 2),
            'timeLogCost' => $timeLogCost,
            'expenseCost' => $expenseTotal,
        ];
    }
}
