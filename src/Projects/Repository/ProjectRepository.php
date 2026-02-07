<?php

declare(strict_types=1);

namespace App\Projects\Repository;

use App\Projects\Entity\Project;
use App\Projects\ValueObject\ProjectId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findById(ProjectId $id): ?Project
    {
        return $this->find($id->toString());
    }

    /**
     * List data for API without full entity hydration (memory-efficient).
     *
     * @return list<array{id: string, name: string, code: string, status: string, companyId: string, companyName: string}>
     */
    public function findListData(int $limit = 20, int $offset = 0): array
    {
        // Use native SQL to bypass any Doctrine filters that might interfere
        $conn = $this->getEntityManager()->getConnection();
        
        // PostgreSQL requires LIMIT/OFFSET to be integers, not parameters
        $limit = max(1, min(100, $limit)); // Ensure valid range
        $offset = max(0, $offset);
        
        // Use prepared statement with explicit parameter binding to avoid sprintf issues
        $sql = '
            SELECT 
                p.id, 
                p.name, 
                p.code, 
                p.status, 
                c.id AS company_id, 
                c.name AS company_name
            FROM projects p
            LEFT JOIN companies c ON p.company_id = c.id
            ORDER BY p.updated_at DESC
            LIMIT ? OFFSET ?
        ';
        
        error_log(sprintf('Projects SQL Query (before binding): %s', $sql));
        error_log(sprintf('Projects SQL Parameters: limit=%d (type: %s), offset=%d (type: %s)', $limit, gettype($limit), $offset, gettype($offset)));
        
        $result = $conn->executeQuery($sql, [$limit, $offset], [\PDO::PARAM_INT, \PDO::PARAM_INT]);
        $rows = $result->fetchAllAssociative();
        
        error_log(sprintf('Projects SQL: limit=%d, offset=%d, returned %d rows', $limit, $offset, count($rows)));
        
        // Also check total count
        $countSql = 'SELECT COUNT(*) as total FROM projects';
        $totalResult = $conn->executeQuery($countSql);
        $total = $totalResult->fetchOne();
        error_log(sprintf('Projects total count in DB: %d', $total));

        return array_map(static function (array $row): array {
            return [
                'id' => (string) $row['id'],
                'name' => (string) $row['name'],
                'code' => (string) $row['code'],
                'status' => (string) $row['status'],
                'companyId' => (string) ($row['company_id'] ?? ''),
                'companyName' => (string) ($row['company_name'] ?? ''),
            ];
        }, $rows);
    }
}
