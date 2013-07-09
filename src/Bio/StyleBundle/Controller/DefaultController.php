<?php

namespace Bio\StyleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

use Bio\DataBundle\Objects\Database;

class DefaultController extends Controller
{
	/**
	 * @Template()
	 */
	public function sidebarAction($route) {
		// if admin show full sidebar loaded from yaml file
		if ($this->get('security.context')->isGranted('ROLE_ADMIN')){
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

			return array('expanded' => $expanded, 'options' => $options, 'role' => 'admin');

		} else {
			$db = new Database($this, 'BioFolderBundle:Folder');
			$root = $db->findOne(array('id' => 1));

			$db = new Database($this, 'BioInfoBundle:Link');
			$links = $db->find(array('location' => 'sidebar'));

			return array('root' => $root, 'links' => $links, 'role' => 'user');
		}
	}

	/**
	 * @Template()
	 */
	public function titleAction() {
		$db = new Database($this, 'BioInfoBundle:Info');
		$info = $db->findOne(array());

		return array('entity' => $info);
	}
}
