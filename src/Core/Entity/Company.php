<?php

declare(strict_types=1);

namespace App\Core\Entity;

use App\Core\ValueObject\CompanyId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Company Entity - Multi-tenancy Root
 * 
 * Senior-level decision: Single Database Multi-tenancy
 * Every entity belongs to a Company for data isolation
 */
#[ORM\Entity]
#[ORM\Table(name: 'companies')]
final class Company
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $subdomain;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status; // active, suspended, cancelled

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeCustomerId = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $stripeSubscriptionId = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $subscriptionPlan; // starter, pro

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $trialEndsAt = null;

    public function __construct(
        CompanyId $id,
        string $name,
        string $subdomain,
        string $subscriptionPlan = 'starter'
    ) {
        $this->id = $id->toString();
        $this->name = $name;
        $this->subdomain = $subdomain;
        $this->status = 'active';
        $this->subscriptionPlan = $subscriptionPlan;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): CompanyId
    {
        return CompanyId::fromString($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSubscriptionPlan(): string
    {
        return $this->subscriptionPlan;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function upgradeToPro(): void
    {
        $this->subscriptionPlan = 'pro';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function suspend(): void
    {
        $this->status = 'suspended';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->updatedAt = new DateTimeImmutable();
    }
}
