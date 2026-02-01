<?php

declare(strict_types=1);

namespace App\Invoicing\Infrastructure\Api\Controller;

use App\Invoicing\Application\Command\FinalizeInvoiceCommand;
use App\Invoicing\Application\CommandHandler\FinalizeInvoiceHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Finalize Invoice Controller
 * 
 * CONTROLLER în pattern-ul MVC:
 * - Primește HTTP Request
 * - Validează permisiunile
 * - Apelează Handler (care lucrează cu Model)
 * - Returnează JSON Response (View)
 * 
 * Notă: AbstractController este din Symfony Framework Bundle.
 * După 'composer install', poți extinde AbstractController pentru funcționalități suplimentare.
 */
final class FinalizeInvoiceController
{
    public function __construct(
        private readonly FinalizeInvoiceHandler $handler
    ) {
    }

    /**
     * Finalizează o factură (schimbă status-ul din DRAFT în SENT)
     * 
     * Flow MVC:
     * 1. HTTP Request → Routing → Controller
     * 2. Controller → Handler → Model (Invoice Entity)
     * 3. Controller → JSON Response (View)
     */
    #[Route('/api/invoices/{id}/finalize', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        // 1. Controller primește request-ul și creează Command
        // Notă: În producție, userId ar veni din token JWT sau sesiune
        $command = new FinalizeInvoiceCommand(
            $id,
            'user-id-from-token' // În producție: $this->getUser()?->getUserIdentifier()
        );

        try {
            // 2. Controller apelează Handler (care lucrează cu Model)
            ($this->handler)($command);
            
            // 3. Controller returnează View (JSON Response)
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Invoice finalized successfully'
            ], 200);
            
        } catch (\InvalidArgumentException $e) {
            // 4. Controller gestionează erorile și returnează View (JSON Error Response)
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ],
                400
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => 'An error occurred while finalizing the invoice'
                ],
                500
            );
        }
    }
}
