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
	public function sidebarAction($error) {
		// if admin show full sidebar loaded from yaml file
		if ($this->get('security.context')->isGranted('ROLE_ADMIN')){
			$options = $this->container->getParameter('sidebar');
			return array('options' => $options);

		} else {
			$db = new Database($this, 'BioFolderBundle:Folder');
			$root = $db->findOne(array('id' => 1));

			$db = new Database($this, 'BioInfoBundle:Link');
			$links = $db->find(array('location' => 'sidebar'), array(), false);

			return array('root' => $root, 'links' => $links);
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
