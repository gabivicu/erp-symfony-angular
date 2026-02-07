<?php

declare(strict_types=1);

namespace App\CRM\Controller;

use App\CRM\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Lead List Controller - GET /api/leads
 */
final class LeadListController
{
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly LeadRepository $leadRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/leads', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('company_filter')) {
            $filters->disable('company_filter');
        }

        $limit = (int) $request->query->get('limit', (string) self::DEFAULT_LIMIT);
        $limit = min(max(1, $limit), self::MAX_LIMIT);
        $offset = max(0, (int) $request->query->get('offset', '0'));

        $data = $this->leadRepository->findListData($limit, $offset);

        $filters->enable('company_filter');

        return new JsonResponse($data);
    }
}
