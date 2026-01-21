<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:promote-admin',
    description: 'Attribue le rôle ROLE_ADMIN à un utilisateur',
)]
class PromoteUserAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l’utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $output->writeln('<error>Utilisateur introuvable.</error>');
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_unique($roles));

        $this->em->flush();

        $output->writeln('<info>ROLE_ADMIN attribué avec succès.</info>');

        return Command::SUCCESS;
    }
}
