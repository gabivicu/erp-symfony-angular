<?php

declare(strict_types=1);

namespace App\Projects\Controller;

use App\Projects\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Project List Controller - GET /api/projects
 * Returns all projects (filter disabled for dev / super admin).
 */
final class ProjectListController
{
    private const DEFAULT_LIMIT = 20;
    private const MAX_LIMIT = 100;

    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/projects', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $filters = $this->entityManager->getFilters();
        $wasEnabled = $filters->isEnabled('company_filter');
        if ($wasEnabled) {
            $filters->disable('company_filter');
        }

        $limit = (int) $request->query->get('limit', (string) self::DEFAULT_LIMIT);
        $limit = min(max(1, $limit), self::MAX_LIMIT);
        $offset = max(0, (int) $request->query->get('offset', '0'));

        $data = $this->projectRepository->findListData($limit, $offset);
        $count = count($data);

        // Debug: log and add debug info to response
        error_log(sprintf('Projects API: requested limit=%d, offset=%d, returned %d items', $limit, $offset, $count));
        
        // Add debug info to response headers for troubleshooting
        $response = new JsonResponse($data);
        $response->headers->set('X-Debug-Limit', (string) $limit);
        $response->headers->set('X-Debug-Offset', (string) $offset);
        $response->headers->set('X-Debug-Count', (string) $count);

        if ($wasEnabled) {
            $filters->enable('company_filter');
        }

        return $response;
    }
}
