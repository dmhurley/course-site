<?php

namespace Bio\PublicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
			$db = $this->get('bio.database')->createDatabase('BioFolderBundle:Folder');
			$root = $db->findOne(array('name' => 'sidebar', 'parent' => null));

			$folders = $db->find(
				array('parent' => $root),
				array(),
				false
				);

			$db = $this->get('bio.database')->createDatabase('BioFolderBundle:File');
			$files = $db->find(
				array('parent' => $root),
				array('name' => 'ASC'),
				false
				);

			$db = $this->get('bio.database')->createDatabase('BioFolderBundle:Link');
			$links = $db->find(
				array('parent' => $root),
				array('name' => 'ASC'),
				false
				);

			return $this->render('BioPublicBundle:Content:sidebar.html.twig', array(
				'folders' => $folders,
				'links' => $links,
				'files' => $files
				)
			);
		}
	}
	/**
	 * @Template()
	 */
	public function titleAction() {
		$db = $this->get('bio.database')->createDatabase('BioInfoBundle:Info');
		$info = $db->findOne(array());

		return array('entity' => $info);
	}

	public function emailAction() {
        $db = $this->get('bio.database')->createDatabase('BioInfoBundle:Info');
        $instructor = $db->findOne(array());
        $link = 'mailto:';
        $link.=$instructor->getEmail();
        return new Response($link);
    }
}
