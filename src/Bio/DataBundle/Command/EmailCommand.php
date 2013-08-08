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
		
		$afterTripQuery = $em->createQuery('
				Select s
				FROM BioStudentBundle:Student s
				LEFT OUTER JOIN BioTripBundle:Trip t
				WITH s MEMBER OF t.students
				LEFT OUTER JOIN BioTripBundle:Evaluation e
				WITH e.student = s
				AND e MEMBER OF t.evals
				WHERE t IS NOT NULL
				AND e IS NULL'
				.' AND t.end < :high'.
				' AND t.end > :low'
			)
			->setParameter('high', new \DateTime('+1 day'))
			->setParameter('low', new \DateTime('-1 day'))
			;

		$students = $afterTripQuery->getResult();

		foreach ($students as $student){
			$output->writeln($student->getEmail());
		}
	}
}