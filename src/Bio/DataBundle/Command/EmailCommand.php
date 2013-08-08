<?php
namespace Bio\DataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Bio\DataBundle\Objects\Database;

class EmailCommand extends ContainerAwareCommand {
	protected function configure() {
		$this->setName('bio:email')
			->setDescription('Sends emails if necessary.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$em = $this->getContainer()->get('doctrine')->getManager();

		$global = $em->createQuery('Select g from BioTripBundle:TripGlobal g')->getSingleResult();
		

		// only do anything if the current time falls between the opening and closing times
		if ($global->getOpening() < new \DateTime() && $global->getClosing() > new \DateTime()){
			
			/******* GET STUDENTS WHO NEED NOTIFICATIONS *******/
			$queryString = '
					Select s
					FROM BioStudentBundle:Student s
					LEFT OUTER JOIN BioTripBundle:Trip t
					WITH s MEMBER OF t.students
					LEFT OUTER JOIN BioTripBundle:Evaluation e
					WITH e.student = s
					AND e MEMBER OF t.evals
					WHERE t IS NOT NULL
					AND e IS NULL';

			// if the closing time is more than two days away,
			// look for students who finished a trip <5> days ago
			$addParameters = false;
			if ($global->getClosing() > new \DateTime('+2 days')) {
				$queryString.= '
					AND t.end < :high
					AND t.end > :low';
				$addParameters = true;
				$output->writeln("Finding students who have finished a trip > 5 days ago.");
			} else {
				$output->writeln("Finding students.");
			}

			$afterTripQuery = $em->createQuery($queryString);
				

			// add parameters if necessary
			if ($addParameters) {
				$afterTripQuery->setParameter('high', new \DateTime('-5 days'))
							   ->setParameter('low', new \DateTime('-6 days'));
			}

			$students = $afterTripQuery->getResult();

			if (count($students) !== 0) {
				/******* SEND EMAILS TO STUDENTS *******/
				$output->writeln("Sending email(s) to:");
				foreach ($students as $student){
					$output->writeln("    ".$student->getEmail());
				}
				$message = \Swift_Message::newInstance()
					->setSubject('Evaluation Reminder')
					->setFrom('bio@uw.edu')
					->setSender('bio@uw.edu');
				foreach($students as $student) {
					$message->addBcc($student->getEmail(), $student->getFName().' '.$student->getLName());
				}
				$message->setBody($this->getContainer()->get('templating')->render('BioDataBundle:Default:email.html.twig', array('global' => $global)))
					->setPriority('high')
					->setContentType('text/html');

				$this->getContainer()->get('mailer')->send($message);


				$output->writeln("Success.");
			} else {
				$output->writeln("All evaluations graded.");
			}
			
		} else {
			$output->writeln("No evaluations can be made.");
		}
	}
}