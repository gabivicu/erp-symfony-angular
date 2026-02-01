<?php

declare(strict_types=1);

namespace App\CRM\Repository;

use App\CRM\Entity\Estimate;
use App\CRM\ValueObject\EstimateId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class EstimateRepository extends ServiceEntityRepository
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
