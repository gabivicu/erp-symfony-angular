<?php

declare(strict_types=1);

namespace App\CRM\Domain\Repository;

use App\CRM\Domain\Entity\Estimate;
use App\CRM\Domain\ValueObject\EstimateId;

/**
 * Domain Repository Interface (Port)
 * Interface în Domain layer, implementare în Infrastructure
 */
interface EstimateRepositoryInterface
{
    public function findById(EstimateId $id): ?Estimate;

    public function save(Estimate $estimate): void;

    /**
     * @return Estimate[]
     */
    public function findAll(): array;
}
