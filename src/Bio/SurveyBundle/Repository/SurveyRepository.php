<?php

namespace Bio\SurveyBundle\Repository;

use Doctrine\Orm\EntityRepository;

use Bio\UserBundle\Entity\AbstractUserStudent;
use Bio\SurveyBundle\Entity\Survey;

class SurveyRepository extends EntityRepository {

    /**
     * Gets all available surveys for a user
     * @param {AbstractUserStudent} $user
     * @return {Collection}
     */
    public function getOpenSurveys(AbstractUserStudent $user) {
        $em = $this->getEntityManager();

        return $em->createQuery('
                SELECT s
                FROM BioSurveyBundle:Survey s
                WHERE s.hidden = false
            ')
            ->getResult();
    }

    /**
     * Get all surveys a user has finished
     * @param {AbstractUserStudent} $user
     * @return {Collection}
     */
    public function getFinishedSurveys(AbstractUserStudent $user) {
        return $this->getEntityManager()
            ->createQuery('
                SELECT s
                FROM BioSurveyBundle:Survey s
                JOIN BioSurveyBundle:SurveyTaker t
                WITH t.survey = s
                WHERE t.student = :student
            ')
            ->setParameter('student', $user)
            ->getResult();
    }
}
