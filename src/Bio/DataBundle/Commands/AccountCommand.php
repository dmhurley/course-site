<?php
namespace Bio\DataBundle\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AccountCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:create:account')
            ->setDescription('Creates an account')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Username?'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Password?'
            )
            ->addArgument(
                'role',
                InputArgument::OPTIONAL,
                'Role?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');
        if (!$role) {
            $role = 'ROLE_ADMIN';
        }

        $output->writeln('Hashing password and adding account to file.');
        $file = __dir__.'/../../../../app/config/security.yml';
        if (file_exists($file)){
            $contents = file_get_contents($file);
            $contents = str_replace('users:', "users:\n                    ".$username.":  { password: '".hash('sha512', $password)."', roles : '".$role."' }", $contents);
            file_put_contents($file, $contents);
        } else {
            throw new Exception();
        }
    }
}