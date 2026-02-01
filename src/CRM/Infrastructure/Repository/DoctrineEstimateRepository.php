<?php

declare(strict_types=1);

namespace App\CRM\Infrastructure\Repository;

use App\CRM\Domain\Entity\Estimate;
use App\CRM\Domain\Repository\EstimateRepositoryInterface;
use App\CRM\Domain\ValueObject\EstimateId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineEstimateRepository extends ServiceEntityRepository implements EstimateRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Estimate::class);
    }

    public function findById(EstimateId $id): ?Estimate
    {
        return $this->find($id->toString());
    }

    public function save(Estimate $estimate): void
    {
        $this->getEntityManager()->persist($estimate);
        $this->getEntityManager()->flush();
    }

    public function findAll(): array
    {
        return parent::findAll();
    }
}
