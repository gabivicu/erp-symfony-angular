<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Entity;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\Invoicing\Domain\Enum\InvoiceStatus;
use App\Invoicing\Domain\Exception\InvoiceCannotBeDeletedException;
use App\Invoicing\Domain\Exception\InvoiceStatusTransitionException;
use App\Invoicing\Domain\ValueObject\InvoiceId;
use App\Finance\Domain\ValueObject\Money;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Rich Domain Model: Invoice Aggregate Root
 */
#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
final class Invoice
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    private Client $client;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $invoiceNumber;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $subtotal;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $totalVat;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $total;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    /** @var InvoiceLine[] */
    #[ORM\OneToMany(targetEntity: InvoiceLine::class, mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private array $lines = [];

    private function __construct(
        InvoiceId $id,
        Client $client,
        string $invoiceNumber,
        string $currency
    ) {
        $this->id = $id->toString();
        $this->client = $client;
        $this->invoiceNumber = $invoiceNumber;
        $this->currency = $currency;
        $this->status = InvoiceStatus::DRAFT->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $subtotalMoney = Money::zero($currency);
        $this->subtotal = $subtotalMoney->getAmount();
        $totalVatMoney = Money::zero($currency);
        $this->totalVat = $totalVatMoney->getAmount();
        $totalMoney = Money::zero($currency);
        $this->total = $totalMoney->getAmount();
    }

    public static function create(
        InvoiceId $id,
        Client $client,
        string $invoiceNumber,
        string $currency
    ): self {
        return new self($id, $client, $invoiceNumber, $currency);
    }

    public function getId(): InvoiceId
    {
        return InvoiceId::fromString($this->id);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getStatus(): InvoiceStatus
    {
        return InvoiceStatus::from($this->status);
    }

    public function getInvoiceNumber(): string
    {
        return $this->invoiceNumber;
    }

    public function getTotal(): Money
    {
        return Money::fromAmount($this->total, $this->currency);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function finalize(): void
    {
        if ($this->status !== InvoiceStatus::DRAFT->value) {
            throw InvoiceStatusTransitionException::canOnlyFinalizeDraft();
        }

        if (empty($this->lines)) {
            throw new \DomainException('Cannot finalize an invoice without line items');
        }

        $this->status = InvoiceStatus::SENT->value;
        $this->sentAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function canBeDeleted(): bool
    {
        return $this->status === InvoiceStatus::DRAFT->value;
    }
}
