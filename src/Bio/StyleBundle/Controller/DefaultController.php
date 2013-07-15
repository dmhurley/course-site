<?php

namespace Bio\StyleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Bio\DataBundle\Objects\Database;

class DefaultController extends Controller
{
	/**
	 * @Template()
	 */
	public function sidebarAction($route) {
		// if admin show full sidebar loaded from yaml file
		if ($this->get('security.context')->isGranted('ROLE_ADMIN')){
			$options = $this->container->getParameter('sidebar');

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
			} else if ($route == 'view_users') {
				$expanded = 'Users';
			} else if($route == 'scores' || $route == 'find_score') {
				$expanded = 'Scores';
			} else {
				$expanded = '';
			}

			return array('expanded' => $expanded, 'options' => $options, 'role' => 'admin');

		} else {
			$db = new Database($this, 'BioFolderBundle:Folder');
			$root = $db->findOne(array('id' => 1));

			$db = new Database($this, 'BioInfoBundle:Link');
			$links = $db->find(array('location' => 'sidebar'), array(), false);

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
