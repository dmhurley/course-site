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
			$options['students']['students'] = 'display_students';
			$options['students']['display'] = 'display_students';
			$options['students']['find'] = 'find_student';
			$options['students']['add'] = 'add_student';
			$options['students']['upload'] = 'upload_student';

		$options['clickers'] = array();
			$options['clickers']['clickers'] = 'register_clicker';
			$options['clickers']['register'] = 'register_clicker';
			$options['clickers']['download'] = 'download_list';
			$options['clickers']['clear'] = 'clear_list';

		if ($route == 'display_students' || $route == 'find_student' || 
			$route == 'edit_student' || $route == 'add_student' || 
			$route == 'upload_student') {
				$top = 'students';
		} else if ( $route == 'register_clicker' || $route == 'download_list' ||
					$route == 'clear_list') {
				$top = 'clickers';
		}

		return array('top' => $top, 'options' => $options);
	}
}
