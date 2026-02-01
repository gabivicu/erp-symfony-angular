<?php

declare(strict_types=1);

namespace App\Invoicing\Controller;

use App\Invoicing\Repository\InvoiceRepository;
use App\Invoicing\ValueObject\InvoiceId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Finalize Invoice Controller - MVC Pattern
 * 
 * Controller în pattern-ul MVC:
 * - Primește HTTP Request
 * - Lucrează direct cu Model (Invoice Entity) prin Repository
 * - Returnează JSON Response (View)
 */
final class FinalizeInvoiceController
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository
    ) {
    }

    /**
     * Finalizează o factură (schimbă status-ul din DRAFT în SENT)
     * 
     * Flow MVC:
     * 1. HTTP Request → Routing → Controller
     * 2. Controller → Repository → Model (Invoice Entity)
     * 3. Controller → JSON Response (View)
     */
    #[Route('/api/invoices/{id}/finalize', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        try {
            // 1. Controller primește request-ul și găsește entitatea prin Repository
            $invoice = $this->invoiceRepository->findById(
                InvoiceId::fromString($id)
            );

            if ($invoice === null) {
                return new JsonResponse(
                    [
                        'status' => 'error',
                        'message' => 'Invoice not found'
                    ],
                    404
                );
            }

            // 2. Controller apelează metoda de domeniu direct pe Model
            $invoice->finalize();

            // 3. Controller salvează Model-ul prin Repository
            $this->invoiceRepository->save($invoice);
            
            // 4. Controller returnează View (JSON Response)
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Invoice finalized successfully'
            ], 200);
            
        } catch (\InvalidArgumentException $e) {
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
