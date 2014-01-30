<?php

namespace Bio\DataBundle\Objects;

use Acme\DemoBundle\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class DatabaseConnectionSetter
{
    private $connection;
   	private $request;
    private $router;
   	private $user;
   	private $pass;
    private $logger;

   	private $hasRun = false;

    public function __construct($container, $user, $pass)
    {	
    	$this->connection = $container->get('doctrine.dbal.default_connection');
        $this->request = $container->get('request');
        $this->router = $container->get('router');
        $this->user = $user;
        $this->pass = $pass;
        $this->logger = $container->get('logger');

        $this->logger->info('connection setter created');
        $this->logger->info($this->request->getPathInfo());
    }

    public function onKernelRequest()
    {	
    	if (!$this->hasRun) {
            $this->logger->info($this->request->getPathInfo());
    		// get route
	        $route = $this->router->match($this->request->getPathInfo());
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