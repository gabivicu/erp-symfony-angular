<?php

declare(strict_types=1);

namespace App\Invoicing\Entity;

use App\Invoicing\ValueObject\InvoiceLineId;
use App\Finance\ValueObject\Money;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_lines')]
final class InvoiceLine
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'lines')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Invoice $invoice;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $unitPrice;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private string $vatRate;

    public function __construct(
        InvoiceLineId $id,
        Invoice $invoice,
        string $description,
        int $quantity,
        Money $unitPrice,
        float $vatRate
    ) {
        $this->id = $id->toString();
        $this->invoice = $invoice;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice->getAmount();
        $this->currency = $unitPrice->getCurrency();
        $this->vatRate = (string) $vatRate;
    }

    public function getId(): InvoiceLineId
    {
        return InvoiceLineId::fromString($this->id);
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getUnitPrice(): Money
    {
        return Money::fromAmount($this->unitPrice, $this->currency);
    }

    public function getVatRate(): float
    {
        return (float) $this->vatRate;
    }
}
