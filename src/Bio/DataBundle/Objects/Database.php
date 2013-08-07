<?php

namespace Bio\DataBundle\Objects;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

use Bio\DataBundle\Exception\BioException;

class Database {

	private $em;
	private $repo;
	private $entityClass;

	public function __construct($controller, $repo) {
		$this->em = $controller->get('doctrine')->getManager();
		$this->repo = $this->em->getRepository($repo);
		$exploded = explode(":", $repo);
		$this->entityClass = $exploded[1];
	}

	public function find(array $options = array(), array $order = array(), $throwErrors = true) {
		$results = $this->repo->findBy($options, $order);
		if (count($results) == 0 && $throwErrors) {
			throw new BioException('No entries found.');
		}
		return $results;
	}

	public function findOne(array $options = array()) {
		$entities = $this->repo->findOneBy($options);
		return $entities;
	}

	public function addMany(array $entities) {
		foreach ($entities as $entity) {
			$this->add($entity);
		}
	}

	public function add($entity) {
		$this->em->persist($entity);
	}

	public function deleteMany(array $entities) {
		foreach($entities as $entity) {
			$this->delete($entity);
		}
	}

	public function delete($entity) {
		$this->em->remove($entity);
	}

	public function deleteBy(array $options) {
		$this->deleteMany($this->find($options));
	}

	public function truncate(array $order = array()) {
		try {
			$entities = $this->find(array(), $order);
		} catch (BioException $e) {
			$entities = array();
		}

        $connection = $this->em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL($this->entityClass, true));

        return $entities;
	}

	public function clear() {
		$this->em->clear();
	}

	public function close($errorMessage = 'Could not persist objects to database') {
		// try {
			$this->em->flush();
		// } catch (\Doctrine\DBAL\DBALException $e) {
		// 	throw new BioException($errorMessage);
		// }
	}
}