<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Process\Process;

use Bio\DataBundle\Objects\Database;
use Bio\InfoBundle\Entity\Person;
use Bio\InfoBundle\Entity\Announcement;
use Bio\InfoBundle\Entity\Link;
use Bio\InfoBundle\Entity\Section;
use Bio\InfoBundle\Entity\Hours;
use Bio\InfoBundle\Entity\Info;
use Bio\FolderBundle\Entity\Folder;
use Bio\ExamBundle\Entity\ExamGlobal;
use Bio\TripBundle\Entity\TripGlobal;

class UpdateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:update')
            ->setDescription('Updates a biology course page.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

/***************************** MIGRATE ********************************/
$output->writeln('<info>Migrating database</info>'); // change to migrate
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console doctrine:migrations:migrate --no-interaction');
        $process->run(function($type, $buffer){echo $buffer;});

        if (!$process->isSuccessful()){
            throw new \Exception('Unable to update schema. '.$process->getExitCodeText());
        }
/***************************** INSTALL ********************************/
$output->writeln('<info>Installing Bundles</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console bio:install --no-clear');
        $process->run(function($type, $buffer){echo $buffer;});

$output->writeln('<info>Installing assets</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console assets:install --symlink');
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to install Assets. '.$process->getExitCodeText());
        }

/***************************** DUMP ********************************/
$output->writeln('<info>Dumping production assets</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console assetic:dump --env=prod');
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to dump Assets. '.$process->getExitCodeText());
        }
    }
}