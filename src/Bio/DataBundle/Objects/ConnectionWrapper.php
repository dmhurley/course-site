<?php

namespace Bio\DataBundle\Objects;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Event\ConnectionEventArgs;

/*
 * @author Dawid zulus Pakula [zulus@w3des.net]
 */
class ConnectionWrapper extends Connection
{		
	private $_dbName = null;
	private $_dbUser = null;
	private $_dbPass = null;

	private $_isConnected = false;

	public function switchTo($dbName, $user, $pass) {
		$this->_dbName = $dbName;
		$this->_dbUser = $user;
		$this->dbPass = $pass;

		$this->close();
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect()
	{	
		if (!$this->isConnected()) {
			if (!$this->_dbName) {
				throw new \InvalidArgumentException("Connection has not been set.");
			}

		    $driverOptions = isset($params['driverOptions']) ? $params['driverOptions'] : array();

		    $params = $this->getParams();
		    $params['dbname'] = $this->_dbName;
		    $params['user'] = $this->_dbUser;
		    $params['password'] = $this->_dbPass;

		    $this->_conn = $this->_driver->connect($params, $params['user'], $params['password'], $driverOptions);

		    if ($this->_eventManager->hasListeners(Events::postConnect)) {
		        $eventArgs = new ConnectionEventArgs($this);
		        $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
		    }

		    $this->_isConnected = true;
		}
		
		return true;
	}

	public function isConnected() {
		return $this->_isConnected;
	}

	public function close() {
		if ($this->isConnected()) {
			parent::close();
			$this->_isConnected = false;
		}
	}
}