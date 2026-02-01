<?php

declare(strict_types=1);

namespace App\Core\Domain\ValueObject;

final class CompanyId
{
    private function __construct(
        private readonly string $value
    ) {
        if (empty($value)) {
            throw new \InvalidArgumentException('Company ID cannot be empty');
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

    public function equals(CompanyId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
