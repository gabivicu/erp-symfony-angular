<?php

declare(strict_types=1);

namespace App\Core\Domain\Trait;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\ValueObject\CompanyId;

/**
 * CompanyAwareTrait
 * 
 * Senior-level decision: Trait-based multi-tenancy
 * All entities that need company isolation use this trait
 * Ensures consistent company_id column across all tables
 */
trait CompanyAwareTrait
{
    private Company $company;

    public function getCompany(): Company
    {
        return $this->company;
    }

    public function getCompanyId(): CompanyId
    {
        return $this->company->getId();
    }

    public function setCompany(Company $company): void
    {
        $this->company = $company;
    }
}
