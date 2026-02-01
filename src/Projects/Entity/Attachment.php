<?php

declare(strict_types=1);

namespace App\Projects\Entity;

use App\Core\Entity\Company;
use App\Core\Trait\CompanyAwareTrait;
use App\Projects\ValueObject\AttachmentId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attachment Entity
 * 
 * Can be attached to Projects, Tasks, or other entities
 */
#[ORM\Entity]
#[ORM\Table(name: 'attachments')]
final class Attachment
{
    use CompanyAwareTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: 'company_id', referencedColumnName: 'id', nullable: false)]
    private Company $company;

    #[ORM\Column(type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(type: 'string', length: 500)]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 100)]
    private string $mimeType;

    #[ORM\Column(type: 'bigint')]
    private int $fileSize;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $uploadedAt;

    // Polymorphic relationship: can belong to Project, Task, etc.
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $attachableType = null; // 'project', 'task', 'lead', etc.

    #[ORM\Column(type: 'string', length: 36, nullable: true)]
    private ?string $attachableId = null;

    public function __construct(
        AttachmentId $id,
        string $filename,
        string $filePath,
        string $mimeType,
        int $fileSize
    ) {
        $this->id = $id->toString();
        $this->filename = $filename;
        $this->filePath = $filePath;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;
        $this->uploadedAt = new DateTimeImmutable();
    }

    public function getId(): AttachmentId
    {
        return AttachmentId::fromString($this->id);
    }

    public function attachTo(string $type, string $id): void
    {
        $this->attachableType = $type;
        $this->attachableId = $id;
    }
}
