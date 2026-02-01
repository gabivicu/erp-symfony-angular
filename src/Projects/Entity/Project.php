<?php

declare(strict_types=1);

namespace App\Projects\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\Finance\ValueObject\Money;
use App\Projects\Enum\ProjectStatus;
use App\Projects\ValueObject\ProjectId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Project Entity
 * 
 * Created from accepted Estimate
 * Tracks budget, time logs, expenses for profitability calculation
 */
#[ORM\Entity]
#[ORM\Table(name: 'projects')]
final class Project
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $code; // e.g., PROJ-2024-001

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $budget;

    #[ORM\Column(type: 'string', length: 3)]
    private string $budgetCurrency;

    #[ORM\Column(type: 'decimal', precision: 19, scale: 4)]
    private string $hourlyRate;

    #[ORM\Column(type: 'string', length: 3)]
    private string $hourlyRateCurrency;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $endDate = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    /** @var Task[] */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private array $tasks = [];

    /** @var TimeLog[] */
    #[ORM\OneToMany(targetEntity: TimeLog::class, mappedBy: 'project', cascade: ['persist', 'remove'])]
    private array $timeLogs = [];

    public function __construct(
        ProjectId $id,
        string $name,
        string $code,
        Money $budget,
        Money $hourlyRate,
        DateTimeImmutable $startDate
    ) {
        $this->id = $id->toString();
        $this->name = $name;
        $this->code = $code;
        $this->budget = $budget->getAmount();
        $this->budgetCurrency = $budget->getCurrency();
        $this->hourlyRate = $hourlyRate->getAmount();
        $this->hourlyRateCurrency = $hourlyRate->getCurrency();
        $this->startDate = $startDate;
        $this->status = ProjectStatus::ACTIVE->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ProjectId
    {
        return ProjectId::fromString($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getBudget(): Money
    {
        return Money::fromAmount($this->budget, $this->budgetCurrency);
    }

    public function getHourlyRate(): Money
    {
        return Money::fromAmount($this->hourlyRate, $this->hourlyRateCurrency);
    }

    public function getStatus(): ProjectStatus
    {
        return ProjectStatus::from($this->status);
    }

    /**
     * Calculate total cost from time logs
     */
    public function calculateTimeLogCost(): Money
    {
        $totalHours = 0.0;
        foreach ($this->timeLogs as $timeLog) {
            $totalHours += $timeLog->getHours();
        }

        $hourlyRate = $this->getHourlyRate();
        $totalCents = (int)round($totalHours * $hourlyRate->getAmountInCents() / 100);
        return Money::fromCents($totalCents, $this->budgetCurrency);
    }

    /**
     * Check if budget is exceeded
     */
    public function isBudgetExceeded(): bool
    {
        $timeLogCost = $this->calculateTimeLogCost();
        $budget = $this->getBudget();
        return $timeLogCost->getAmountInCents() > $budget->getAmountInCents();
    }

    public function addTask(Task $task): void
    {
        $this->tasks[] = $task;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addTimeLog(TimeLog $timeLog): void
    {
        $this->timeLogs[] = $timeLog;
        $this->updatedAt = new DateTimeImmutable();
    }
}
