<?php

declare(strict_types=1);

namespace App\Finance\Repository;

use App\Finance\Entity\Expense;
use App\Finance\ValueObject\ExpenseId;
use App\Projects\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ExpenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function findById(ExpenseId $id): ?Expense
    {
        return $this->find($id->toString());
    }

    /**
     * List data for API without full entity hydration (memory-efficient).
     *
     * @return list<array{id: string, description: string, amount: float, currency: string, status: string, expenseDate: string, projectName: string|null}>
     */
    public function findListData(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('e.id', 'e.description', 'e.amount', 'e.currency', 'e.status', 'e.expenseDate', 'p.name AS projectName')
            ->leftJoin('e.project', 'p')
            ->orderBy('e.updatedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            $expenseDate = $row['expenseDate'] ?? null;
            if ($expenseDate instanceof \DateTimeInterface) {
                $expenseDate = $expenseDate->format('Y-m-d\TH:i:sP');
            }
            return [
                'id' => (string) $row['id'],
                'description' => (string) $row['description'],
                'amount' => (float) $row['amount'],
                'currency' => (string) $row['currency'],
                'status' => (string) $row['status'],
                'expenseDate' => (string) $expenseDate,
                'projectName' => isset($row['projectName']) ? (string) $row['projectName'] : null,
            ];
        }, $rows);
    }

    public function findByProject(Project $project): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getResult();
    }

    public function save(Expense $expense): void
    {
        $this->getEntityManager()->persist($expense);
        $this->getEntityManager()->flush();
    }

    public function remove(Expense $expense): void
    {
        $this->getEntityManager()->remove($expense);
        $this->getEntityManager()->flush();
    }
}
