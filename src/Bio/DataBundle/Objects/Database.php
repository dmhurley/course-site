<?php

namespace Bio\DataBundle\Objects;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;

use Bio\DataBundle\Exception\BioException;

class Database {

	private $em;
	private $repo;
	private $entityClass;

	public function __construct(Controller $controller, $repo) {
		$this->em = $controller->getDoctrine()->getManager();
		$this->repo = $this->em->getRepository($repo);
		$this->entityClass = explode(":", $repo)[1];
	}

	public function find(array $options = array(), array $order = array()) {
		$results = $this->repo->findBy($options, $order);
		if (count($results) == 0) {
			throw new BioException('No entries found.');
		}
		return $results;
	}

	public function findOne(array $options = array()) {
		$entities = $this->find($options);
		if (count($entities) !== 1) {
			throw new Exception('More than one entry found.');
		}
		return $entities[0];
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
		$em->clear();
	}

	public function close($errorMessage = 'Could not persist objects to database') {
		try {
			$this->em->flush();
		} catch (\Doctrine\DBAL\DBALException $e) {
			throw new BioException($errorMessage);
		}
	}
}