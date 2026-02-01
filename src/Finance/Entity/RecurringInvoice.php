<?php

declare(strict_types=1);

namespace App\Finance\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\Finance\ValueObject\Money;
use App\Finance\ValueObject\RecurringInvoiceId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * RecurringInvoice Entity
 * 
 * Automatically generates invoices based on schedule
 * Processed by Symfony Scheduler
 */
#[ORM\Entity]
#[ORM\Table(name: 'recurring_invoices')]
final class RecurringInvoice
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'string', length: 50)]
    private string $frequency; // daily, weekly, monthly, yearly

    #[ORM\Column(type: 'integer')]
    private int $dayOfMonth; // For monthly: 1-31

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $dayOfWeek = null; // For weekly: monday, tuesday, etc.

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastGeneratedAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $nextGenerationDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        RecurringInvoiceId $id,
        string $frequency,
        Money $amount,
        string $description,
        DateTimeImmutable $startDate
    ) {
        $this->id = $id->toString();
        $this->frequency = $frequency;
        $this->amount = $amount->getAmount();
        $this->currency = $amount->getCurrency();
        $this->description = $description;
        $this->startDate = $startDate;
        $this->isActive = true;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->calculateNextGenerationDate();
    }

    public function getId(): RecurringInvoiceId
    {
        return RecurringInvoiceId::fromString($this->id);
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function getNextGenerationDate(): DateTimeImmutable
    {
        return $this->nextGenerationDate;
    }

    public function shouldGenerate(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->endDate !== null && $this->endDate < new DateTimeImmutable()) {
            return false;
        }

        return $this->nextGenerationDate <= new DateTimeImmutable();
    }

    public function markAsGenerated(): void
    {
        $this->lastGeneratedAt = new DateTimeImmutable();
        $this->calculateNextGenerationDate();
        $this->updatedAt = new DateTimeImmutable();
    }

    private function calculateNextGenerationDate(): void
    {
        $now = new DateTimeImmutable();
        
        $this->nextGenerationDate = match ($this->frequency) {
            'daily' => $now->modify('+1 day'),
            'weekly' => $now->modify('+1 week'),
            'monthly' => $now->modify('+1 month'),
            'yearly' => $now->modify('+1 year'),
            default => throw new \InvalidArgumentException("Invalid frequency: {$this->frequency}"),
        };
    }
}
