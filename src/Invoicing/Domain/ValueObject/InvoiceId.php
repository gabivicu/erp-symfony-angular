<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\ValueObject;

final class InvoiceId
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Invoice ID cannot be empty');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function generate(): self
    {
        return new self(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
