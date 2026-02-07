<?php

declare(strict_types=1);

namespace App\CRM\Repository;

use App\CRM\Entity\Lead;
use App\CRM\ValueObject\LeadId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class LeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lead::class);
    }

    public function findById(LeadId $id): ?Lead
    {
        return $this->find($id->toString());
    }

    /**
     * List data for API without full entity hydration (memory-efficient).
     *
     * @return list<array{id: string, name: string, email: string, companyName: string, status: string, createdAt: string}>
     */
    public function findListData(int $limit = 20, int $offset = 0): array
    {
        $rows = $this->createQueryBuilder('l')
            ->select('l.id', 'l.name', 'l.email', 'l.companyName', 'l.status', 'l.createdAt')
            ->orderBy('l.updatedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        return array_map(static function (array $row): array {
            $createdAt = $row['createdAt'] ?? null;
            if ($createdAt instanceof \DateTimeInterface) {
                $createdAt = $createdAt->format('Y-m-d\TH:i:sP');
            }
            return [
                'id' => (string) $row['id'],
                'name' => (string) $row['name'],
                'email' => (string) $row['email'],
                'companyName' => (string) $row['companyName'],
                'status' => (string) $row['status'],
                'createdAt' => (string) $createdAt,
            ];
        }, $rows);
    }
}
