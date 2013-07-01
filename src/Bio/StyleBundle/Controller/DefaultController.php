<?php

namespace Bio\StyleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
	/**
	 * @Template()
	 */
	public function sidebarAction($route) {
		$options = array();
		$options['students'] = array();
			$options['students']['display'] = 'display_students';
			$options['students']['find'] = 'find_student';
			$options['students']['add'] = 'add_student';
			$options['students']['upload'] = 'upload_student';

		$options['clickers'] = array();
			$options['clickers']['register'] = 'display_students';
			$options['clickers']['download'] = 'display_students';

		if ($route == 'display_students' || $route == 'find_student' || 
			$route == 'edit_student' || $route == 'add_student' || 
			$route == 'upload_student') {
				$top = 'students';
		} else if ( $route == 'register_clicker') {
			$top = 'clickers';
		}

		return array('top' => $top, 'options' => $options);
	}
}
