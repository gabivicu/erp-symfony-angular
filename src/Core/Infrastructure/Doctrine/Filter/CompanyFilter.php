<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Doctrine\Filter;

use App\Core\Trait\CompanyAwareTrait;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use ReflectionClass;

/**
 * Global Doctrine Filter for Multi-tenancy
 * 
 * Senior-level decision: Automatic data isolation
 * Automatically adds WHERE company_id = X to all queries
 * Prevents data leakage between companies
 * 
 * Usage: Enable in config/packages/doctrine.yaml
 */
final class CompanyFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        // Check if entity uses CompanyAwareTrait
        if (!$this->isCompanyAware($targetEntity->getName())) {
            return '';
        }

        // Get company_id from filter parameters (set by CompanyFilterSubscriber)
        $companyId = $this->getParameter('company_id');

        if ($companyId === null) {
            // If no company context, deny access (security by default)
            return '1 = 0';
        }

        return sprintf('%s.company_id = %s', $targetTableAlias, $companyId);
    }

    private function isCompanyAware(string $entityClass): bool
    {
        if (!class_exists($entityClass)) {
            return false;
        }

        $reflection = new ReflectionClass($entityClass);
        $traits = $reflection->getTraitNames();

        return in_array(CompanyAwareTrait::class, $traits, true);
    }
}
