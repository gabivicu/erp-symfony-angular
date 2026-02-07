<?php

declare(strict_types=1);

namespace App\Finance\Controller;

use App\Finance\Repository\RecurringInvoiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Recurring Invoice List Controller - GET /api/recurring-invoices
 */
final class RecurringInvoiceListController
{
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly RecurringInvoiceRepository $recurringInvoiceRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/recurring-invoices', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('company_filter')) {
            $filters->disable('company_filter');
        }

        $limit = (int) $request->query->get('limit', (string) self::DEFAULT_LIMIT);
        $limit = min(max(1, $limit), self::MAX_LIMIT);
        $offset = max(0, (int) $request->query->get('offset', '0'));

        $data = $this->recurringInvoiceRepository->findListData($limit, $offset);

        $filters->enable('company_filter');

        return new JsonResponse($data);
    }
}
