<?php

declare(strict_types=1);

namespace App\Projects\Controller;

use App\Projects\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Task List Controller - MVC Pattern
 *
 * Exposes GET /api/tasks for the frontend task list.
 * Uses array hydration (no full entities) to avoid memory exhaustion.
 */
final class TaskListController
{
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/tasks', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Temporarily disable company filter so all tasks are returned (dev / single-tenant).
        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('company_filter')) {
            $filters->disable('company_filter');
        }

        $limit = (int) $request->query->get('limit', (string) self::DEFAULT_LIMIT);
        $limit = min(max(1, $limit), self::MAX_LIMIT);
        $offset = max(0, (int) $request->query->get('offset', '0'));

        $data = $this->taskRepository->findListData($limit, $offset);

        $filters->enable('company_filter');

        return new JsonResponse($data);
    }
}
