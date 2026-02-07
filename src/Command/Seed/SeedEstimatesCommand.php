<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\CRM\Entity\Estimate;
use App\CRM\Entity\Lead;
use App\CRM\Enum\EstimateStatus;
use App\CRM\ValueObject\EstimateId;
use App\CRM\ValueObject\EstimateLine;
use App\Finance\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:estimates',
    description: 'Seed estimates (requires companies and leads to exist)'
)]
final class SeedEstimatesCommand extends Command
{
    use SeederDataTrait;

    private int $estimateCounter = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Estimates per company', 50);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Estimates');

        $this->entityManager->getFilters()->disable('company_filter');

        $conn = $this->entityManager->getConnection();
        $companyIds = $conn->fetchFirstColumn('SELECT id FROM companies ORDER BY id');
        if ($companyIds === []) {
            $io->error('No companies found. Run app:seed:companies first.');
            return Command::FAILURE;
        }

        $perCompany = (int) $input->getOption('per-company');
        $statuses = [EstimateStatus::DRAFT, EstimateStatus::SENT, EstimateStatus::ACCEPTED, EstimateStatus::REJECTED];

        $total = 0;
        foreach ($companyIds as $companyId) {
            $leadCount = (int) $conn->fetchOne('SELECT COUNT(*) FROM leads WHERE company_id = ?', [$companyId]);
            $total += min($perCompany, $leadCount);
        }

        $bar = $io->createProgressBar($total);
        $bar->start();

        $batchSize = 500;
        $count = 0;

        foreach ($companyIds as $companyId) {
            $leadIds = $conn->fetchFirstColumn(
                'SELECT id FROM leads WHERE company_id = ? ORDER BY id LIMIT ?',
                [$companyId, $perCompany],
                [\Doctrine\DBAL\ParameterType::STRING, \Doctrine\DBAL\ParameterType::INTEGER]
            );

            foreach ($leadIds as $leadId) {
                $this->estimateCounter++;
                $estimateNumber = 'EST-' . date('Y') . '-' . str_pad((string)$this->estimateCounter, 6, '0', STR_PAD_LEFT) . '-' . uniqid('', true);
                $status = $statuses[array_rand($statuses)];

                $leadRef = $this->entityManager->getReference(Lead::class, $leadId);
                $estimate = new Estimate(EstimateId::generate(), $leadRef, $estimateNumber, 'USD');

                $reflection = new \ReflectionClass($estimate);
                $companyProperty = $reflection->getProperty('company');
                $companyProperty->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $companyId);
                $companyProperty->setValue($estimate, $companyRef);

                for ($j = 0; $j < rand(2, 5); $j++) {
                    $line = new EstimateLine(
                        'Service ' . ($j + 1),
                        rand(1, 10),
                        Money::fromAmount((string)rand(5000, 20000), 'USD'),
                        rand(0, 20) / 100
                    );
                    $estimate->addLine($line);
                }

                $statusProperty = $reflection->getProperty('status');
                $statusProperty->setAccessible(true);
                $statusProperty->setValue($estimate, $status->value);

                $this->entityManager->persist($estimate);
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
        $io->success(sprintf('Created %d estimates.', $count));

        return Command::SUCCESS;
    }
}
