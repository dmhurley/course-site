<?php

namespace Bio\DataBundle\Objects;

use Acme\DemoBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class DatabaseConnectionSetter
{
    private $connection;
   	private $router;
   	private $user;
   	private $pass;

   	private $hasRun = false;

    public function __construct($connection, $router, $user, $pass)
    {	
    	$this->connection = $connection;
        $this->router = $router;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function onKernelController(FilterControllerEvent $event)
    {	

    	if (!$this->hasRun) {
    		// get route
	        $route = $this->router->match($this->router->getContext()->getPathInfo());
            if ( isset($route['year']) && isset($route['quarter']) && isset($route['number']) ) {
                $dbName = $route['number'].'-'.$route['quarter'].'-'.$route['year'];
                $this->connection->switchTo($dbName, $this->user, $this->pass);
                $this->hasRun = true;
            }
    	}

        // if ($controller[0] instanceof TokenAuthenticatedController) {
        //     $token = $event->getRequest()->query->get('token');
        //     if (!in_array($token, $this->tokens)) {
        //         throw new AccessDeniedHttpException('This action needs a valid token!');
        //     }
        // }
    }
}