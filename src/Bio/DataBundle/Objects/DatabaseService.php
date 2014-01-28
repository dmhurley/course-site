<?php

namespace Bio\DataBundle\Objects;

use Bio\DataBundle\Objects\Database;


class DatabaseService {

	private $doctrine;
	private $session;
	
	public function __construct($doctrine, $routing) {
		$this->doctrine = $doctrine;
		var_dump($routing);
	}

	public function createDatabase($repo) {
		return new Database($this->doctrine->getManager(), $repo);
	}
}