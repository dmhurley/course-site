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
			/*
			 * select students
			 * who are in a trip that has ended
			 * and have not finished an evaluation for that trip
			 */
			$queryString = '
					SELECT 	DATE_DIFF(CURRENT_DATE(), t.end) as days,
							t.title as title,
							s.email as email,
							s.id as id

					FROM BioTripBundle:Trip t
					LEFT OUTER JOIN BioUserBundle:AbstractUserStudent s
					WITH s MEMBER OF t.students
					LEFT OUTER JOIN BioTripBundle:Evaluation e
					WITH e.student = s
					AND e MEMBER OF t.evals
					WHERE s IS NOT NULL
					AND e IS NULL
					AND t.end < :now
					AND (
							(
								t.end < :one
								AND t.end > :onesubone
							)
							OR 
							(
								t.end < :two
								AND t.end > :twosubone
						 	)
							OR
							(
								t.end < :three
								AND t.end > :threesubone
							)
						)';
			
			$v = $global->getEvalDue();
			$days = floor($v/3 + .5);

			$daysArray = [$days, $days*2, $days*3];

			$daysArray = array_map(function($value) use ($v) {
					if ($value >= $v) {
						return -$value;
					}
					return $v - $value;
				}, $daysArray);

			$afterTripQuery = $em->createQuery($queryString)
				->setParameter('now', new \DateTime())
				->setParameter('one', new \DateTime('-'.$daysArray[0].' day'))
				->setParameter('onesubone', new \DateTime('-'.$daysArray[0].' day -1 hour'))
				->setParameter('two', new \DateTime('-'.$daysArray[1].' day'))
				->setParameter('twosubone', new \DateTime('-'.$daysArray[1].' day -1 hour'))
				->setParameter('three', new \DateTime('-'.$daysArray[2].' day'))
				->setParameter('threesubone', new \DateTime('-'.$daysArray[2].' day -1 hour'));



			/********* SEND EMAILS ************/


			$results = $afterTripQuery->getResult();

			if (count($results) !== 0) {
				/******* SEND EMAILS TO STUDENTS *******/

				$db = new Database($this->getContainer(), 'BioInfoBundle:Info');
				$info = $db->findOne(array());

				$message = \Swift_Message::newInstance()
					->setSubject('Evaluation Reminder')
					->setFrom($info->getEmail())
					->setPriority('high')
					->setContentType('text/html');

				$output->writeln("Sending email(s) to:");
				foreach($results as $result) {
					if ($result['email'] === '') {
						$output->writeln("    Could not send email to: ". $result['id']);
					} else {
						$output->writeln("    ".$result['email']." - ". $result['title'].' - '.$result['days'] ." day(s) left.");
						$message->setTo($result['email'])
							->setBody(
								$this->getContainer()->get('templating')->render('BioDataBundle:Default:email.html.twig', 
									array(
										'global' => $global,
										'days' => $result['days'],
										'title' => $result['title']
									)
								)
							);
						$this->getContainer()->get('mailer')->send($message);
					}
				}

			} else {
				$output->writeln("No emails sent. None needed.");
			}
			
		} else {
			$output->writeln("No emails sent. Evals not open.");
		}
	}
}