<?php

declare(strict_types=1);

namespace App\Finance\Domain\Entity;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\Finance\Domain\Enum\ExpenseStatus;
use App\Finance\Domain\ValueObject\ExpenseId;
use App\Finance\Domain\ValueObject\Money;
use App\Projects\Domain\Entity\Project;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Expense Entity
 * 
 * Can be linked to Projects for profitability calculation
 */
#[ORM\Entity]
#[ORM\Table(name: 'expenses')]
final class Expense
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Project $project = null;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $amount;

    #[ORM\Column(type: 'string', length: 3)]
    private string $currency;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $expenseDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private ?string $receiptPath = null;

    public function __construct(
        ExpenseId $id,
        string $description,
        Money $amount,
        DateTimeImmutable $expenseDate
    ) {
        $this->id = $id->toString();
        $this->description = $description;
        $this->amount = $amount->getAmount();
        $this->currency = $amount->getCurrency();
        $this->expenseDate = $expenseDate;
        $this->status = ExpenseStatus::PENDING->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ExpenseId
    {
        return ExpenseId::fromString($this->id);
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getAmount(): Money
    {
        return Money::fromAmount($this->amount, $this->currency);
    }

    public function getStatus(): ExpenseStatus
    {
        return ExpenseStatus::from($this->status);
    }

    public function linkToProject(Project $project): void
    {
        $this->project = $project;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function approve(): void
    {
        if ($this->status !== ExpenseStatus::PENDING->value) {
            throw new \DomainException('Only pending expenses can be approved');
        }

        $this->status = ExpenseStatus::APPROVED->value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reject(): void
    {
        $this->status = ExpenseStatus::REJECTED->value;
        $this->updatedAt = new DateTimeImmutable();
    }
}
