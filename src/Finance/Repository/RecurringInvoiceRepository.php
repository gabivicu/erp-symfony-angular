<?php

declare(strict_types=1);

namespace App\Finance\Repository;

use App\Finance\Entity\RecurringInvoice;
use App\Finance\ValueObject\RecurringInvoiceId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class RecurringInvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecurringInvoice::class);
    }

    public function save(RecurringInvoice $recurringInvoice): void
    {
        $this->getEntityManager()->persist($recurringInvoice);
        $this->getEntityManager()->flush();
    }

    public function findById(RecurringInvoiceId $id): ?RecurringInvoice
    {
        return $this->find($id->toString());
    }

    /**
     * @return RecurringInvoice[]
     */
    public function findDueForGeneration(): array
    {
        $qb = $this->createQueryBuilder('ri');
        
        return $qb
            ->where('ri.isActive = :isActive')
            ->andWhere('ri.nextGenerationDate <= :now')
            ->andWhere('(ri.endDate IS NULL OR ri.endDate >= :now)')
            ->setParameter('isActive', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RecurringInvoice[]
     */
    public function findAll(): array
    {
        return parent::findAll();
    }

    public function remove(RecurringInvoice $recurringInvoice): void
    {
        $this->getEntityManager()->remove($recurringInvoice);
        $this->getEntityManager()->flush();
    }
}
