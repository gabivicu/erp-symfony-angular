<?php

declare(strict_types=1);

namespace App\CRM\ValueObject;

final class EstimateId
{
    private function __construct(
        private readonly string $value
    ) {
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
}
