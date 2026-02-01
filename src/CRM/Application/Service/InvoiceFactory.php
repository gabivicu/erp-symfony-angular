<?php

declare(strict_types=1);

namespace App\CRM\Application\Service;

use App\CRM\Domain\Entity\Estimate;
use App\Invoicing\Domain\Entity\Invoice;
use App\Invoicing\Domain\Entity\Client;
use App\Invoicing\Domain\ValueObject\InvoiceId;
use App\Invoicing\Domain\ValueObject\ClientId;
use App\Finance\Domain\ValueObject\Money;
use App\Core\Domain\Entity\Company;
use App\Projects\Domain\Entity\Project;

/**
 * Invoice Factory
 * Creates Invoice entities from Estimates
 */
final class InvoiceFactory
{
    public function createFromEstimate(Estimate $estimate, Project $project): Invoice
    {
        // Create Client from Lead
        $lead = $estimate->getLead();
        $client = new Client(
            ClientId::generate(),
            $lead->getName(),
            $lead->getEmail()
        );

        // Create invoice
        $invoice = Invoice::create(
            InvoiceId::generate(),
            $client,
            $this->generateInvoiceNumber($estimate->getCompany()),
            $estimate->getTotal()->getCurrency()
        );

        // Set company context using reflection
        $reflection = new \ReflectionClass($invoice);
        $method = $reflection->getMethod('setCompany');
        $method->setAccessible(true);
        $method->invoke($invoice, $estimate->getCompany());

        return $invoice;
    }

    public function createDepositInvoice(Estimate $estimate, Project $project, Money $depositAmount): Invoice
    {
        // Create Client from Lead
        $lead = $estimate->getLead();
        $client = new Client(
            ClientId::generate(),
            $lead->getName(),
            $lead->getEmail()
        );

        $invoice = Invoice::create(
            InvoiceId::generate(),
            $client,
            $this->generateInvoiceNumber($estimate->getCompany()) . '-DEPOSIT',
            $depositAmount->getCurrency()
        );

        // Set company context using reflection
        $reflection = new \ReflectionClass($invoice);
        $method = $reflection->getMethod('setCompany');
        $method->setAccessible(true);
        $method->invoke($invoice, $estimate->getCompany());

        return $invoice;
    }

    private function generateInvoiceNumber(Company $company): string
    {
        $year = (new \DateTimeImmutable())->format('Y');
        $number = 1; // Would query database
        return sprintf('INV-%s-%04d', $year, $number);
    }
}
