<?php
namespace Bio\DataBundle\Commands;

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

class SetupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:setup')
            ->setDescription('Setup a biology course page.')
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
            ->addOption(
               'all',
               null,
               InputOption::VALUE_NONE,
               'If set, the task will yell in uppercase letters'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');


$output->writeln('<info>Installing assets</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console assets:install --symlink', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to install Assets. '.$process->getExitCodeText());
        }

$output->writeln('<info>Creating database</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console doctrine:database:create', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to create database. '.$process->getExitCodeText());
        }

$output->writeln('<info>Creating schema</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console doctrine:schema:create', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to generate schema. '.$process->getExitCodeText());
        }

$output->writeln('<info>generating entities</info>');
$output->writeln('<question>--------------------------------------------</question>');
        try{
            $this->populateDatabase($output);
        } catch (\Exception $e) {
            throw new \Exception('Unable to persist entities to database.');
        }

        $output->writeln('<info>Creating Account</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console bio:create:account '.$username.' '.$password, null, null, null, 300);
        
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to add user. '.$process->getExitCodeText());
        }
    }

    private function populateDatabase(OutputInterface $output) {
        // does not matter what repository we use as long as we only persist objects to database. 'BioInfoBundle:Info' is arbitrary
        $db = new Database($this->getContainer(), 'BioInfoBundle:Info');
            $info = new Info();
            $info->setCourseNumber(999)
                ->setTitle('Biologiology')
                ->setQtr('summer')
                ->setYear(2013)
                ->setDays(array('m', 'w', 'f'))
                ->setStartTime(new \DateTime())
                ->setEndTime(new \DateTime())
                ->setBldg('KNE  Kane Hall')
                ->setRoom('120')
                ->setEmail('fakeemail@gmail.com');

            $root = new Folder();
            $root->setName('root');

            $instructor = new Person();
            $instructor->setfName('John')
                ->setlName('Doe')
                ->setEmail('johndoe@gmail.com')
                ->setBldg('KNE  Kane Hall')
                ->setRoom('101')
                ->setTitle('instructor');

        $db->add($info);
        $db->add($root);
        $db->add($instructor);

        $db->close();
    }
}