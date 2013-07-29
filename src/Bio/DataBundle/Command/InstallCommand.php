<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Yaml\Yaml;

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
            $this->install(array('exam'), $output);
        } else {
            if (count($bundles) === 0) {
                $output->writeln("OHNOES");
            } else {
                $this->install($bundles, $output);
            }
            $output->writeln(implode($bundles, " "));
        }
    }

    private function install(array $bundles, $output) {
        $src = 'app/config/parameters.yml.dist';
        $destination = 'app/config/parameters.yml';
        if (file_exists($destination) && file_exists($src)){

            $parameters = Yaml::parse($src);

            foreach($bundles as $bundleName){
                $file = 'src/Bio/'.ucFirst($bundleName).'Bundle/Resources/config/sidebar.yml';

                if (file_exists($file)) {
                    $bundle = Yaml::parse($file);
                    $bundleKeys = array_keys($bundle);
                    $parameters['parameters']['sidebar'][$bundleKeys[0]] = $bundle[$bundleKeys[0]];
                } else {
                    $output->writeln(getcwd().'/'.$file." does not exist.");
                }
            }

            file_put_contents($destination, Yaml::dump($parameters, 6, 4));
        } else {
            $output->writeln("NO PARAMETERS FILE");
        }
    }
}