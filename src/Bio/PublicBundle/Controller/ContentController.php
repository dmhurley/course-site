<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Bio\DataBundle\Objects\Database;
use Symfony\Component\HttpFoundation\Response;


class ContentController extends Controller
{	
	/**
	 * @Template()
	 */
	public function sidebarAction($error) {
		// if admin show full sidebar loaded from yaml file
		if (!$error && $this->get('security.context')->isGranted('ROLE_ADMIN')){
			$options = $this->container->getParameter('sidebar');
			return array('options' => $options);

		} else {
			$db = new Database($this, 'BioFolderBundle:Folder');
			$root = $db->findOne(array('name' => 'sidebar', 'parent' => null));

			$folders = $db->find(array('parent' => $root));

			$db = new Database($this, 'BioFolderBundle:File');
			$files = $db->find(array('parent' => $root), array('name' => 'ASC'), false);

			$db = new Database($this, 'BioFolderBundle:Link');
			$links = $db->find(array('parent' => $root), array('name' => 'ASC'), false);

			return $this->render('BioPublicBundle:Content:sidebar.html.twig', array('folders' => $folders, 'links' => $links, 'files' => $files));
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

	public function emailAction() {
        $db = new Database($this, 'BioInfoBundle:Info');
        $instructor = $db->findOne(array());
        $link = 'mailto:';
        $link.=$instructor->getEmail();
        return new Response($link);
    }
}
