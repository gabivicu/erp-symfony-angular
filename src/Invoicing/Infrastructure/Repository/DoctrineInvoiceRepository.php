<?php

declare(strict_types=1);

namespace App\Invoicing\Infrastructure\Repository;

use App\Invoicing\Domain\Entity\Invoice;
use App\Invoicing\Domain\Repository\InvoiceRepositoryInterface;
use App\Invoicing\Domain\ValueObject\InvoiceId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class DoctrineInvoiceRepository extends ServiceEntityRepository implements InvoiceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    public function save(Invoice $invoice): void
    {
        $this->getEntityManager()->persist($invoice);
        $this->getEntityManager()->flush();
    }

    public function findById(InvoiceId $id): ?Invoice
    {
        return $this->find($id->toString());
    }

    public function findByInvoiceNumber(string $invoiceNumber): ?Invoice
    {
        return $this->findOneBy(['invoiceNumber' => $invoiceNumber]);
    }

    public function findAll(): array
    {
        return parent::findAll();
    }

    public function remove(Invoice $invoice): void
    {
        $this->getEntityManager()->remove($invoice);
        $this->getEntityManager()->flush();
    }
}
