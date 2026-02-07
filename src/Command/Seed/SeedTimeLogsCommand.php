<?php

declare(strict_types=1);

namespace App\Command\Seed;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:timelogs',
    description: 'Seed time logs (requires tasks to exist, uses DBAL for low memory)'
)]
final class SeedTimeLogsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-task', null, InputOption::VALUE_OPTIONAL, 'Time logs per task', 8);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Time Logs');

        $this->entityManager->getFilters()->disable('company_filter');

        $conn = $this->entityManager->getConnection();
        $totalTasks = (int) $conn->fetchOne('SELECT COUNT(*) FROM tasks');
        if ($totalTasks === 0) {
            $io->error('No tasks found. Run app:seed:tasks first.');
            return Command::FAILURE;
        }

        $perTask = (int) $input->getOption('per-task');
        $total = $totalTasks * $perTask;
        $bar = $io->createProgressBar($total);
        $bar->start();

        $batchSize = 100;
        $offset = 0;
        $inserted = 0;

        while ($offset < $totalTasks) {
            $rows = $conn->fetchAllAssociative(
                'SELECT t.id as task_id, t.project_id, p.company_id FROM tasks t INNER JOIN projects p ON t.project_id = p.id ORDER BY t.id LIMIT :limit OFFSET :offset',
                ['limit' => $batchSize, 'offset' => $offset],
                ['limit' => \Doctrine\DBAL\ParameterType::INTEGER, 'offset' => \Doctrine\DBAL\ParameterType::INTEGER]
            );

            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $valueSets = [];
            $params = [];

            foreach ($rows as $row) {
                $taskId = $row['task_id'];
                $projectId = $row['project_id'];
                $companyId = $row['company_id'];

                for ($i = 0; $i < $perTask; $i++) {
                    $id = \Ramsey\Uuid\Uuid::uuid4()->toString();
                    $hours = (string) rand(1, 8);
                    $loggedDate = (new \DateTimeImmutable('-' . rand(0, 90) . ' days'))->format('Y-m-d H:i:s');
                    $description = 'Work session ' . ($i + 1);

                    $valueSets[] = "(?, ?, ?, ?, ?, ?, ?, ?)";
                    $params[] = $id;
                    $params[] = $companyId;
                    $params[] = $projectId;
                    $params[] = $taskId;
                    $params[] = $description;
                    $params[] = $hours;
                    $params[] = $loggedDate;
                    $params[] = $now;
                    $inserted++;
                    $bar->advance();
                }
            }

            if ($valueSets !== []) {
                $sql = 'INSERT INTO time_logs (id, company_id, project_id, task_id, description, hours, logged_date, created_at) VALUES ' . implode(', ', $valueSets);
                $conn->executeStatement($sql, $params);
            }

            $offset += $batchSize;
        }

        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d time logs.', $inserted));

        return Command::SUCCESS;
    }
}
