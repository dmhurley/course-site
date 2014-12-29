<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140613130231 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE Survey (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE SurveyQuestion (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT '(DC2Type:array)', surveyID INT DEFAULT NULL, INDEX IDX_B2819106CE072A18 (surveyID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE SurveyQuestion ADD CONSTRAINT FK_B2819106CE072A18 FOREIGN KEY (surveyID) REFERENCES Survey (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE SurveyQuestion DROP FOREIGN KEY FK_B2819106CE072A18");
        $this->addSql("DROP TABLE Survey");
        $this->addSql("DROP TABLE SurveyQuestion");
    }
}
