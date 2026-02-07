<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Invoicing\Enum\InvoiceStatus;
use App\Projects\Enum\ProjectStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Dashboard Stats Controller - GET /api/dashboard
 *
 * Returns lightweight aggregated stats for the dashboard.
 */
final class DashboardStatsController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/dashboard', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('company_filter')) {
            $filters->disable('company_filter');
        }

        $now = new \DateTimeImmutable('now');
        $monthStart = $now->modify('first day of this month')->setTime(0, 0);

        // Total revenue = sum of PAID invoices paid this month (fallback to createdAt if paidAt is null).
        $qb = $this->entityManager->createQueryBuilder();
        $totalRevenue = (float) $qb
            ->select('COALESCE(SUM(i.total), 0)')
            ->from(\App\Invoicing\Entity\Invoice::class, 'i')
            ->where('i.status = :paidStatus')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('i.paidAt'),
                        $qb->expr()->gte('i.paidAt', ':monthStart')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->isNull('i.paidAt'),
                        $qb->expr()->gte('i.createdAt', ':monthStart')
                    )
                )
            )
            ->setParameter('paidStatus', InvoiceStatus::PAID->value)
            ->setParameter('monthStart', $monthStart)
            ->getQuery()
            ->getSingleScalarResult();

        $activeProjects = (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(p.id)
            FROM App\Projects\Entity\Project p
            WHERE p.status = :activeStatus
        DQL)
            ->setParameter('activeStatus', ProjectStatus::ACTIVE->value)
            ->getSingleScalarResult();

        $pendingInvoices = (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(i.id)
            FROM App\Invoicing\Entity\Invoice i
            WHERE i.status = :sentStatus
        DQL)
            ->setParameter('sentStatus', InvoiceStatus::SENT->value)
            ->getSingleScalarResult();

        $teamMembers = (int) $this->entityManager->createQuery(<<<'DQL'
            SELECT COUNT(u.id)
            FROM App\Core\Entity\User u
            WHERE u.active = true
        DQL)->getSingleScalarResult();

        $filters->enable('company_filter');

        return new JsonResponse([
            'totalRevenue' => $totalRevenue,
            'activeProjects' => $activeProjects,
            'pendingInvoices' => $pendingInvoices,
            'teamMembers' => $teamMembers,
            'generatedAt' => $now->format('c'),
        ]);
    }
}

