<?php

declare(strict_types=1);

namespace App\CRM\ValueObject;

use App\Finance\ValueObject\Money;

final class EstimateLine
{
    public function __construct(
        private readonly string $description,
        private readonly int $quantity,
        private readonly Money $unitPrice,
        private readonly float $vatRate
    ) {
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
        return $this->unitPrice;
    }

    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    public function getSubtotal(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unitPrice' => [
                'amount' => $this->unitPrice->getAmount(),
                'currency' => $this->unitPrice->getCurrency(),
            ],
            'vatRate' => $this->vatRate,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['description'],
            $data['quantity'],
            Money::fromAmount($data['unitPrice']['amount'], $data['unitPrice']['currency']),
            $data['vatRate']
        );
    }
}
