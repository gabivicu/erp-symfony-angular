<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Finance\ValueObject\Money;
use App\Invoicing\Entity\Client;
use App\Invoicing\Entity\Invoice;
use App\Invoicing\Entity\InvoiceLine;
use App\Invoicing\Enum\InvoiceStatus;
use App\Invoicing\ValueObject\InvoiceId;
use App\Invoicing\ValueObject\InvoiceLineId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:invoices',
    description: 'Seed invoices (requires companies and clients to exist)'
)]
final class SeedInvoicesCommand extends Command
{
    use SeederDataTrait;

    private int $invoiceCounter = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Invoices per company', 150);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Invoices');

        $this->entityManager->getFilters()->disable('company_filter');

        $conn = $this->entityManager->getConnection();
        $companyIds = $conn->fetchFirstColumn('SELECT id FROM companies ORDER BY id');
        if ($companyIds === []) {
            $io->error('No companies found. Run app:seed:companies first.');
            return Command::FAILURE;
        }

        $perCompany = (int) $input->getOption('per-company');
        $total = count($companyIds) * $perCompany;
        $bar = $io->createProgressBar($total);
        $bar->start();

        $statuses = [InvoiceStatus::DRAFT, InvoiceStatus::SENT, InvoiceStatus::PAID];
        $batchSize = 500;
        $count = 0;

        foreach ($companyIds as $companyId) {
            $clientIds = $conn->fetchFirstColumn('SELECT id FROM clients WHERE company_id = ?', [$companyId]);
            if ($clientIds === []) {
                $bar->advance($perCompany);
                continue;
            }

            for ($i = 0; $i < $perCompany; $i++) {
                $clientId = $clientIds[array_rand($clientIds)];
                $this->invoiceCounter++;
                $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((string)$this->invoiceCounter, 6, '0', STR_PAD_LEFT) . '-' . uniqid('', true);
                $status = $statuses[array_rand($statuses)];

                $clientRef = $this->entityManager->getReference(Client::class, $clientId);
                $invoice = Invoice::create(InvoiceId::generate(), $clientRef, $invoiceNumber, 'USD');

                $reflection = new \ReflectionClass($invoice);
                $companyProperty = $reflection->getProperty('company');
                $companyProperty->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $companyId);
                $companyProperty->setValue($invoice, $companyRef);

                $statusProperty = $reflection->getProperty('status');
                $statusProperty->setAccessible(true);
                $statusProperty->setValue($invoice, $status->value);

                $linesProperty = $reflection->getProperty('lines');
                $linesProperty->setAccessible(true);
                $lines = [];
                $subtotal = Money::zero('USD');
                $totalVat = Money::zero('USD');

                for ($j = 0; $j < rand(1, 6); $j++) {
                    $quantity = rand(1, 10);
                    $unitPrice = Money::fromAmount((string)rand(10000, 50000), 'USD');
                    $vatRate = rand(0, 20) / 100;
                    $lineSubtotal = $unitPrice->multiply($quantity);
                    $lineVat = $lineSubtotal->multiply((int)($vatRate * 100))->divide(100);

                    $subtotal = $subtotal->add($lineSubtotal);
                    $totalVat = $totalVat->add($lineVat);

                    $line = new InvoiceLine(
                        InvoiceLineId::generate(),
                        $invoice,
                        'Item ' . ($j + 1),
                        $quantity,
                        $unitPrice,
                        $vatRate
                    );
                    $lines[] = $line;
                    $this->entityManager->persist($line);
                }
                $linesProperty->setValue($invoice, new \Doctrine\Common\Collections\ArrayCollection($lines));

                $totalMoney = $subtotal->add($totalVat);
                $subtotalProperty = $reflection->getProperty('subtotal');
                $subtotalProperty->setAccessible(true);
                $subtotalProperty->setValue($invoice, $subtotal->getAmountAsString());

                $vatProperty = $reflection->getProperty('totalVat');
                $vatProperty->setAccessible(true);
                $vatProperty->setValue($invoice, $totalVat->getAmountAsString());

                $totalProperty = $reflection->getProperty('total');
                $totalProperty->setAccessible(true);
                $totalProperty->setValue($invoice, $totalMoney->getAmountAsString());

                $this->entityManager->persist($invoice);
                $count++;
                $bar->advance();

                if ($count % $batchSize === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d invoices.', $count));

        return Command::SUCCESS;
    }
}
