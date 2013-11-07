<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Bio\FolderBundle\Entity\Folder;
use Bio\FolderBundle\Entity\File;

/**
 * @Route("/download")
 * @Template()
 */
class DownloadController extends Controller
{
    /**
     * @Route("/{id}", name="download")
     * @ParamConverter("file", class="BioFolderBundle:File")
     */
    public function downloadAction(Request $request, File $file = null) {
        if ($file && file_exists($file->getAbsolutePath())) {
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
        throw $this->createNotFoundException('Could not find file.');
    }
}