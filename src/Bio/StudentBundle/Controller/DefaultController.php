<?php

namespace Bio\StudentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

use Bio\StudentBundle\Entity\Student;
use Bio\InfoBundle\Entity\Section;
use Bio\InfoBundle\Entity\CourseSection;
use Bio\StudentBundle\Form\StudentType;
use Bio\DataBundle\Exception\BioException;
use Bio\DataBundle\Objects\Database;
use Doctrine\DBAL\Types\Type as DBALType;
use Bio\StudentBundle\StudentUploader;

/**
 * @Route("/admin/student")
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="students_instruct")
     * @Template()
     */
    public function instructionAction() {
        return array('title' => "Students");
    }

    /**
     * @Route("/find", name="find_student")
     * @Template()
     */
    public function findAction(Request $request){
        $flash = $request->getSession()->getFlashBag();

        $db = new Database($this, 'BioInfoBundle:Section');
        $findArray = $flash->peek('find');
        if ( isset($findArray['section'])) {
            $s = $db->find(array('id' => $findArray['section']), array(), false);
            if (!$s) {
                unset($findArray['section']);
            } else {
                $findArray['section'] = $s;
            }
        }
        $form = $this->createFormBuilder($request->getSession()->getFlashBag()->peek('find'))
            ->add('sid', 'text', array(
                'label' => 'Student ID:',
                'required' => false,
                'constraints' => new Assert\Regex("/[0-9]{7}/"),
                'attr' => array(
                        'pattern' => '[0-9]{7}',
                        'title' => "7 digit student ID"
                    )
                )
            )
            ->add('fName', 'text', array(
                'label' => 'First Name:',
                'required' => false
                )
            )
            ->add('lName', 'text', array(
                'label' => 'Last Name:',
                'required' => false
                )
            )
            ->add('section', 'entity', array(
                'label' => 'Section:',
                'required' => false,
                'class' => 'BioInfoBundle:Section',
                'property' => 'name',
                'data' => $flash->has('find')?$db->findOne(array('id' => $flash->peek('find'))):'',
                'empty_value' => '',
                'query_builder' => function($repo) {
                    return $repo->createQueryBuilder('s')->orderBy('s.name', 'ASC');
                }
                )
            )
            ->add('email', 'text', array(
                'label' => 'Email:',
                'required' => false,
                'constraints' => new Assert\Email()
                )
            )
            ->add('find', 'submit')
            ->getForm();

        $result = array();
        if ($request->getMethod() === "POST" || $flash->has('find')) {
            $form->handleRequest($request);
            if ($request->getMethod() !== "POST") {
                $array = $flash->peek('find');
                $result = $this->findStudents($array);
            } else if ($form->isValid()) {
                $array = array_filter(array_slice($form->getData(), 0, 5));
                if (isset($array['section'])) {
                    $array['section'] = $array['section']->getId();
                }
                $flash->set('find', $array);
                $result = $this->findStudents($array);
            } else {
                $flash->set('failure', 'Invalid form.');
            }
        }

        return array(
            'form' => $form->createView(),
            'entities' => $result,
            'title' => 'Find Student'
            );
    }

    private function findStudents($array) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder()
            ->select('s')
            ->from('BioStudentBundle:Student', 's');

        foreach(array_keys($array) as $i => $key) {
            if ($key === 'sid' || $key === 'email') {
                $array[$key] = DBALType::getType('privatestring')->encrypt($array[$key]);
            }

            if (is_string($array[$key])) {
                $qb->andWhere('s.'.$key.' LIKE :value'.$i)
                    ->setParameter('value'.$i, $array[$key].'%');
            } else {
                $qb->andWhere('s.'.$key.' = :value'.$i)
                    ->setParameter('value'.$i, $array[$key]);
            }
        }
        if (count($array) > 0) {
            reset($array);
            $qb->orderBy('s.'.key($array), 'ASC');
        } else {
            $qb->orderBy('s.fName', 'ASC');
        }
        $query = $qb->getQuery();
        $result = $query->getResult();
        return $result;
    }

    /**
     * @Route("/add", name="add_student")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function addAction(Request $request)
    {
        $flash = $request->getSession()->getFlashBag();

    	$entity = new Student();
    	$form = $this->createForm(new StudentType(), $entity)
            ->add('submit', 'submit');

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		if ($form->isValid()) {

                // try to save the user to the db
                // the create function also encodes password for us
                $result = $this->getDoctrine()
                               ->getManager()
                               ->getRepository('BioStudentBundle:Student')
                               ->create($entity, $this->get('security.encoder_factory'));

                // handle error
                if (!$result['success']) {
                    $error = new FormError("That student ID or email is already registered");
                    $form->get('sid')->addError($error);
                    $form->get('email')->addError($error);
                } else {
                    $flash->set('success', 'Student added.');
                    return $this->redirect($this->generateUrl('add_student'));
                }
    		}

            $flash->set('failure', 'Invalid form.');
    	}
        return array(
            'form' => $form->createView(),
            'title' => "Add Student"
        );
    }

    /**
     * @Route("/delete/{id}", name="delete_student")
     */
    public function deleteAction(Request $request, Student $student = null) {
        $flash = $request->getSession()->getFlashBag();

        $result = $this->getDoctrine()
                       ->getManager()
                       ->getRepository('BioStudentBundle:Student')
                       ->delete($user);

        $flash->set(
            $result['success'] ? 'success' : 'failure',
            $result['message']
        );

        if (!$request->headers->get('referer')){
            return $this->redirect($this->generateUrl('find_student'));
        } else {
            return $this->redirect($request->headers->get('referer'));
        }
    }

    /**
     * @Route("/edit/{id}", name="edit_student")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function editAction(Request $request, Student $student = null) {
        $flash = $request->getSession()->getFlashBag();

        if ($student === null) {
            $flash->set('failure', 'Could not find that student.');
            return $this->redirect($this->generateUrl('find_student'));
        }

    	$form = $this->createForm(new StudentType(), $student)
            ->add('save', 'submit');

    	if ($request->getMethod() === "POST") {		// if request was sent
    		$form->handleRequest($request);
    		if ($form->isValid()) {					// and form was valid
    			$db = new Database($this, 'BioStudentBundle:Student');
                try {
                    $db->close();
                    $flash->set('success', 'Student edited.');
                    return $this->redirect($this->generateUrl('find_student'));
                } catch (BioException $e) {
                    $form->get('email')->addError(new FormError("A student already has that email."));
                    $flash->set('failure', 'Invalid form.');
                }
    		} else {
                $flash->set('failure', 'Invalid form.');
            }
    	}

    	return array('form' => $form->createView(), 'title' => "Edit Student");
    }

	/**
     * @Route("/upload", name="upload_student")
     * @Template("BioPublicBundle:Template:singleForm.html.twig")
     */
    public function uploadAction(Request $request) {
        $flash = $request->getSession()->getFlashBag();

    	$form = $this->createFormBuilder()
    		->add('file', 'file', array('label' => 'File:'))
    		->add('Upload', 'submit')
    		->getForm();

    	if ($request->getMethod() === "POST") {
    		$form->handleRequest($request);
    		$data = $form->get('file')->getData();
    		if ($data !== null) {

                $uploader = new StudentUploader($data);
                $result = $uploader->parse(
                    $this->getDoctrine()->getManager(),
                    $this->get('security.encoder_factory')
                );

                $flash->set(
                    $result['success'] ? 'success' : 'failure',
                    $result['message']
                );

                if (!$result['success']) {
                    $form->get('file')->addError(new FormError($result['message']));
                }
	    	}
    	}
    	return array("form" => $form->createView(), 'title' => "Upload Student List");
    }
}
