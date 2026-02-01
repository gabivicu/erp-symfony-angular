<?php

declare(strict_types=1);

namespace App\Projects\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\Projects\Enum\TaskStatus;
use App\Projects\ValueObject\TaskId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Task Entity with Self-Referencing Relationship
 * 
 * Senior-level decision: Self-referencing for subtasks
 * Tasks can have parent tasks (for subtasks/hierarchical structure)
 */
#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
final class Task
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: Task::class, inversedBy: 'subtasks')]
    #[ORM\JoinColumn(name: 'parent_task_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Task $parentTask = null; // Self-referencing relationship

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $estimatedHours = null;

    #[ORM\Column(type: 'integer')]
    private int $priority; // 1-5, 1 = highest

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $dueDate = null;

    /** @var Task[] */
    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'parentTask')]
    private array $subtasks = [];

    public function __construct(
        TaskId $id,
        Project $project,
        string $title,
        int $priority = 3
    ) {
        $this->id = $id->toString();
        $this->project = $project;
        $this->title = $title;
        $this->priority = $priority;
        $this->status = TaskStatus::TODO->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): TaskId
    {
        return TaskId::fromString($this->id);
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function getParentTask(): ?Task
    {
        return $this->parentTask;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStatus(): TaskStatus
    {
        return TaskStatus::from($this->status);
    }

    /**
     * Add a subtask (child task)
     * Self-referencing relationship
     */
    public function addSubtask(Task $subtask): void
    {
        if ($subtask->getId()->toString() === $this->id) {
            throw new \DomainException('Task cannot be its own subtask');
        }

        // Prevent circular references
        $current = $this->parentTask;
        while ($current !== null) {
            if ($current->getId()->toString() === $subtask->getId()->toString()) {
                throw new \DomainException('Circular reference detected');
            }
            $current = $current->getParentTask();
        }

        $subtask->setParentTask($this);
        $this->subtasks[] = $subtask;
        $this->updatedAt = new DateTimeImmutable();
    }

    protected function setParentTask(Task $parent): void
    {
        $this->parentTask = $parent;
    }

    /**
     * Get all subtasks recursively
     */
    public function getAllSubtasks(): array
    {
        $allSubtasks = [];
        foreach ($this->subtasks as $subtask) {
            $allSubtasks[] = $subtask;
            $allSubtasks = array_merge($allSubtasks, $subtask->getAllSubtasks());
        }
        return $allSubtasks;
    }
}
