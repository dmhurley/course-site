<?php

namespace Bio\StudentBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Bio\StudentBundle\Entity\Student;

class StudentRepository extends EntityRepository {

    /**
     * Finds a student by search conditions
     * @param {Array} $array - key/value where key is property value is desired
     * @return {Collection}
     */
    public function find($array) {
        $em = $this->getEntityManager();

        // start our standard query builder
        $qb = $em->createQueryBuilder()
                 ->select('s')
                 ->from('BioStudentBundle:Student', 's');

        // iterate over the index/key/values of the array
        // adding a querybuild#andWhere for each search thing
        foreach(array_keys($array) as $i => $key) {

            // handle special case for encrypted types
            if ($key === 'sid' || $key === 'email') {
                $array[$key] = DBALType::getType('privatestring')->encrypt($array[$key]);
            }

            // for searching with a string, see if it starts with the search value
            if (is_string($array[$key])) {
                $qb->andWhere('s'.$key.' LIKE :value'.$i)
                    ->setParameter('value'.$i, $array[$key].'%');
            }
            // otherwise we assume the search value can be ='d
            else {
                $qb->andWhere('s'.$key.' = :value'.$i)
                    ->setParameter('value'.$i, $array[$key]);
            }
        }

        // use the first key as the orderBy index
        // but default to the fName otherwise
        if (count($array) > 0) {
            reset($array);
            $qb->orderBy('s.'.key($array), 'ASC');
        } else {
            $qb->orderBy('s.fName', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Create a new user
     * @param {Student} $user
     * @param {?} $encoderFactory
     * @return {Array} - result
     */
    public function create(Student $user, $encoderFactory) {
        $em = $this->getEntityManager();
        $em->persist($user);

        $pwd = $user->getPassword();
        return $em->getRepository('BioUserBundle:AbstractUserStudent')
                  ->changePassword($user, $encoderFactory, $pwd);
    }

    /**
     * Tries to delete a student
     * @param {Student} $user
     * @return {Array} - results
     */
    public function delete(Student $user = null) {
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Student could not be found.'
            );
        }

        $em = $this->getEntityManager()
        $em->remove($user);

        try {
            $em->flush();
            return array(
                'success' => true,
                'message' => 'Student deleted.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => 'Student could not be deleted.'
            );
        }

        $this->getEntityManager()->remove($user);
    }
}
