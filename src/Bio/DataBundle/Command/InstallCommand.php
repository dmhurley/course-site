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
            $this->setSidebar(array('exam', 'trip'), $output);
            $this->setRouting(array('exam', 'trip'), $output);
        } else {
            if (count($bundles) === 0) {
                $output->writeln("OHNOES");
            } else {
                $this->setSidebar($bundles, $output);
                $this->setRouting($bundles, $output);
            }
            $output->writeln(implode($bundles, " "));
        }
    }

    private function setConfig($bundles, $output, $thing, $pre = null) {
        $distribution = 'app/config/'.$thing.'.yml.dist';
        $destination = 'app/config/'.$thing.'.yml';
        if (file_exists($distribution)) {
            $dist = Yaml::parse($distribution);
        } else {
            $output->writeln($distribution." does not exist. Generating empty array.");
            $dist = array();    
        }
        if (file_exists($destination)) {
            $dest = Yaml::parse($destination);
        } else {
            $output->writeln($destination." does not exist. Generating empty array.");
            $dest = array();    
        }

        $output->writeln(print_r($dest));
        $output->writeln(print_r($dist));

        foreach($bundles as $bundleName) {
            $configFileName = 'src/Bio/'.ucFirst($bundleName).'Bundle/Resources/config/'.$thing.'.yml';
            if (file_exists($configFileName)) {
                $src = Yaml::parse($configFileName);
                $srcKeys = array_keys($src);
                $dest[$srcKeys[0]] = $src[$srcKeys[0]];
            } else {
                $output->writeln("Could not find file: ".getcwd().$configFileName);
            }
        }

        file_put_contents($destination, Yaml::dump($dest, 6, 4));
    }

    private function findValueRecursive($key, $array) {
        $object = null;
        array_walk_recursive($array, function() {

        }, $object);
        return $object;
    }

    private function setSidebar(array $bundles, OutputInterface $output) {
        $distribution = 'app/config/parameters.yml.dist';
        $destination = 'app/config/parameters.yml';
        $thing = 'sidebar';
        
        $this->setConfig($bundles, $output, $thing);
    }

    private function setRouting(array $bundles, OutputInterface $output) {
        $distribution = 'app/config/routing.yml.dist';
        $destination = 'app/config/routing.yml';
        $thing = 'routing';

        $this->setConfig($bundles, $output, $thing);
    }
        
}