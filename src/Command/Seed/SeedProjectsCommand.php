<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Finance\ValueObject\Money;
use App\Projects\Entity\Project;
use App\Projects\Enum\ProjectStatus;
use App\Projects\ValueObject\ProjectId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:projects',
    description: 'Seed projects (requires companies to exist)'
)]
final class SeedProjectsCommand extends Command
{
    use SeederDataTrait;

    private int $projectCounter = 0;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Projects per company', 40);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Projects');

        $this->entityManager->getFilters()->disable('company_filter');

        $companies = $this->entityManager->getRepository(Company::class)->findAll();
        if ($companies === []) {
            $io->error('No companies found. Run app:seed:companies first.');
            return Command::FAILURE;
        }

        $perCompany = (int) $input->getOption('per-company');
        $total = count($companies) * $perCompany;
        $bar = $io->createProgressBar($total);
        $bar->start();

        $statuses = [ProjectStatus::ACTIVE, ProjectStatus::ON_HOLD, ProjectStatus::COMPLETED];

        foreach ($companies as $company) {
            for ($i = 0; $i < $perCompany; $i++) {
                $name = self::PROJECT_NAMES[array_rand(self::PROJECT_NAMES)] . ' ' . ($i + 1);
                $this->projectCounter++;
                $code = 'PROJ-' . date('Y') . '-' . str_pad((string)$this->projectCounter, 6, '0', STR_PAD_LEFT) . '-' . uniqid('', true);
                $status = $statuses[array_rand($statuses)];
                $budgetAmount = rand(500000, 2000000);
                $hourlyRateAmount = rand(5000, 15000);
                $budget = Money::fromCents($budgetAmount, 'USD');
                $hourlyRate = Money::fromCents($hourlyRateAmount, 'USD');
                $startDate = new \DateTimeImmutable('-' . rand(0, 365) . ' days');

                $project = new Project(
                    ProjectId::generate(),
                    $name,
                    $code,
                    $budget,
                    $hourlyRate,
                    $startDate
                );

                $reflection = new \ReflectionClass($project);
                $companyProperty = $reflection->getProperty('company');
                $companyProperty->setAccessible(true);
                $companyProperty->setValue($project, $company);

                $statusProperty = $reflection->getProperty('status');
                $statusProperty->setAccessible(true);
                $statusProperty->setValue($project, $status->value);

                $this->entityManager->persist($project);
                $bar->advance();
            }
        }

        $this->entityManager->flush();
        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d projects.', $total));

        return Command::SUCCESS;
    }
}
