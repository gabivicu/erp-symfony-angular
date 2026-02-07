<?php

declare(strict_types=1);

namespace App\Projects\Repository;

use App\Projects\Entity\Task;
use App\Projects\ValueObject\TaskId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findById(TaskId $id): ?Task
    {
        return $this->find($id->toString());
    }

    /**
     * @return list<Task>
     */
    public function findAllOrderedByUpdatedAt(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.project', 'p')
            ->addSelect('p')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * List data for API without full entity hydration (memory-efficient).
     *
     * @return list<array{id: string, title: string, status: string, projectId: string, projectName: string}>
     */
    public function findListData(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('t.id', 't.title', 't.status', 'p.id AS projectId', 'p.name AS projectName')
            ->leftJoin('t.project', 'p')
            ->orderBy('t.updatedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            return [
                'id' => (string) $row['id'],
                'title' => (string) $row['title'],
                'status' => (string) $row['status'],
                'projectId' => (string) $row['projectId'],
                'projectName' => (string) ($row['projectName'] ?? ''),
            ];
        }, $rows);
    }

    public function save(Task $task): void
    {
        $this->getEntityManager()->persist($task);
        $this->getEntityManager()->flush();
    }

    public function remove(Task $task): void
    {
        $this->getEntityManager()->remove($task);
        $this->getEntityManager()->flush();
    }
}
