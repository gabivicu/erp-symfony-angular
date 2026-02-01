<?php

declare(strict_types=1);

namespace App\CRM\Application\Service;

use App\CRM\Domain\Entity\Estimate;
use App\Core\Domain\Entity\Company;
use App\Projects\Domain\Entity\Project;
use App\Projects\Domain\ValueObject\ProjectId;
use App\Finance\Domain\ValueObject\Money;

/**
 * Project Factory
 * Creates Project entities from Estimates
 */
final class ProjectFactory
{
    public function createFromEstimate(Estimate $estimate): Project
    {
        $company = $estimate->getCompany();
        
        // Generate project code (e.g., PROJ-2024-001)
        $projectCode = $this->generateProjectCode($company);

        // Use estimate total as initial budget
        $budget = $estimate->getTotal();
        
        // Default hourly rate (could come from company settings)
        $hourlyRate = Money::fromFloat(100.0, $budget->getCurrency());

        $project = new Project(
            ProjectId::generate(),
            $estimate->getLead()->getName() . ' - Project',
            $projectCode,
            $budget,
            $hourlyRate,
            new \DateTimeImmutable()
        );

        // Set company context
        $reflection = new \ReflectionClass($project);
        $method = $reflection->getMethod('setCompany');
        $method->setAccessible(true);
        $method->invoke($project, $company);

        return $project;
    }

    private function generateProjectCode(Company $company): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        // In production, query database for next number
        $number = 1; // Would be: max(project numbers for this company this year) + 1
        return sprintf('PROJ-%s-%03d', $year, $number);
    }
}
