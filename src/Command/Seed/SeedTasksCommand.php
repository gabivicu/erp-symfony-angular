<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Projects\Enum\TaskStatus;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Seeds tasks via raw DBAL inserts only (no ORM) to avoid memory exhaustion.
 */
#[AsCommand(
    name: 'app:seed:tasks',
    description: 'Seed tasks (requires projects to exist, uses DBAL for low memory)'
)]
final class SeedTasksCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-project', null, InputOption::VALUE_OPTIONAL, 'Tasks per project', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Tasks');

        $conn = $this->connection;
        $totalProjects = (int) $conn->fetchOne('SELECT COUNT(*) FROM projects');
        if ($totalProjects === 0) {
            $io->error('No projects found. Run app:seed:projects first.');
            return Command::FAILURE;
        }

        $perProject = (int) $input->getOption('per-project');
        $total = $totalProjects * $perProject;
        $bar = $io->createProgressBar($total);
        $bar->start();

        $batchSize = 500;
        $offset = 0;
        $inserted = 0;
        $taskNames = self::TASK_NAMES;
        $statusTodo = TaskStatus::TODO->value;
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        while ($offset < $totalProjects) {
            $rows = $conn->fetchAllAssociative(
                'SELECT id as project_id, company_id FROM projects ORDER BY id LIMIT :limit OFFSET :offset',
                ['limit' => $batchSize, 'offset' => $offset],
                ['limit' => ParameterType::INTEGER, 'offset' => ParameterType::INTEGER]
            );

            $valueSets = [];
            $params = [];

            foreach ($rows as $row) {
                $projectId = $row['project_id'];
                $companyId = $row['company_id'];

                for ($i = 0; $i < $perProject; $i++) {
                    $id = Uuid::uuid4()->toString();
                    $title = $taskNames[array_rand($taskNames)] . ' ' . ($i + 1);
                    $priority = rand(1, 5);

                    $valueSets[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                    $params[] = $id;
                    $params[] = $companyId;
                    $params[] = $projectId;
                    $params[] = $title;
                    $params[] = $statusTodo;
                    $params[] = $priority;
                    $params[] = $now;
                    $params[] = $now;
                    $inserted++;
                    $bar->advance();
                }
            }

            if ($valueSets !== []) {
                $sql = 'INSERT INTO tasks (id, company_id, project_id, title, status, priority, created_at, updated_at) VALUES ' . implode(', ', $valueSets);
                $conn->executeStatement($sql, $params);
            }

            $offset += $batchSize;
        }

        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d tasks.', $inserted));

        return Command::SUCCESS;
    }
}
