<?php

declare(strict_types=1);

namespace App\Command;

use App\Core\Entity\User;
use App\Core\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Ramsey\Uuid\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user for login (JWT)',
)]
final class CreateUserCommand extends Command
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
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email (used as username for login)')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Plain password')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Display name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $name = $input->getOption('name');

        if ($email === null || $password === null) {
            $io->error('Use --email=... and --password=...');
            return Command::FAILURE;
        }

        if ($this->userRepository->findOneByEmail($email) !== null) {
            $io->error('User with this email already exists.');
            return Command::FAILURE;
        }

        $user = new User(
            Uuid::uuid4()->toString(),
            $email,
            '', // will be hashed below
            ['ROLE_USER']
        );
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        if ($name !== null) {
            $user->setName($name);
        }

        $this->userRepository->save($user);
        $io->success(sprintf('User created: %s', $email));
        return Command::SUCCESS;
    }
}
