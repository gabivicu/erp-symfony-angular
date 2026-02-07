<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Core\ValueObject\CompanyId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:companies',
    description: 'Seed companies'
)]
final class SeedCompaniesCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of companies', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Companies');

        $this->entityManager->getFilters()->disable('company_filter');

        $count = (int) $input->getOption('count');
        $bar = $io->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $name = self::COMPANY_NAMES[$i % count(self::COMPANY_NAMES)] . ($i >= count(self::COMPANY_NAMES) ? ' ' . ($i + 1) : '');
            $baseName = preg_replace('/[^a-z0-9]/i', '', $name);
            $subdomain = strtolower($baseName) . '-' . time() . '-' . ($i + 1) . '-' . uniqid('', true);
            $plan = $i % 3 === 0 ? 'pro' : 'starter';

            $company = new Company(
                CompanyId::generate(),
                $name,
                $subdomain,
                $plan
            );

            $this->entityManager->persist($company);
            $bar->advance();
        }

        $this->entityManager->flush();
        $bar->finish();
        $io->newLine(2);
        $io->success(sprintf('Created %d companies.', $count));

        return Command::SUCCESS;
    }
}
