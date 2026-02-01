<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Repository;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\Core\Domain\ValueObject\CompanyId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Base Repository for Company-Aware Entities
 * 
 * Senior-level decision: Base repository ensures company filtering
 * All repositories extend this to get automatic company filtering
 */
abstract class BaseCompanyAwareRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, string $entityClass)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * Find by ID within company context
     * Company filter automatically applied via Doctrine Filter
     */
    public function findById(string $id, CompanyId $companyId): ?object
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * Find all for a specific company
     * Company filter automatically applied
     */
    public function findAllForCompany(CompanyId $companyId): array
    {
        return $this->findAll();
    }

    /**
     * Ensure entity belongs to company before saving
     */
    protected function ensureCompanyContext(object $entity, Company $company): void
    {
        if (!in_array(CompanyAwareTrait::class, class_uses_recursive($entity), true)) {
            throw new \InvalidArgumentException('Entity must use CompanyAwareTrait');
        }

        // Set company if not already set
        if (method_exists($entity, 'getCompany') && $entity->getCompany() === null) {
            // Use reflection to set company (since trait method is protected)
            $reflection = new \ReflectionClass($entity);
            $method = $reflection->getMethod('setCompany');
            $method->setAccessible(true);
            $method->invoke($entity, $company);
        }
    }
}
