<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141120145124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE SurveyTaker DROP FOREIGN KEY FK_77785A44A3D10F50');
        $this->addSql('ALTER TABLE SurveyTaker ADD surveyID INT DEFAULT NULL');
        $this->addSql('ALTER TABLE SurveyTaker ADD CONSTRAINT FK_77785A44CE072A18 FOREIGN KEY (surveyID) REFERENCES Survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE SurveyTaker ADD CONSTRAINT FK_77785A44A3D10F50 FOREIGN KEY (studentID) REFERENCES AbstractUserStudent (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_77785A44CE072A18 ON SurveyTaker (surveyID)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        
        $this->addSql('ALTER TABLE SurveyTaker DROP FOREIGN KEY FK_77785A44CE072A18');
        $this->addSql('ALTER TABLE SurveyTaker DROP FOREIGN KEY FK_77785A44A3D10F50');
        $this->addSql('DROP INDEX IDX_77785A44CE072A18 ON SurveyTaker');
        $this->addSql('ALTER TABLE SurveyTaker DROP surveyID');
        $this->addSql('ALTER TABLE SurveyTaker ADD CONSTRAINT FK_77785A44A3D10F50 FOREIGN KEY (studentID) REFERENCES survey (id) ON DELETE CASCADE');
    }
}
