<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Doctrine\Filter;

use App\Core\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets company_id for the Doctrine CompanyFilter on each request.
 * Without this, the filter returns 1=0 and no company-scoped entities are returned.
 *
 * Company context: X-Company-Id header, or first company in DB (dev fallback).
 */
final class CompanyFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        
        // Skip company filter for CORS preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            return;
        }

        // Skip company filter for login endpoint (and any variants like /api/login/)
        $path = $request->getPathInfo();
        if (\str_starts_with($path, '/api/login')) {
            return;
        }

        $companyId = $request->headers->get('X-Company-Id');

        if ($companyId === null || $companyId === '') {
            try {
                $companyId = $this->getFirstCompanyId();
            } catch (\Throwable $e) {
                // If database is not ready or companies table doesn't exist, skip filter
                return;
            }
        }

        if ($companyId !== null) {
            try {
                $filter = $this->entityManager->getFilters()->enable('company_filter');
                $filter->setParameter('company_id', $this->entityManager->getConnection()->quote($companyId));
            } catch (\Throwable $e) {
                // If filter cannot be enabled, skip it
                return;
            }
        }
    }

    private function getFirstCompanyId(): ?string
    {
        try {
            $company = $this->entityManager->getRepository(Company::class)->findOneBy([], ['id' => 'ASC']);
            return $company !== null ? $company->getId()->toString() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
