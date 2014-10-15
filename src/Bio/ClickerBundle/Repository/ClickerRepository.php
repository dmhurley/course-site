<?php

namespace Bio\ClickerBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Bio\ClickerBundle\Entity\Clicker;
use Bio\UserBundle\Entity\User;

class ClickerRepository extends EntityRepository {

    /**
     * Registers a clicker ID to a user
     * @param {User} user
     * @param {String} cid - clicker id
     * @return {Array} - {'error': boolean, 'message': string}
     */
    public function registerClicker(User $user, $cid) {
        $em = $this->getEntityManager();
        $clicker = $this->getClickerByUser($user);
        $new = false;

        if (!$clicker) {
            $clicker = new Clicker();
            $clicker->setStudent($user);
            $em->persist($clicker);

            $new = true;
        }
        $clicker->setCid($cid);

        try {
            $em->flush();
            return array(
                'success' => true,
                'message' => $new ? 'Clicker ID #'.$cid.' registered.' :
                                    'Clicker ID changed to #'.$cid.'.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => 'Someone else is already registered to that clicker.'
            );
        }
    }

    /**
     * Tries to find a clicker by clicker id
     * @param {string} $cid
     * @return {Clicker}
     */
    public function getClickerByUser(User $user) {
        return $this->getEntityManager()
            ->createQuery('
                SELECT c
                FROM BioClickerBundle:Clicker c
                WHERE c.student = :student
            ')
            ->setParameter('student', $user)
            ->getOneOrNullResult();
    }

    /**
     * Queries and returns all unregistered students
     * @return {Collection} - result
     */
    public function getUnregisteredStudents() {
        return $this->getEntityManager()
            ->createQuery('
                SELECT s
                FROM BioStudentBundle:Student s
                LEFT JOIN BioClickerBundle:Clicker c
                WITH s = c.student
                WHERE c.id IS NULL
                ORDER BY s.lName ASC
            ')
            ->getResult();
    }

    /**
     * Clears all clcker registrations and
     * resets the id counter
     * @return {Collection} all deleted clickers
     */
    public function removeAll() {
        $clickers = $this->findAll();

        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('Clicker', true));

        return $clickers;
    }
}
