<?php

namespace Bio\StyleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class DefaultController extends Controller
{
	/**
	 * @Template()
	 */
	public function sidebarAction($route) {
		$yaml = new Parser();
		$options = $yaml->parse(file_get_contents('bundles/biostyle/sidebar.yml'));

		if ($route == 'display_students' || $route == 'find_student' || 
			$route == 'edit_student' || $route == 'add_student' || 
			$route == 'upload_student') {
				$expanded = 'Students';
		} else if ( $route == 'register_clicker' || $route == 'download_list' ||
					$route == 'clear_list') {
				$expanded = 'Clickers';
		} else if ( $route == 'edit_info' || $route == "view" || $route == 'edit') {
			$expanded = 'Course Info';
		} else if ($route == 'view_folders' || $route == 'clear_folders') {
			$expanded = 'Folders';
		} else {
			$expanded = '';
		}

		return array('expanded' => $expanded, 'options' => $options);
	}
}
