<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class SetupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:setup')
            ->setDescription('Setup a biology course page.')
            ->addOption(
                'no-account',
                null,
                InputOption::VALUE_NONE,
                'create an admin account on setup?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        if (!$input->getOption('no-account')) {
            $dialog = $this->getHelperSet()->get('dialog');
            $username = $dialog->ask(
                $output,
                'Username: ',
                null
            );
            $password = $dialog->askHiddenResponse(
                    $output,
                    'Password: ',
                    false
                );
            $email = $dialog->ask(
                $output,
                'Email: ',
                null
            );
        }
/***************************** CREATE DATABASE ********************************/
$output->writeln('<info>Creating database</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console doctrine:database:create', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to create database: (run bio:update?) '.$process->getExitCodeText());
        }

/************************* MIGRATE, CREATE, INSTALL, DUMP *****************************/
        $process = new Process('php app/console bio:update', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});

/************************* CREATE ACCOUNT *****************************/
        
         if (!$input->getOption('no-account')) {
            $output->writeln('<info>Creating Account</info>');
            $output->writeln('<question>--------------------------------------------</question>');
            $process = new Process(
                    'php app/console bio:create:account'.
                    ' --username='.$username.
                    ' --password='.$password.
                    ' --email='.$email.
                    ' --role=ROLE_SUPER_ADMIN',
                 null, null, null, 300);
            
            $process->run(function($type, $buffer){echo $buffer;});
            if (!$process->isSuccessful()) {
                throw new \Exception('Unable to add user. '.$process->getExitCodeText());
            }
        }

        $output->writeln("Clearing cache.");
            $process = new Process('php app/console cache:clear --env=prod');
            $process->run(function($type, $buffer){echo $buffer;});

        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to clear caches. Clear them manually for changes to take effect.');
        }

        $process = new Process('php app/console cache:clear --env=dev');
        $process->run(function($type, $buffer){echo $buffer;});

        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to clear dev cache. Clear it manually for changes to take effect.');
        }
    }
}