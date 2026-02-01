<?php

declare(strict_types=1);

namespace App\Core\Infrastructure\Doctrine\Subscriber;

use App\Core\Domain\ValueObject\CompanyId;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Company Filter Subscriber
 * 
 * Sets the company_id filter parameter based on current user's company
 * Must be called before any queries execute
 */
final class CompanyFilterSubscriber implements EventSubscriber
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad,
        ];
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entityManager = $args->getEntityManager();
        $this->enableCompanyFilter($entityManager);
    }

    public function enableCompanyFilter(EntityManagerInterface $entityManager): void
    {
        $user = $this->security->getUser();

        if ($user === null) {
            return;
        }

        // Get company from user (assuming User entity has getCompany() method)
        // In production, this would be: $companyId = $user->getCompany()->getId();
        // For now, we'll set it from a method or property
        
        if (method_exists($user, 'getCompanyId')) {
            $companyId = $user->getCompanyId();
            
            $filter = $entityManager->getFilters()->enable('company_filter');
            $filter->setParameter('company_id', $companyId->toString());
        }
    }
}
