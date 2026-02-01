<?php

declare(strict_types=1);

namespace App\CRM\Domain\Entity;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\CRM\Domain\Enum\LeadStatus;
use App\CRM\Domain\ValueObject\LeadId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lead Entity - CRM Pre-Sales
 * 
 * Business Rule: Accepted Estimate converts Lead to 'Won' status
 */
#[ORM\Entity]
#[ORM\Table(name: 'leads')]
final class Lead
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

    #[ORM\Column(type: 'string', length: 255)]
    private string $email;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $companyName;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $source = null; // website, referral, cold_call, etc.

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $updatedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $convertedAt = null;

    public function __construct(
        LeadId $id,
        string $name,
        string $email,
        string $companyName
    ) {
        $this->id = $id->toString();
        $this->name = $name;
        $this->email = $email;
        $this->companyName = $companyName;
        $this->status = LeadStatus::NEW->value;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): LeadId
    {
        return LeadId::fromString($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getStatus(): LeadStatus
    {
        return LeadStatus::from($this->status);
    }

    public function markAsWon(): void
    {
        if ($this->status === LeadStatus::WON->value) {
            return; // Already won
        }

        $this->status = LeadStatus::WON->value;
        $this->convertedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function markAsLost(): void
    {
        $this->status = LeadStatus::LOST->value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isWon(): bool
    {
        return $this->status === LeadStatus::WON->value;
    }
}
