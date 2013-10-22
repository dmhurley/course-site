<?php

namespace Bio\InfoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpFoundation\Request;
use Bio\DataBundle\Objects\Database;
use Bio\DataBundle\Exception\BioException;

/**
 * @Route("/admin/{entityName}", requirements={
 *      "entityName" = "^announcement|hours|person|section|courseSection$",
 * })
 */
class DefaultController extends Controller {
	/**
     * @Route("/", name="view")
     */
    public function baseAction(Request $request, $entityName) {
        $lc = strtolower($entityName);
        $uc = ucfirst($entityName);
        $entityType = 'Bio\\InfoBundle\\Entity\\'.$uc;
        $formType = 'Bio\\InfoBundle\\Form\\'.$uc.'Type';

        $entity = new $entityType;
        $form = $this->createForm(new $formType, $entity, array(
                'action' => $this->generateUrl('create_entity', array(
                        'bundle' => 'info',
                        'entityName' => $entityName
                    )
                )
            )
        )
        ->add('submit', 'submit');

        return $this->render('BioInfoBundle:'.$uc.':'.$lc.'.html.twig', 
                array(
                    'form' => $form->createView(),
                    'title' => 'Manage' 
                    )
                );
    }
}