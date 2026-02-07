<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Finance\Entity\Expense;
use App\Finance\Enum\ExpenseStatus;
use App\Finance\ValueObject\ExpenseId;
use App\Finance\ValueObject\Money;
use App\Projects\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:expenses',
    description: 'Seed expenses (requires companies and projects to exist)'
)]
final class SeedExpensesCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Expenses per company (~2.5 per project)', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Expenses');

        $this->entityManager->getFilters()->disable('company_filter');

        $companies = $this->entityManager->getRepository(Company::class)->findAll();
        $projects = $this->entityManager->getRepository(Project::class)->findAll();
        if ($companies === [] || $projects === []) {
            $io->error('Companies and projects required. Run app:seed:companies and app:seed:projects first.');
            return Command::FAILURE;
        }

        $perCompany = (int) $input->getOption('per-company');
        $total = count($companies) * $perCompany;
        $bar = $io->createProgressBar($total);
        $bar->start();

        $statuses = [ExpenseStatus::PENDING, ExpenseStatus::APPROVED, ExpenseStatus::REJECTED, ExpenseStatus::PAID];
        $categories = ['Travel', 'Equipment', 'Software', 'Marketing', 'Office Supplies', 'Training'];
        $batchSize = 1000;
        $count = 0;

        foreach ($companies as $company) {
            $companyProjects = array_filter($projects, fn($project) => $this->getCompanyFromEntity($project) === $company);
            $companyProjects = array_values($companyProjects);

            for ($i = 0; $i < $perCompany; $i++) {
                $amount = Money::fromAmount((string)rand(5000, 50000), 'USD');
                $category = $categories[array_rand($categories)];
                $project = !empty($companyProjects) ? $companyProjects[array_rand($companyProjects)] : null;
                $description = $category . ' expense ' . ($i + 1);
                $expenseDate = new \DateTimeImmutable('-' . rand(0, 60) . ' days');

                $expense = new Expense(ExpenseId::generate(), $description, $amount, $expenseDate);

                $reflection = new \ReflectionClass($expense);
                $companyProperty = $reflection->getProperty('company');
                $companyProperty->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $company->getId()->toString());
                $companyProperty->setValue($expense, $companyRef);

                if ($project) {
                    $projectProperty = $reflection->getProperty('project');
                    $projectProperty->setAccessible(true);
                    $projectRef = $this->entityManager->getReference(Project::class, $project->getId()->toString());
                    $projectProperty->setValue($expense, $projectRef);
                }

                $this->entityManager->persist($expense);
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
        $io->success(sprintf('Created %d expenses.', $total));

        return Command::SUCCESS;
    }
}
