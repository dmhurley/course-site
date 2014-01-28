<?php

namespace Bio\DataBundle\Objects;

use Bio\DataBundle\Objects\Database;


class DatabaseService {

	private $doctrine;
	
	public function __construct($doctrine) {
		$this->doctrine = $doctrine;
	}

	public function createDatabase($repo) {
		return new Database($this->doctrine->getManager(), $repo);
	}
}