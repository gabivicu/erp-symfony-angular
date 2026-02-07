<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\Invoicing\Entity\Client;
use App\Invoicing\ValueObject\ClientId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:clients',
    description: 'Seed clients (requires companies to exist)'
)]
final class SeedClientsCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Clients per company', 105);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Clients');

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

        $batchSize = 1000;
        $count = 0;

        foreach ($companies as $company) {
            for ($i = 0; $i < $perCompany; $i++) {
                $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
                $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
                $name = "$firstName $lastName";
                $email = strtolower("$firstName.$lastName" . ($i + 1) . '@example.com');

                $client = new Client(ClientId::generate(), $name, $email);

                $reflection = new \ReflectionClass($client);
                $property = $reflection->getProperty('company');
                $property->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $company->getId()->toString());
                $property->setValue($client, $companyRef);

                $this->entityManager->persist($client);
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
        $io->success(sprintf('Created %d clients.', $total));

        return Command::SUCCESS;
    }
}
