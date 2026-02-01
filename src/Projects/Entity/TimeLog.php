<?php

declare(strict_types=1);

namespace App\Projects\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\Projects\ValueObject\TimeLogId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * TimeLog Entity
 * 
 * Tracks time spent on tasks/projects
 * Used for budget tracking and profitability calculation
 */
#[ORM\Entity]
#[ORM\Table(name: 'time_logs')]
final class TimeLog
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'timeLogs')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Task $task = null;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $hours;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $loggedDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        TimeLogId $id,
        Project $project,
        string $description,
        float $hours,
        DateTimeImmutable $loggedDate,
        ?Task $task = null
    ) {
        if ($hours <= 0) {
            throw new \DomainException('Hours must be greater than 0');
        }

        $this->id = $id->toString();
        $this->project = $project;
        $this->task = $task;
        $this->description = $description;
        $this->hours = (string) $hours;
        $this->loggedDate = $loggedDate;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): TimeLogId
    {
        return TimeLogId::fromString($this->id);
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function getHours(): float
    {
        return (float) $this->hours;
    }

    public function getLoggedDate(): DateTimeImmutable
    {
        return $this->loggedDate;
    }
}
