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
        
        $this->addSql("ALTER TABLE FileBase ADD studentID INT DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL");
        $this->addSql("ALTER TABLE FileBase ADD CONSTRAINT FK_FBD8A46AA3D10F50 FOREIGN KEY (studentID) REFERENCES Student (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_FBD8A46AA3D10F50 ON FileBase (studentID)");

        $this->addSql("ALTER TABLE filebase ADD `alpha` VARCHAR(2) DEFAULT NOT NULL");
        $this->addSql(' UPDATE filebase f SET f.order = "10" WHERE f.type = "folder" ');
        $this->addSql(' UPDATE filebase f SET f.order = "20" WHERE f.type = "file" ');
        $this->addSql(' UPDATE filebase f SET f.order = "30" WHERE f.type = "link" ');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FileBase DROP FOREIGN KEY FK_FBD8A46AA3D10F50");
        $this->addSql("DROP INDEX IDX_FBD8A46AA3D10F50 ON FileBase");
        $this->addSql("ALTER TABLE FileBase DROP studentID, CHANGE name name VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE FileBase DROP `order` ");
    }
}
