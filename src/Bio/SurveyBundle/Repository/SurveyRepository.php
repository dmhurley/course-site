<?php

namespace Bio\SurveyBundle\Repository;

use Doctrine\Orm\EntityRepository;

use Bio\UserBundle\Entity\AbstractUserStudent;
use Bio\SurveyBundle\Entity\Survey;

class SurveyRepository extends EntityRepository {

    public function getTaker(Survey $survey, AbstractUserStudent $user) {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM BioSurveyBundle:SurveyTaker t
                WHERE t.student = :student
                AND t.survey = :survey
            ')
            ->setParameter('student', $user)
            ->setParameter('survey', $survey)
            ->getOneOrNullResult();
    }

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
                LEFT JOIN BioSurveyBundle:SurveyTaker t
                WITH (t.survey = s
                    AND (
                        t.student = :student
                        OR t.id IS NULL
                    )
                )
                WHERE t.id IS NULL
                AND s.hidden = false
            ')
            ->setParameter('student', $user)
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

    /**
     * Check to see if a user has taken a survey
     * @param {Survey} $survey
     * @param {AbstractUserStudent} $user
     * @return {Boolean}
     */
    public function hasTaken(Survey $survey, AbstractUserStudent $user) {
        return $this->getEntityManager()
            ->createQuery('
                SELECT t
                FROM BioSurveyBundle:SurveyTaker t
                WHERE t.student = :user
                AND t.survey = :survey
            ')
            ->setParameter('survey', $survey)
            ->setParameter('user', $user)
            ->getOneOrNullResult() !== null;
    }
}
