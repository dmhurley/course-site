<?php

namespace Bio\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Bio\UserBundle\Entity\AbstractUserStudent;

class AbstractUserStudentRepository extends EntityRepository {

    public function getUserByUsername($username) {
        return $this->getEntityManager()
            ->createQuery('
                SELECT a
                FROM BioUserBundle:AbstractUserStudent a
                JOIN BioUserBundle:User u
                WITH u.id = a.id
                JOIN BioUserBundle:Student s
                WITH s.id = a.id
                WHERE u.username = :username
                OR a.sid = :username
            ')
            ->setParameter('username', $username)
            ->getOneOrNull();
    }

    /**
     * Reset a users password
     * Forced to pass in the encoder factory because we cannot
     * easily to dependency injection into doctrine classes
     *
     * @param {AbstractUserStudent} $user
     * @param {?} $encoderFactory - from #controller->get('security.encoder_factory')
     * @return {Array} - result with password field
     */
    public function reset(AbstractUserStudent $user = null, $encoderFactory) {

        // don't allow SETUP users to have password reset
        // making user = null will return error result
        // from $this->changePassword function
        if ($user && $user->getRoles()[0] === "ROLE_SETUP") {
            $user = null;
        }

        // create random password, update user in db
        $pwd = substr(md5(rand()), 0, 7);
        $result = $this->changePassword($user, $encoderFactory, $pwd);

        // modify result
        $result['password'] = $pwd;
        $result['message'] = $result['success'] ? 'Password reset.' :
                                                  'Could not reset password.';
        return $result;
    }

    /**
     * Changes a users password
     * @param {AbstractUserStudent} $user
     * @param {?} $encoderFactor
     * @param {String} $pwd
     * @return {Array} - result
     */
    public function changePassword(AbstractUserStudent $user = null, $encoderFactory, $pwd) {
        // handle no user
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Could not find user.'
            );
        }

        $encoder = $encoderFactory->getEncoder($user);
        $user->setPassword($encoder->encodePassword($pwd, $user->getSalt()));

        try {
            $this->getEntityManager()->flush();
            return array(
                'success' => true,
                'message' => 'Password set.',
            );
        } catch (\Exception $e) {

            return array(
                'success' => false,
                'message' => 'Could not set password.'
            );
        }
    }
}
