<?php

declare(strict_types=1);

namespace App\Projects\Repository;

use App\Projects\Entity\TimeLog;
use App\Projects\ValueObject\TimeLogId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TimeLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeLog::class);
    }

    public function findById(TimeLogId $id): ?TimeLog
    {
        return $this->find($id->toString());
    }

    /**
     * List data for API without full entity hydration (memory-efficient).
     *
     * @return list<array{id: string, description: string, hours: float, loggedDate: string, projectId: string, projectName: string, taskId: string|null, taskTitle: string|null}>
     */
    public function findListData(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->createQueryBuilder('tl')
            ->select(
                'tl.id',
                'tl.description',
                'tl.hours',
                'tl.loggedDate',
                'p.id AS projectId',
                'p.name AS projectName',
                't.id AS taskId',
                't.title AS taskTitle'
            )
            ->innerJoin('tl.project', 'p')
            ->leftJoin('tl.task', 't')
            ->orderBy('tl.loggedDate', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            $loggedDate = $row['loggedDate'] ?? null;
            if ($loggedDate instanceof \DateTimeInterface) {
                $loggedDate = $loggedDate->format('Y-m-d\TH:i:sP');
            }
            return [
                'id' => (string) $row['id'],
                'description' => (string) $row['description'],
                'hours' => (float) $row['hours'],
                'loggedDate' => (string) $loggedDate,
                'projectId' => (string) ($row['projectId'] ?? ''),
                'projectName' => (string) ($row['projectName'] ?? ''),
                'taskId' => isset($row['taskId']) ? (string) $row['taskId'] : null,
                'taskTitle' => isset($row['taskTitle']) ? (string) $row['taskTitle'] : null,
            ];
        }, $rows);
    }
}
