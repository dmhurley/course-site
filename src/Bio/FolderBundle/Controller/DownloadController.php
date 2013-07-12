<?php

namespace Bio\FolderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
     * @Route("/{filename}", name="download")
     * @Template()
     */
    public function downloadAction(Request $request, $filename) {
        $file = $this->get('kernel')->getRootDir()."/../web/files/".$filename;
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            $data = file_get_contents($file);
            return array('text' => $data);
        }

        $request->getSession()->getFlashBag()->set('failure', 'Could not find file "'.$filename.'".');
        
        if ($request->headers->get('referer')){
            return $this->redirect($request->headers->get('referer'));
        } 

        return $this->redirect($this->generateUrl('view_folders'));
    }
}