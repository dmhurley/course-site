<?php

namespace Bio\SurveyBundle\Repository;

use Doctrine\Orm\Entity\Repository;

use Bio\UserBundle\Entity\AbstractUserStudent;
use Bio\SurveyBundle\Entity\Survey;

class SurveyRepository extends EntityRepository {

    /**
     * Gets all available surveys for a user
     * @param {AbstractUserStudent} user
     * @return {Collection}
     */
    public function getSurveys(AbstractUserStudent $user) {
        $em = $this->getEntityManager();

        return $em->createQuery('
                SELECT s
                FROM BioSurveyBundle:Survey s
                JOIN BioSurveyBundle:SurveyTaker t
                WITH t.survey = s
                WHERE s.hidden = false
                AND t.student = :studnet
            ')
            ->setParameter('student', $user)
            ->getResult();
    }

    public function
}
