<?php

declare(strict_types=1);

namespace App\CRM\Infrastructure\Api\Controller;

use App\CRM\Application\Service\EstimateConversionService;
use App\CRM\Domain\Repository\EstimateRepositoryInterface;
use App\CRM\Domain\ValueObject\EstimateId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Convert Estimate Controller
 * 
 * CONTROLLER în pattern-ul MVC:
 * - Primește HTTP Request pentru conversia Estimate → Project + Invoice
 * - Apelează Service (Model) pentru logica de business
 * - Returnează JSON Response (View)
 * 
 * Notă: AbstractController este din Symfony Framework Bundle.
 * După 'composer install', poți extinde AbstractController pentru funcționalități suplimentare.
 */
final class ConvertEstimateController
{
    public function __construct(
        private readonly EstimateConversionService $conversionService,
        private readonly EstimateRepositoryInterface $estimateRepository
    ) {
    }

    /**
     * Convertește un Estimate acceptat în Project + Invoice
     * 
     * Flow MVC:
     * 1. HTTP Request → Controller
     * 2. Controller → Repository (găsește Estimate - Model)
     * 3. Controller → Service (logica de business - Model)
     * 4. Controller → JSON Response (View)
     */
    #[Route('/api/estimates/{id}/convert', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        // 1. Controller primește request-ul și găsește Estimate (Model)
        $estimate = $this->estimateRepository->findById(EstimateId::fromString($id));
        
        if (!$estimate) {
            return new JsonResponse(
                ['status' => 'error', 'message' => 'Estimate not found'],
                404
            );
        }

        // 2. Controller extrage parametri din request
        $data = json_decode($request->getContent(), true);
        $depositPercentage = $data['depositPercentage'] ?? null;
        $depositPercentage = $depositPercentage ? (float) $depositPercentage : null;

        try {
            // 3. Controller apelează Service (Model) pentru logica de business
            $result = $this->conversionService->convertEstimateToProject(
                $estimate,
                $depositPercentage
            );

            // 4. Controller returnează View (JSON Response)
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Estimate converted to project successfully',
                'data' => [
                    'project' => [
                        'id' => $result->project->getId()->toString(),
                        'name' => $result->project->getName(),
                        'code' => $result->project->getCode(),
                    ],
                    'invoice' => [
                        'id' => $result->invoice->getId()->toString(),
                        'total' => $result->invoice->getTotal()->getAmount(),
                        'currency' => $result->invoice->getCurrency(),
                    ],
                    'lead' => [
                        'id' => $result->lead->getId()->toString(),
                        'status' => $result->lead->getStatus()->value,
                    ],
                ],
            ], 201);
            
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['status' => 'error', 'message' => $e->getMessage()],
                400
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['status' => 'error', 'message' => 'An error occurred during conversion'],
                500
            );
        }
    }
}
