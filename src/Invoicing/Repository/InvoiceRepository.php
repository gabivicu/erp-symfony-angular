<?php

declare(strict_types=1);

namespace App\Invoicing\Repository;

use App\Invoicing\Entity\Invoice;
use App\Invoicing\ValueObject\InvoiceId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class InvoiceRepository extends ServiceEntityRepository
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
