<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\User\UserInterface;

class InstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:install')
            ->setDescription('Install optional bundles')
            ->addOption(
                'all',
                '-a',
                InputOption::VALUE_NONE,
                'Install all available bundles?'
            )
            ->addArgument(
                'bundles',
                InputArgument::IS_ARRAY,
                'exam|trip'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bundles = $input->getArgument('bundles');

        if ($input->getOption('all')) {
            $output->writeln('Installing all bundles');
            $this->install(array('exam'))
        } else {
            if (count($bundles) === 0) {
                $output->writeln("OHNOES");
            } else {
                $this->install($bundles);
            }
            $output->writeln(implode($bundles, " "));
        }
    }

    private function install(array $bundles) {
        // do things
    }
}