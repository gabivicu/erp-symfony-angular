<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Database Seeder - runs each entity seeder in a separate PHP process
 * so memory is freed after each seeder (avoids memory limit issues).
 *
 * Run individual seeders: app:seed:companies, app:seed:clients, app:seed:leads, etc.
 */
#[AsCommand(
    name: 'app:seed',
    description: 'Seed database (runs each seeder in a separate process to avoid memory limit)'
)]
final class DatabaseSeederCommand extends Command
{
    protected function configure(): void
    {
        $this->addOption('companies', 'c', InputOption::VALUE_OPTIONAL, 'Number of companies', 20)
            ->addOption('leads', 'l', InputOption::VALUE_OPTIONAL, 'Number of leads per company', 100)
            ->addOption('projects', 'p', InputOption::VALUE_OPTIONAL, 'Number of projects per company', 40)
            ->addOption('invoices', 'i', InputOption::VALUE_OPTIONAL, 'Number of invoices per company', 150)
            ->addOption('memory', 'm', InputOption::VALUE_OPTIONAL, 'Memory limit for each seeder process (e.g. 512M, 1G)', '512M')
            ->addOption('only', null, InputOption::VALUE_OPTIONAL, 'Run only these seeders (comma-separated: companies, clients, leads, estimates, projects, tasks, timelogs, invoices, expenses, recurring-invoices)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Database Seeder - Running each seeder in a separate process');

        $app = $this->getApplication();
        if ($app === null) {
            $io->error('Application not available.');
            return Command::FAILURE;
        }

        $kernel = $app->getKernel();
        $consolePath = $kernel->getProjectDir() . '/bin/console';
        $phpBinary = (defined('PHP_BINARY') && \PHP_BINARY !== '') ? \PHP_BINARY : 'php';
        // Use current process memory limit when running with -d memory_limit=X so subprocesses get the same limit
        $memoryLimit = ini_get('memory_limit') ?: $input->getOption('memory');
        $memoryLimit = $memoryLimit ?: '512M';

        $companiesCount = (int) $input->getOption('companies');
        $leadsPerCompany = (int) $input->getOption('leads');
        $projectsPerCompany = (int) $input->getOption('projects');
        $invoicesPerCompany = (int) $input->getOption('invoices');

        $clientsPerCompany = (int) ($invoicesPerCompany * 0.7);
        $estimatesPerCompany = (int) ($leadsPerCompany * 0.5);
        $expensesPerCompany = (int) ($projectsPerCompany * 2.5);

        $commands = [
            ['app:seed:companies', ['--count=' . $companiesCount]],
            ['app:seed:clients', ['--per-company=' . $clientsPerCompany]],
            ['app:seed:leads', ['--per-company=' . $leadsPerCompany]],
            ['app:seed:estimates', ['--per-company=' . $estimatesPerCompany]],
            ['app:seed:projects', ['--per-company=' . $projectsPerCompany]],
            ['app:seed:tasks', ['--per-project=5']],
            ['app:seed:timelogs', ['--per-task=2']],
            ['app:seed:invoices', ['--per-company=' . $invoicesPerCompany]],
            ['app:seed:expenses', ['--per-company=' . $expensesPerCompany]],
            ['app:seed:recurring-invoices', ['--per-company=10']],
        ];

        $only = $input->getOption('only');
        if ($only !== null && $only !== '') {
            $allowed = array_map('trim', explode(',', $only));
            $nameToCommand = [
                'companies' => 'app:seed:companies',
                'clients' => 'app:seed:clients',
                'leads' => 'app:seed:leads',
                'estimates' => 'app:seed:estimates',
                'projects' => 'app:seed:projects',
                'tasks' => 'app:seed:tasks',
                'timelogs' => 'app:seed:timelogs',
                'invoices' => 'app:seed:invoices',
                'expenses' => 'app:seed:expenses',
                'recurring-invoices' => 'app:seed:recurring-invoices',
            ];
            $allowedNames = [];
            foreach ($allowed as $key) {
                if (isset($nameToCommand[$key])) {
                    $allowedNames[] = $nameToCommand[$key];
                }
            }
            $commands = array_filter($commands, static fn (array $c) => \in_array($c[0], $allowedNames, true));
            if ($commands === []) {
                $io->error('No valid seeders in --only. Use: companies, clients, leads, estimates, projects, tasks, timelogs, invoices, expenses, recurring-invoices');
                return Command::FAILURE;
            }
        }

        $startTime = microtime(true);

        foreach ($commands as [$name, $args]) {
            $io->section($name);

            $process = new Process(
                array_merge([$phpBinary, '-d', 'memory_limit=' . $memoryLimit, $consolePath, $name], $args),
                $kernel->getProjectDir(),
                null,
                null,
                null
            );
            $process->setTimeout(null);
            $process->run(function (string $type, string $buffer) use ($output): void {
                $output->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->error(sprintf('Seeder %s failed: %s', $name, $process->getErrorOutput() ?: $process->getOutput()));
                return Command::FAILURE;
            }
        }

        $duration = round(microtime(true) - $startTime, 2);
        $io->success([
            'All seeders completed (each ran in a separate process).',
            sprintf('Duration: %s seconds', $duration),
        ]);

        return Command::SUCCESS;
    }
}
