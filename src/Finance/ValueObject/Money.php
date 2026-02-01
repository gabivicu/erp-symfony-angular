<?php

declare(strict_types=1);

namespace App\Finance\ValueObject;

/**
 * Money Value Object
 * Reused from Invoicing module
 */
final class Money
{
    private function __construct(
        private readonly int $amountInCents,
        private readonly string $currency
    ) {
        if ($amountInCents < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }

        if (strlen($currency) !== 3) {
            throw new \InvalidArgumentException('Currency must be a 3-letter ISO code');
        }
    }

    public static function fromCents(int $amountInCents, string $currency): self
    {
        return new self($amountInCents, $currency);
    }

    public static function fromFloat(float $amount, string $currency): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public static function zero(string $currency): self
    {
        return new self(0, $currency);
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function getAmount(): float
    {
        return $this->amountInCents / 100.0;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->amountInCents * $multiplier, $this->currency);
    }

    public function divide(int $divisor): self
    {
        if ($divisor === 0) {
            throw new \InvalidArgumentException('Cannot divide by zero');
        }
        return new self((int)round($this->amountInCents / $divisor), $this->currency);
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                sprintf('Currency mismatch: %s vs %s', $this->currency, $other->currency)
            );
        }
    }
}
