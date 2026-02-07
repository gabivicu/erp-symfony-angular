<?php

declare(strict_types=1);

namespace App\Command;

use App\Core\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:debug-user',
    description: 'Debug user login - check if user exists and password matches',
)]
final class DebugUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password to test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');
        $password = $input->getOption('password');

        if (!is_string($email) || $email === '') {
            $io->error('Use --email=...');
            return Command::FAILURE;
        }

        $user = $this->userRepository->findOneByEmail($email);
        if ($user === null) {
            $io->error(sprintf('User NOT FOUND: %s', $email));
            return Command::FAILURE;
        }

        $io->success(sprintf('User FOUND: %s', $email));
        $io->table(
            ['Property', 'Value'],
            [
                ['ID', $user->getId()],
                ['Email', $user->getEmail()],
                ['Active', $user->isActive() ? 'YES' : 'NO'],
                ['Roles', implode(', ', $user->getRoles())],
                ['Password Hash (first 50 chars)', substr($user->getPassword(), 0, 50) . '...'],
            ]
        );

        if ($user->isActive() === false) {
            $io->warning('User is INACTIVE - login will fail!');
        }

        if (is_string($password) && $password !== '') {
            $isValid = $this->passwordHasher->isPasswordValid($user, $password);
            if ($isValid) {
                $io->success('Password is VALID ✓');
            } else {
                $io->error('Password is INVALID ✗');
            }
        } else {
            $io->note('Use --password=... to test password');
        }

        return Command::SUCCESS;
    }
}
