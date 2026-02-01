<?php

declare(strict_types=1);

namespace App\Shared\Domain\Entity;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\Shared\Domain\ValueObject\CommentId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Comment Entity - Polymorphic Relationship
 * 
 * Senior-level decision: Polymorphic association
 * Comments can belong to Task, Project, Lead, Invoice, etc.
 * Uses single table with type + id columns
 */
#[ORM\Entity]
#[ORM\Table(name: 'comments')]
final class Comment
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(type: 'string', length: 50)]
    private string $commentableType; // 'project', 'task', 'lead', 'invoice', etc.

    #[ORM\Column(type: 'string', length: 36)]
    private string $commentableId;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        CommentId $id,
        string $content,
        string $commentableType,
        string $commentableId
    ) {
        $this->id = $id->toString();
        $this->content = $content;
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): CommentId
    {
        return CommentId::fromString($this->id);
    }

    public function getCommentableType(): string
    {
        return $this->commentableType;
    }

    public function getCommentableId(): string
    {
        return $this->commentableId;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
