<?php

declare(strict_types=1);

namespace App\Command\Seed;

use App\Core\Entity\Company;
use App\CRM\Entity\Lead;
use App\CRM\Enum\LeadStatus;
use App\CRM\ValueObject\LeadId;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed:leads',
    description: 'Seed leads (requires companies to exist)'
)]
final class SeedLeadsCommand extends Command
{
    use SeederDataTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('per-company', null, InputOption::VALUE_OPTIONAL, 'Leads per company', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Seed Leads');

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

        $sources = ['website', 'referral', 'cold_call', 'email', 'social_media', 'trade_show'];
        $statuses = [LeadStatus::NEW, LeadStatus::CONTACTED, LeadStatus::QUALIFIED, LeadStatus::WON, LeadStatus::LOST];
        $weightedStatusIndices = [0, 0, 0, 1, 1, 2, 3, 4];
        $batchSize = 1000;
        $count = 0;

        foreach ($companies as $company) {
            for ($i = 0; $i < $perCompany; $i++) {
                $firstName = self::FIRST_NAMES[array_rand(self::FIRST_NAMES)];
                $lastName = self::LAST_NAMES[array_rand(self::LAST_NAMES)];
                $name = "$firstName $lastName";
                $email = strtolower("lead.$firstName.$lastName" . ($i + 1) . '@example.com');
                $companyName = $name . ' Company';

                $lead = new Lead(LeadId::generate(), $name, $email, $companyName);

                $reflection = new \ReflectionClass($lead);
                $property = $reflection->getProperty('company');
                $property->setAccessible(true);
                $companyRef = $this->entityManager->getReference(Company::class, $company->getId()->toString());
                $property->setValue($lead, $companyRef);

                $phoneProperty = $reflection->getProperty('phone');
                $phoneProperty->setAccessible(true);
                $phoneProperty->setValue($lead, '+1' . rand(2000000000, 9999999999));

                $sourceProperty = $reflection->getProperty('source');
                $sourceProperty->setAccessible(true);
                $sourceProperty->setValue($lead, $sources[array_rand($sources)]);

                $statusIndex = $weightedStatusIndices[array_rand($weightedStatusIndices)];
                $statusProperty = $reflection->getProperty('status');
                $statusProperty->setAccessible(true);
                $statusProperty->setValue($lead, $statuses[$statusIndex]->value);

                $this->entityManager->persist($lead);
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
        $io->success(sprintf('Created %d leads.', $total));

        return Command::SUCCESS;
    }
}
