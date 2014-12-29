<?php

namespace Bio\PublicBundle\DependencyInjection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedHandler implements AccessDeniedHandlerInterface{

	protected $router;

    public function __construct($router){
        $this->router = $router;
    }

	function handle(Request $request, AccessDeniedException $accessDeniedException){
		$request->getSession()->getFlashBag()->set('failure', 'You do not have permission to enter.');

		if ($request->headers->get('referer')){
			return new RedirectResponse($request->headers->get('referer'));
		}
		return new RedirectResponse($this->router->generate('main_page'));
	}
}