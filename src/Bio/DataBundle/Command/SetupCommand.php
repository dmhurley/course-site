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

class SetupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('bio:setup')
            ->setDescription('Setup a biology course page.')
            ->addArgument(
                'bundles',
                InputArgument::IS_ARRAY,
                'default|all|[info folder student clicker score exam trip switch user]'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
        $bundles = $input->getArgument('bundles');

$output->writeln('<info>Installing Bundles</info>');
$output->writeln('<question>--------------------------------------------</question>');
        if (count($bundles) === 0 || array_search('default', $bundles) !== false) {
            $process = new Process('php app/console bio:install -d --no-clear', null, null, null, 300);
        } else if (array_search('all', $bundles) !== false) {
            $process = new Process('php app/console bio:install -a --no-clear', null, null, null, 300);
        } else {
            $process = new Process('php app/console bio:install '.implode(' ', $bundles).' --no-clear', null, null, null, 300);
        }
        $process->run(function($type, $buffer){echo $buffer;});

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

$output->writeln('<info>Creating schema</info>');
$output->writeln('<question>--------------------------------------------</question>');
        $process = new Process('php app/console doctrine:schema:create', null, null, null, 300);
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            $process = new Process('php app/console doctrine:schema:update --force', null, null, null, 300);
            $process->run(function($type, $buffer){echo $buffer;});

            if (!$process->isSuccessful()){
                throw new \Exception('Unable to generate schema. '.$process->getExitCodeText());
            }
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
        $process = new Process('php app/console bio:create:account --username='.$username.' --password='.$password.' --email='.$email.' --role=ROLE_SUPER_ADMIN', null, null, null, 300);
        
        $process->run(function($type, $buffer){echo $buffer;});
        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to add user. '.$process->getExitCodeText());
        }

        $output->writeln("Clearing cache.");
            $process = new Process('php app/console cache:clear');
            $process->run(function($type, $buffer){echo $buffer;});

        if (!$process->isSuccessful()) {
            throw new \Exception('Unable to clear cache. Clear it manually for changes to take effect.');
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
            $root->setPrivate(false);

            $instructor = new Person();
            $instructor->setfName('John')
                ->setlName('Doe')
                ->setEmail('johndoe@gmail.com')
                ->setBldg('KNE  Kane Hall')
                ->setRoom('101')
                ->setTitle('instructor');

            $examGlobal = new ExamGlobal();
            $examGlobal->setGrade(2)
                ->setRules("Exam rules go here.");

            $tripGlobal = new TripGlobal();
            $tripGlobal->setOpening(new \DateTime())
                ->setClosing(new \Datetime())
                ->setMaxTrips(1)
                ->setEvalDue(5)
                ->setPromo('Trip promo goes here.')
                ->setInstructions('Trip instructions go here.');


        $db->add($info);
        $db->add($root);
        $db->add($instructor);
        $db->add($examGlobal);
        $db->add($tripGlobal);

        $db->close();
    }
}