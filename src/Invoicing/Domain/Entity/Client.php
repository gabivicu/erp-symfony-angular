<?php

declare(strict_types=1);

namespace App\Invoicing\Domain\Entity;

use App\Core\Domain\Entity\Company;
use App\Core\Domain\Trait\CompanyAwareTrait;
use App\Invoicing\Domain\ValueObject\ClientId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'clients')]
final class Client
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

    public function __construct(
        ClientId $id,
        string $name,
        string $email
    ) {
        $this->id = $id->toString();
        $this->name = $name;
        $this->email = $email;
    }

    public function getId(): ClientId
    {
        return ClientId::fromString($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
