<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141118155911 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('CREATE TABLE SurveyAnswer (id INT AUTO_INCREMENT NOT NULL, answer_id INT DEFAULT NULL, answer LONGTEXT NOT NULL, questionID INT DEFAULT NULL, INDEX IDX_43088A4FAA334807 (answer_id), INDEX IDX_43088A4F70294E72 (questionID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE SurveyTaker (id INT AUTO_INCREMENT NOT NULL, studentID INT DEFAULT NULL, INDEX IDX_77785A44A3D10F50 (studentID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE SurveyAnswer ADD CONSTRAINT FK_43088A4FAA334807 FOREIGN KEY (answer_id) REFERENCES SurveyTaker (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SurveyAnswer ADD CONSTRAINT FK_43088A4F70294E72 FOREIGN KEY (questionID) REFERENCES SurveyQuestion (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SurveyTaker ADD CONSTRAINT FK_77785A44A3D10F50 FOREIGN KEY (studentID) REFERENCES Survey (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE SurveyAnswer DROP FOREIGN KEY FK_43088A4FAA334807');
        $this->addSql('DROP TABLE SurveyAnswer');
        $this->addSql('DROP TABLE SurveyTaker');
    }
}
