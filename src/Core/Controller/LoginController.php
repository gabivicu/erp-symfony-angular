<?php

declare(strict_types=1);

namespace App\Core\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Login route - handled by Security json_login firewall.
 * This controller only provides the route; authentication is handled by the firewall.
 */
final class LoginController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST', 'GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        // This should never be reached for POST requests (handled by firewall)
        // Only GET requests will reach here
        if ($request->getMethod() === 'GET') {
            return new JsonResponse([
                'message' => 'Use POST with JSON body: {"username":"email","password":"..."}',
            ], 405);
        }

        // POST requests are handled by json_login firewall before reaching here
        return new JsonResponse([
            'error' => 'Authentication failed',
        ], 401);
    }
}
