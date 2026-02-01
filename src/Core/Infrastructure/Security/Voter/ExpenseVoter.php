<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Security\Voter;

use App\Finance\Domain\Entity\Expense;
use App\Finance\Domain\Enum\ExpenseStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Expense Voter
 * 
 * Senior-level decision: Complex permission logic
 * - Employee: Can create expenses
 * - Manager: Can approve expenses
 * - Admin: Full access
 */
final class ExpenseVoter extends Voter
{
    public const CREATE = 'EXPENSE_CREATE';
    public const VIEW = 'EXPENSE_VIEW';
    public const APPROVE = 'EXPENSE_APPROVE';
    public const DELETE = 'EXPENSE_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::VIEW, self::APPROVE, self::DELETE])
            && ($subject === null || $subject instanceof Expense);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user) {
            return false;
        }

        $roles = $token->getRoleNames();

        return match ($attribute) {
            self::CREATE => $this->canCreate($roles),
            self::VIEW => $this->canView($subject, $roles),
            self::APPROVE => $this->canApprove($subject, $roles),
            self::DELETE => $this->canDelete($subject, $roles),
            default => false,
        };
    }

    private function canCreate(array $roles): bool
    {
        // Employees and above can create expenses
        return in_array('ROLE_EMPLOYEE', $roles, true)
            || in_array('ROLE_MANAGER', $roles, true)
            || in_array('ROLE_ADMIN', $roles, true);
    }

    private function canView(?Expense $expense, array $roles): bool
    {
        // All authenticated users can view expenses in their company
        return true; // Company filter handles isolation
    }

    private function canApprove(?Expense $expense, array $roles): bool
    {
        // Only Managers and Admins can approve
        if (!in_array('ROLE_MANAGER', $roles, true) && !in_array('ROLE_ADMIN', $roles, true)) {
            return false;
        }

        if ($expense === null) {
            return true; // Can approve in general
        }

        // Can only approve pending expenses
        return $expense->getStatus() === ExpenseStatus::PENDING;
    }

    private function canDelete(?Expense $expense, array $roles): bool
    {
        // Only Admins can delete, and only pending expenses
        if (!in_array('ROLE_ADMIN', $roles, true)) {
            return false;
        }

        if ($expense === null) {
            return true;
        }

        return $expense->getStatus() === ExpenseStatus::PENDING;
    }
}
