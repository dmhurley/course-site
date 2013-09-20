<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130911110229 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        // create new table
        $this->addSql("ALTER TABLE AbstractUserStudent ADD email VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)'");

        // switch  emails
        $this->addSql("UPDATE AbstractUserStudent, Student SET AbstractUserStudent.email=Student.email WHERE AbstractUserStudent.id=Student.id");
        $this->addSql("UPDATE AbstractUserStudent, User SET AbstractUserStudent.email=User.email WHERE AbstractUserStudent.id=User.id");

        // drop old tables
        $this->addSql("DROP INDEX UNIQ_789E96AFE7927C74 ON Student");
        $this->addSql("ALTER TABLE Student DROP email, CHANGE sid sid VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)'");
        $this->addSql("ALTER TABLE User DROP email");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        // create new tables
        $this->addSql("ALTER TABLE Student ADD email VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:privatestring)', CHANGE sid sid VARCHAR(255) DEFAULT NULL COMMENT '(DC2Type:privatestring)'");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_789E96AFE7927C74 ON Student (email)");
        $this->addSql("ALTER TABLE User ADD email VARCHAR(255) NOT NULL COMMENT '(DC2Type:privatestring)'");

        // switch  emails
        $this->addSql("UPDATE AbstractUserStudent, Student SET Student.email=AbstractUserStudent.email WHERE AbstractUserStudent.id=Student.id");
        $this->addSql("UPDATE AbstractUserStudent, User SET User.email=AbstractUserStudent.email WHERE AbstractUserStudent.id=User.id");

        // drop old table
        $this->addSql("ALTER TABLE AbstractUserStudent DROP email");
    }
}
