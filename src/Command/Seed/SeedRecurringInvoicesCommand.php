<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Finance\Entity\RecurringInvoice;
use App\Finance\ValueObject\Money;
use App\Finance\ValueObject\RecurringInvoiceId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:recurring-invoices',
    description: 'Seed recurring invoices (requires companies to exist)'
)]
final class SeedRecurringInvoicesCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Recurring invoices per company', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Recurring Invoices');

        $this->entityManager->getFilters()->disable('company_filter');

        $conn = $this->entityManager->getConnection();
        $companyIds = $conn->fetchFirstColumn('SELECT id FROM companies ORDER BY id');
        if ($companyIds === []) {
            $io->error('No companies found. Run app:seed:companies first.');
            return Command::FAILURE;
        }

        $perCompany = (int) $input->getOption('per-company');
        $frequencies = ['daily', 'weekly', 'monthly', 'yearly'];
        $total = count($companyIds) * $perCompany;
        $bar = $io->createProgressBar($total);
        $bar->start();
        $created = 0;

        foreach ($companyIds as $companyId) {
            for ($i = 0; $i < $perCompany; $i++) {
                $amount = Money::fromAmount((string)rand(100000, 500000), 'USD');
                $frequency = $frequencies[array_rand($frequencies)];
                $description = 'Recurring invoice ' . ($i + 1) . ' - ' . $frequency;
                $startDate = new \DateTimeImmutable('-' . rand(0, 90) . ' days');

                $recurringInvoice = new RecurringInvoice(
                    RecurringInvoiceId::generate(),
                    $frequency,
                    $amount,
                    $description,
                    $startDate
                );

                $reflection = new \ReflectionClass($recurringInvoice);
                $companyProperty = $reflection->getProperty('company');
                $companyProperty->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $companyId);
                $companyProperty->setValue($recurringInvoice, $companyRef);

                $this->entityManager->persist($recurringInvoice);
                $created++;
                $bar->advance();
            }
        }

        $this->entityManager->flush();
        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d recurring invoices.', $created));

        return Command::SUCCESS;
    }
}
