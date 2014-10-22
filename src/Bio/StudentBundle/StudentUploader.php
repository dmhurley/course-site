<?php

namespace Bio\StudentBundle;

use Bio\StudentBundle\Entity\Student;

class StudentUploader {

    private $lines;

    public function __constructor($file) {

        // get the content of the file
        // and split into lines
        // csv parse each line and store
        $data = file_get_contents($file);
        $this->lines = array_map(str_getcsv, preg_split(
            '/\n\r|\r\n|\n|\r/',
            $data,
            -1,
            PREG_SPLIT_NO_EMPTY
        ));

        $this->parseHeaders();
    }

    /**
     * Extracts the headers from the file.
     * Headers get saved like [header => index, header => index]
     * @private
     */
    private function parseHeaders() {
        $this->headers = array_flip($this->data[0]);
    }

    /**
     * Parses the file into db
     * @param {EntityManager} $em
     * @return {Array} - result
     */
    public function parse($em, $encoderFactory) {
        $encoder = $encoderFactory->getEncoder(new Student());

        // get all current students/data
        // then walk over arrays, making keys mean something for quick lookup
        $sections = $em->getRepository('BioSectionBundle:Section')->findAll();
        $students = $em->getRepository('BioStudentBundle:Student')->findAll();
        array_walk($sections, function(&$key, $section) { $key = $section->getName(); });
        array_walk($students, function(&$key, $student) { $key = $student->getSid(); });

        // make arrays to hold the students/sections after upload
        // students/sections not in these will be deleted
        $newStudents = array();
        $newSections = array();

        // map each line (excluding the first) to values we want
        $data = array_map($this->extract, array_slice($this->data, 1));

        // build sections
        foreach($data as $i => $studentData) {
            $sectionName = $studentData['labSection'];
            $isNew = isset($sections[$sectionName]);

            // build new section if it doesn't exist
            // otherwise just keep it
            if ($isNew) {
                $newSections[$sectionName] = new Section();
                $newSections[$sectionName]
                    ->setStart(new \DateTime('midnight'))
                    ->setEnd(new \DateTime('midnight'))
                    ->setDays([])
                    ->setBldg("HCK\tHitchcock Hall")
                    ->setRoom(0);

                $em->persist($newSections[$sectionName]);
            } else {
                $newSections[$sectionName] = $sections[$sectionName];
            }
        }


        // build students
        foreach($data as $i => $studentData) {

            // get existing student or create new one
            $isNew = !isset($studentData['sid']);
            $student = $isNew ? new Student() : $students[$studentData['sid']];

            // encode new password based off last name or use current one
            $password = $isNew ? $encoder->encodePassword($studentData['lastName'], $student->getSalt()) :
                                 $student->getPassword();

            // set data on student
            $student->setSid($sid)
                ->setSection($newSections[$studentData['labSection']])
                ->setEmail($studentData['email'])
                ->setFName($studentData['firstName'])
                ->setLName($studentData['lastName'])
                ->setMName($studentData['middleName'])
                ->setPassword($password);

            // persist if new
            if ($isNew) {
                $em->persist($student);
            }
        }


    }

    /**
     * Extract wanted values from a line of data
     * @private
     * @param {Array} $line
     * @return {Array} - data
     */
    private function extract($line) {

        // extra raw data
        $sid = $line[$this->headers['StudentNo']];
        $name = $line[$this->headers['Name']];
        $section = $line[$this->headers['Lb Sect']];
        $email = $line[$this->headers['Email']];

        // defaults
        $section = $section ? $section : 'AA';
        $email = $email ? $email : '';

        // split $name into $firstName, $middleName, and $lastName
        $parts = explode(",", $name); // ['Last, First( Middle (I))']
        $parts[1] = explode(' ', $parts[1], 2); // ['Last', ['First', 'Middle I']]

        $lastName = trim($parts[0]);
        $firstName = trim($parts[1][0]);
        $middleName = trim(count($parts[1]) > 1 ? $parts[1][1] : '');

        return array(
            'sid' => $sid,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'middleName' => $middleName,
            'section' => substr($section, 0, 1),
            'labSection' => $section,
            'email' => $email
        );
    }
}
