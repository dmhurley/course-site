<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130917140542 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE filebase ADD studentID INT DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL");
        $this->addSql("ALTER TABLE filebase ADD CONSTRAINT FK_FBD8A46AA3D10F50 FOREIGN KEY (studentID) REFERENCES Student (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE filebase DROP FOREIGN KEY FK_FBD8A46A727ACA70;");
        $this->addSql("ALTER TABLE filebase ADD CONSTRAINT FK_FBD8A46A727ACA70 FOREIGN KEY (parent_id) REFERENCES filebase (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_FBD8A46AA3D10F50 ON filebase (studentID)");

        $this->addSql("ALTER TABLE filebase ADD `alpha` VARCHAR(2) NOT NULL");
        $this->addSql(' UPDATE filebase f SET f.alpha = "10" WHERE f.type = "folder" ');
        $this->addSql(' UPDATE filebase f SET f.alpha = "20" WHERE f.type = "file" ');
        $this->addSql(' UPDATE filebase f SET f.alpha = "30" WHERE f.type = "link" ');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE filebase DROP FOREIGN KEY FK_FBD8A46AA3D10F50");
        $this->addSql("DROP INDEX IDX_FBD8A46AA3D10F50 ON filebase");
        $this->addSql("ALTER TABLE filebase DROP studentID, CHANGE name name VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE filebase DROP `alpha` ");
    }
}
