<?php

declare(strict_types=1);

namespace App\CRM\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\CRM\Enum\EstimateStatus;
use App\CRM\ValueObject\EstimateId;
use App\CRM\ValueObject\EstimateLine;
use App\Finance\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Estimate Entity (Quote)
 * 
 * Business Rule: Accepted Estimate automatically converts to Project + Invoice
 */
#[ORM\Entity]
#[ORM\Table(name: 'estimates')]
final class Estimate
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: Lead::class)]
    #[ORM\JoinColumn(name: 'lead_id', referencedColumnName: 'id', nullable: false)]
    private Lead $lead;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $estimateNumber;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $subtotal;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $totalVat;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $total;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $validityDays = 30;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $acceptedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    /** @var EstimateLine[] */
    #[ORM\Column(type: 'json')]
    private array $lines = [];

    public function __construct(
        EstimateId $id,
        Lead $lead,
        string $estimateNumber,
        string $currency
    ) {
        $this->id = $id->toString();
        $this->lead = $lead;
        $this->estimateNumber = $estimateNumber;
        $this->currency = $currency;
        $this->status = EstimateStatus::DRAFT->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $subtotalMoney = Money::zero($currency);
        $this->subtotal = $subtotalMoney->getAmount();
        $totalVatMoney = Money::zero($currency);
        $this->totalVat = $totalVatMoney->getAmount();
        $totalMoney = Money::zero($currency);
        $this->total = $totalMoney->getAmount();
        
        if ($this->validityDays !== null) {
            $this->expiresAt = (new DateTimeImmutable())->modify("+{$this->validityDays} days");
        }
    }

    public function getId(): EstimateId
    {
        return EstimateId::fromString($this->id);
    }

    public function getLead(): Lead
    {
        return $this->lead;
    }

    public function getEstimateNumber(): string
    {
        return $this->estimateNumber;
    }

    public function getStatus(): EstimateStatus
    {
        return EstimateStatus::from($this->status);
    }

    public function getTotal(): Money
    {
        return Money::fromAmount($this->total, $this->currency);
    }

    public function accept(): void
    {
        if ($this->status !== EstimateStatus::SENT->value) {
            throw new \DomainException('Only sent estimates can be accepted');
        }

        if ($this->expiresAt !== null && $this->expiresAt < new DateTimeImmutable()) {
            throw new \DomainException('Estimate has expired');
        }

        $this->status = EstimateStatus::ACCEPTED->value;
        $this->acceptedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isAccepted(): bool
    {
        return $this->status === EstimateStatus::ACCEPTED->value;
    }

    public function addLine(EstimateLine $line): void
    {
        if ($this->status !== EstimateStatus::DRAFT->value) {
            throw new \DomainException('Cannot modify non-draft estimate');
        }

        $this->lines[] = $line->toArray();
        $this->recalculateTotals();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return EstimateLine[]
     */
    public function getLines(): array
    {
        return array_map(
            fn(array $lineData) => EstimateLine::fromArray($lineData),
            $this->lines
        );
    }

    private function recalculateTotals(): void
    {
        $subtotal = Money::zero($this->currency);
        $totalVat = Money::zero($this->currency);

        foreach ($this->lines as $lineData) {
            $line = EstimateLine::fromArray($lineData);
            $lineSubtotal = $line->getSubtotal();
            $subtotal = $subtotal->add($lineSubtotal);
            $vatAmount = $lineSubtotal->multiply((int)($line->getVatRate() * 100))->divide(10000);
            $totalVat = $totalVat->add($vatAmount);
        }

        $this->subtotal = $subtotal->getAmount();
        $this->totalVat = $totalVat->getAmount();
        $this->total = $subtotal->add($totalVat)->getAmount();
    }
}
