<?php

namespace Bio\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Bio\UserBundle\Entity\User;

class UserRepository extends EntityRepository {

    private $roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
    private $setup = 'ROLE_SETUP';

    /**
     * Promote or demote a user
     * @param {User} $user
     * @param {boolean} $pro - true to promote, defaults to false/demote
     */
    public function mote(User $user = null, $pro = false) {
        // handle null user
        if (!$user) {
            return array(
                'success' => false,
                'message' => 'Could not find that user.'
            );
        }

        $role = $user->getRoles()[0];

        // handle ROLE_SETUP user
        // because they cannot be modified
        if ($role === $this->setup) {
            return array(
                'success' => false,
                'message' => 'Could not find that user.'
            );
        }

        $index = array_search($role, $this->roles, true);

        // promotion
        if ($pro) {

            // handle someone promoted all the way
            if ($index === count($this->roles) - 1) {
                return array(
                    'success' => false,
                    'message' => 'Cannot promote the user any more.'
                );
            }

            $user->setRoles(array(
                $this->roles[$index + 1]
            ));

        // demotion
        } else {

            // handle someone demoted all the way
            if ($index === 0) {
                return array(
                    'success' => false,
                    'message' => 'Cannnot demote user any more.'
                );
            }

            $user->setRoles(array(
                $this->roles[$index - 1]
            ));
        }

        try {
            $this->getEntityManager()->flush();
            return array(
                'success' => true,
                'message' => $pro ? 'User promoted.' :
                                    'User demoted.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $pro ? 'Could not promote that user.' :
                                    'Could not demote that user.'
            );
        }
    }

    public function delete(User $user = null) {
        // handle no user or ROLE_SETUP
        if (!$user || $user->getRoles()[0] === $this->setup) {
            return array(
                'success' => false,
                'message' => 'User could not be found.'
            );
        }

        try {
            $em = $this->getEntityManager();
            $em->remove($user);
            $em->flush();

            return array(
                'success' => true,
                'message' => 'User deleted.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => 'User could not be deleted.'
            );
        }
    }
}
