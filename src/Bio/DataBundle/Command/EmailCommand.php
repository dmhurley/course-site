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
		
		/*

		*/
		$afterTripQuery = $em->createQuery('
				Select s 
				FROM BioStudentBundle:Student s
				WHERE NOT EXISTS (
						SELECT e 
						FROM BioTripBundle:Evaluation e
						WHERE NOT EXISTS (
								SELECT t
								FROM BioTripBundle:Trip t
								WHERE s MEMBER OF t.students
								AND e MEMBER OF t.evals
								AND e.student = s
								AND t.end > :days
							)
					)
			')->setParameter('days', new \DateTime('-5 days'));
		$students = $afterTripQuery->getResult();

		$output->writeln(count($students));
	}
}