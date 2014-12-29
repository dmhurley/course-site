<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130909155329 extends AbstractMigration
{   
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Student (id INT NOT NULL, section_id INT DEFAULT NULL, sid VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:privatestring)', fName VARCHAR(50) NOT NULL, lName VARCHAR(50) NOT NULL, email VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:privatestring)', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_789E96AF57167AB4 (sid), UNIQUE INDEX UNIQ_789E96AFE7927C74 (email), INDEX IDX_789E96AFD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Clicker (id INT AUTO_INCREMENT NOT NULL, cid VARCHAR(255) NOT NULL, studentID INT DEFAULT NULL, UNIQUE INDEX UNIQ_C80B99594B30D9C4 (cid), UNIQUE INDEX UNIQ_C80B9959A3D10F50 (studentID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Base (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, timestamp DATETIME DEFAULT NULL, expiration DATETIME DEFAULT NULL, text LONGTEXT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, address VARCHAR(2048) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, fName VARCHAR(255) DEFAULT NULL, lName VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, bldg VARCHAR(255) DEFAULT NULL, room VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, day VARCHAR(255) DEFAULT NULL, start TIME DEFAULT NULL, end TIME DEFAULT NULL, days VARCHAR(255) DEFAULT NULL, byAppointment TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_6086515F5E237E06 (name), INDEX IDX_6086515F217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Info (id INT AUTO_INCREMENT NOT NULL, courseNumber VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, qtr VARCHAR(255) NOT NULL, year INT NOT NULL, days LONGTEXT NOT NULL COMMENT '(DC2Type:array)', startTime TIME NOT NULL, endTime TIME NOT NULL, bldg VARCHAR(255) NOT NULL, room VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE FileBase (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, private TINYINT(1) DEFAULT NULL, path VARCHAR(1024) DEFAULT NULL, INDEX IDX_FBD8A46A727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE AbstractUserStudent (id INT AUTO_INCREMENT NOT NULL, salt VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE User (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)', password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT '(DC2Type:array)', UNIQUE INDEX UNIQ_2DA17977F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Scores (id INT AUTO_INCREMENT NOT NULL, sid VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)', scores LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Stat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, stats LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE EvalQuestion (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Evaluation (id INT AUTO_INCREMENT NOT NULL, timestamp DATETIME NOT NULL, score INT DEFAULT NULL, studentID INT DEFAULT NULL, tripID INT DEFAULT NULL, INDEX IDX_5C7EA6A5A3D10F50 (studentID), INDEX IDX_5C7EA6A5E0B6FCC7 (tripID), UNIQUE INDEX Evaluation (tripID, studentID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE eval_answers (eval_id INT NOT NULL, answer_id INT NOT NULL, INDEX IDX_187BA02BD53B5884 (eval_id), INDEX IDX_187BA02BAA334807 (answer_id), PRIMARY KEY(eval_id, answer_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Response (id INT AUTO_INCREMENT NOT NULL, question_id INT DEFAULT NULL, answer LONGTEXT NOT NULL, INDEX IDX_C70D69AD1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Trip (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, shortSum LONGTEXT NOT NULL, longSum LONGTEXT NOT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, max INT NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE trips_students (trip_id INT NOT NULL, student_id INT NOT NULL, INDEX IDX_C2D76ADBA5BC2E0E (trip_id), INDEX IDX_C2D76ADBCB944F1A (student_id), PRIMARY KEY(trip_id, student_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE TripGlobal (id INT AUTO_INCREMENT NOT NULL, opening DATETIME NOT NULL, closing DATETIME NOT NULL, maxTrips INT NOT NULL, evalDue INT NOT NULL, guidePass VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)', instructions LONGTEXT NOT NULL, promo LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE default_questions (global INT NOT NULL, query_id INT NOT NULL, INDEX IDX_1BBE7C72E8058B83 (global), UNIQUE INDEX UNIQ_1BBE7C72EF946F99 (query_id), PRIMARY KEY(global, query_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Answer (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, answer LONGTEXT NOT NULL, questionID INT DEFAULT NULL, INDEX IDX_DD714F13AA334807 (answer_id), INDEX IDX_DD714F1370294E72 (questionID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Exam (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, section VARCHAR(2) DEFAULT NULL, tdate DATE NOT NULL, tstart TIME NOT NULL, tend TIME NOT NULL, tduration INT NOT NULL, gdate DATE NOT NULL, gstart TIME NOT NULL, gend TIME NOT NULL, gDuration INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE exam_questions (test_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_354F518C1E5D0459 (test_id), INDEX IDX_354F518C1E27F6BF (question_id), PRIMARY KEY(test_id, question_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE ExamGlobal (id INT AUTO_INCREMENT NOT NULL, grade INT NOT NULL, rules LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Grade (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, grader_id INT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME DEFAULT NULL, points INT DEFAULT NULL, INDEX IDX_989B8130AA334807 (answer_id), INDEX IDX_989B8130873EE83B (grader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Question (id INT AUTO_INCREMENT NOT NULL, question LONGTEXT NOT NULL, answer LONGTEXT NOT NULL, points INT NOT NULL, tags LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE TestTaker (id INT AUTO_INCREMENT NOT NULL, target_id INT DEFAULT NULL, status INT NOT NULL, timecard LONGTEXT NOT NULL COMMENT '(DC2Type:array)', vars LONGTEXT NOT NULL COMMENT '(DC2Type:array)', numGraded INT NOT NULL, studentID INT DEFAULT NULL, examID INT DEFAULT NULL, INDEX IDX_B0857D34158E0B66 (target_id), INDEX IDX_B0857D34A3D10F50 (studentID), INDEX IDX_B0857D34B3C62D94 (examID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE graded (you_id INT NOT NULL, target_id INT NOT NULL, INDEX IDX_B930E4D7436C51BA (you_id), INDEX IDX_B930E4D7158E0B66 (target_id), PRIMARY KEY(you_id, target_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE graded_by (you_id INT NOT NULL, target_id INT NOT NULL, INDEX IDX_72BE556A436C51BA (you_id), INDEX IDX_72BE556A158E0B66 (target_id), PRIMARY KEY(you_id, target_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE Request (id INT AUTO_INCREMENT NOT NULL, student_id INT DEFAULT NULL, current_section_id INT DEFAULT NULL, request_id INT DEFAULT NULL, status INT NOT NULL, lastUpdated DATETIME NOT NULL, UNIQUE INDEX UNIQ_F42AB603CB944F1A (student_id), INDEX IDX_F42AB6037982A08 (current_section_id), UNIQUE INDEX UNIQ_F42AB603427EB8A5 (request_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE requested_sections (request_id INT NOT NULL, sections_id INT NOT NULL, INDEX IDX_5AE50A08427EB8A5 (request_id), INDEX IDX_5AE50A08577906E4 (sections_id), PRIMARY KEY(request_id, sections_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE Student ADD CONSTRAINT FK_789E96AFD823E37A FOREIGN KEY (section_id) REFERENCES Base (id)");
        $this->addSql("ALTER TABLE Student ADD CONSTRAINT FK_789E96AFBF396750 FOREIGN KEY (id) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Clicker ADD CONSTRAINT FK_C80B9959A3D10F50 FOREIGN KEY (studentID) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Base ADD CONSTRAINT FK_6086515F217BBB47 FOREIGN KEY (person_id) REFERENCES Base (id)");
        $this->addSql("ALTER TABLE FileBase ADD CONSTRAINT FK_FBD8A46A727ACA70 FOREIGN KEY (parent_id) REFERENCES FileBase (id)");
        $this->addSql("ALTER TABLE User ADD CONSTRAINT FK_2DA17977BF396750 FOREIGN KEY (id) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Evaluation ADD CONSTRAINT FK_5C7EA6A5A3D10F50 FOREIGN KEY (studentID) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Evaluation ADD CONSTRAINT FK_5C7EA6A5E0B6FCC7 FOREIGN KEY (tripID) REFERENCES Trip (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE eval_answers ADD CONSTRAINT FK_187BA02BD53B5884 FOREIGN KEY (eval_id) REFERENCES Evaluation (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE eval_answers ADD CONSTRAINT FK_187BA02BAA334807 FOREIGN KEY (answer_id) REFERENCES Response (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Response ADD CONSTRAINT FK_C70D69AD1E27F6BF FOREIGN KEY (question_id) REFERENCES EvalQuestion (id)");
        $this->addSql("ALTER TABLE trips_students ADD CONSTRAINT FK_C2D76ADBA5BC2E0E FOREIGN KEY (trip_id) REFERENCES Trip (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE trips_students ADD CONSTRAINT FK_C2D76ADBCB944F1A FOREIGN KEY (student_id) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE default_questions ADD CONSTRAINT FK_1BBE7C72E8058B83 FOREIGN KEY (global) REFERENCES TripGlobal (id)");
        $this->addSql("ALTER TABLE default_questions ADD CONSTRAINT FK_1BBE7C72EF946F99 FOREIGN KEY (query_id) REFERENCES EvalQuestion (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Answer ADD CONSTRAINT FK_DD714F13AA334807 FOREIGN KEY (answer_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Answer ADD CONSTRAINT FK_DD714F1370294E72 FOREIGN KEY (questionID) REFERENCES Question (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE exam_questions ADD CONSTRAINT FK_354F518C1E5D0459 FOREIGN KEY (test_id) REFERENCES Exam (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE exam_questions ADD CONSTRAINT FK_354F518C1E27F6BF FOREIGN KEY (question_id) REFERENCES Question (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Grade ADD CONSTRAINT FK_989B8130AA334807 FOREIGN KEY (answer_id) REFERENCES Answer (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Grade ADD CONSTRAINT FK_989B8130873EE83B FOREIGN KEY (grader_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE TestTaker ADD CONSTRAINT FK_B0857D34158E0B66 FOREIGN KEY (target_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE TestTaker ADD CONSTRAINT FK_B0857D34A3D10F50 FOREIGN KEY (studentID) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE TestTaker ADD CONSTRAINT FK_B0857D34B3C62D94 FOREIGN KEY (examID) REFERENCES Exam (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7436C51BA FOREIGN KEY (you_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7158E0B66 FOREIGN KEY (target_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded_by ADD CONSTRAINT FK_72BE556A436C51BA FOREIGN KEY (you_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded_by ADD CONSTRAINT FK_72BE556A158E0B66 FOREIGN KEY (target_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Request ADD CONSTRAINT FK_F42AB603CB944F1A FOREIGN KEY (student_id) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Request ADD CONSTRAINT FK_F42AB6037982A08 FOREIGN KEY (current_section_id) REFERENCES Base (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE Request ADD CONSTRAINT FK_F42AB603427EB8A5 FOREIGN KEY (request_id) REFERENCES Request (id) ON DELETE SET NULL");
        $this->addSql("ALTER TABLE requested_sections ADD CONSTRAINT FK_5AE50A08427EB8A5 FOREIGN KEY (request_id) REFERENCES Request (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE requested_sections ADD CONSTRAINT FK_5AE50A08577906E4 FOREIGN KEY (sections_id) REFERENCES Base (id) ON DELETE CASCADE");

        $this->addSql("INSERT INTO `TripGlobal` (`opening`, `closing`, `maxTrips`, `evalDue`, `guidePass`, `instructions`, `promo`) VALUES ('2013-09-20 09:39:25', '2013-09-20 09:39:25', 1, 5, '', 'Trip instructions go here.', 'Trip promo goes here.');");
        $this->addSql("INSERT INTO `ExamGlobal` (`grade`, `rules`) VALUES (2, 'Exam rules go here.');");
        $this->addSql("INSERT INTO `FileBase` (`id`, `parent_id`, `type`, `name`, `private`, `path`) VALUES (1, NULL, 'folder', 'root', 0, NULL);");
        $this->addSql("INSERT INTO `Info` (`courseNumber`, `title`, `qtr`, `year`, `days`, `startTime`, `endTime`, `bldg`, `room`, `email`) VALUES ('999', 'Biologiology', 'summer', 2013, 'a:3:{i:0;s:1:\"m\";i:1;s:1:\"w\";i:2;s:1:\"f\";}', '09:15:14', '09:15:14', 'HCK	Hitchcock Hall', '120', 'fakeemail@gmail.com')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Student DROP FOREIGN KEY FK_789E96AFD823E37A");
        $this->addSql("ALTER TABLE Base DROP FOREIGN KEY FK_6086515F217BBB47");
        $this->addSql("ALTER TABLE Request DROP FOREIGN KEY FK_F42AB6037982A08");
        $this->addSql("ALTER TABLE requested_sections DROP FOREIGN KEY FK_5AE50A08577906E4");
        $this->addSql("ALTER TABLE FileBase DROP FOREIGN KEY FK_FBD8A46A727ACA70");
        $this->addSql("ALTER TABLE Student DROP FOREIGN KEY FK_789E96AFBF396750");
        $this->addSql("ALTER TABLE Clicker DROP FOREIGN KEY FK_C80B9959A3D10F50");
        $this->addSql("ALTER TABLE User DROP FOREIGN KEY FK_2DA17977BF396750");
        $this->addSql("ALTER TABLE Evaluation DROP FOREIGN KEY FK_5C7EA6A5A3D10F50");
        $this->addSql("ALTER TABLE trips_students DROP FOREIGN KEY FK_C2D76ADBCB944F1A");
        $this->addSql("ALTER TABLE TestTaker DROP FOREIGN KEY FK_B0857D34A3D10F50");
        $this->addSql("ALTER TABLE Request DROP FOREIGN KEY FK_F42AB603CB944F1A");
        $this->addSql("ALTER TABLE Response DROP FOREIGN KEY FK_C70D69AD1E27F6BF");
        $this->addSql("ALTER TABLE default_questions DROP FOREIGN KEY FK_1BBE7C72EF946F99");
        $this->addSql("ALTER TABLE eval_answers DROP FOREIGN KEY FK_187BA02BD53B5884");
        $this->addSql("ALTER TABLE eval_answers DROP FOREIGN KEY FK_187BA02BAA334807");
        $this->addSql("ALTER TABLE Evaluation DROP FOREIGN KEY FK_5C7EA6A5E0B6FCC7");
        $this->addSql("ALTER TABLE trips_students DROP FOREIGN KEY FK_C2D76ADBA5BC2E0E");
        $this->addSql("ALTER TABLE default_questions DROP FOREIGN KEY FK_1BBE7C72E8058B83");
        $this->addSql("ALTER TABLE Grade DROP FOREIGN KEY FK_989B8130AA334807");
        $this->addSql("ALTER TABLE exam_questions DROP FOREIGN KEY FK_354F518C1E5D0459");
        $this->addSql("ALTER TABLE TestTaker DROP FOREIGN KEY FK_B0857D34B3C62D94");
        $this->addSql("ALTER TABLE Answer DROP FOREIGN KEY FK_DD714F1370294E72");
        $this->addSql("ALTER TABLE exam_questions DROP FOREIGN KEY FK_354F518C1E27F6BF");
        $this->addSql("ALTER TABLE Answer DROP FOREIGN KEY FK_DD714F13AA334807");
        $this->addSql("ALTER TABLE Grade DROP FOREIGN KEY FK_989B8130873EE83B");
        $this->addSql("ALTER TABLE TestTaker DROP FOREIGN KEY FK_B0857D34158E0B66");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7436C51BA");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7158E0B66");
        $this->addSql("ALTER TABLE graded_by DROP FOREIGN KEY FK_72BE556A436C51BA");
        $this->addSql("ALTER TABLE graded_by DROP FOREIGN KEY FK_72BE556A158E0B66");
        $this->addSql("ALTER TABLE Request DROP FOREIGN KEY FK_F42AB603427EB8A5");
        $this->addSql("ALTER TABLE requested_sections DROP FOREIGN KEY FK_5AE50A08427EB8A5");
        $this->addSql("DROP TABLE Student");
        $this->addSql("DROP TABLE Clicker");
        $this->addSql("DROP TABLE Base");
        $this->addSql("DROP TABLE Info");
        $this->addSql("DROP TABLE FileBase");
        $this->addSql("DROP TABLE AbstractUserStudent");
        $this->addSql("DROP TABLE User");
        $this->addSql("DROP TABLE Scores");
        $this->addSql("DROP TABLE Stat");
        $this->addSql("DROP TABLE EvalQuestion");
        $this->addSql("DROP TABLE Evaluation");
        $this->addSql("DROP TABLE eval_answers");
        $this->addSql("DROP TABLE Response");
        $this->addSql("DROP TABLE Trip");
        $this->addSql("DROP TABLE trips_students");
        $this->addSql("DROP TABLE TripGlobal");
        $this->addSql("DROP TABLE default_questions");
        $this->addSql("DROP TABLE Answer");
        $this->addSql("DROP TABLE Exam");
        $this->addSql("DROP TABLE exam_questions");
        $this->addSql("DROP TABLE ExamGlobal");
        $this->addSql("DROP TABLE Grade");
        $this->addSql("DROP TABLE Question");
        $this->addSql("DROP TABLE TestTaker");
        $this->addSql("DROP TABLE graded");
        $this->addSql("DROP TABLE graded_by");
        $this->addSql("DROP TABLE Request");
        $this->addSql("DROP TABLE requested_sections");
    }
}
