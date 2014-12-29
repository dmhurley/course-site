<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

use Bio\FolderBundle\Entity\File;

class PublicController extends Controller
{
    /**
     * @Route("/download/{id}")
     * @Route("/folders/download/{id}", name="download")
     * @ParamConverter("file", class="BioFolderBundle:File")
     */
    public function downloadAction(Request $request, File $file) {
        if (file_exists($file->getAbsolutePath())) {
            $name = $file->getName();
            $typeArray = explode('.', $file->getPath());

            $response = $this->render('BioPublicBundle:Template:blank.html.twig', array(
                'text' => file_get_contents($file->getAbsolutePath())
                )
            );
            $response->headers->set(
                "Content-Type", $file->getMime()
                );

            $response->headers->set(
                'Content-Disposition', ('attachment; filename="'.$name.'.'.end($typeArray).'"')
                );
            
            $response->headers->set(
                'Content-Length', filesize($file->getAbsolutePath())
                );

            return $response;
        }

        $request->getSession()->getFlashBag()->set('failure', 'Could not find file "'.$filename.'".');
        
        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } 

        return $this->redirect($this->generateUrl('view_folders'));
    }
}