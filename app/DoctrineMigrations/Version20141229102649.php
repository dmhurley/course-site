<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141229102649 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FileBase DROP FOREIGN KEY FK_FBD8A46A727ACA70;");
        $this->addSql("ALTER TABLE FileBase ADD CONSTRAINT FK_FBD8A46A727ACA70 FOREIGN KEY (parent_id) REFERENCES FileBase (id) ON DELETE CASCADE");

        $this->addSql(' UPDATE FileBase f SET f.alpha = "10" WHERE f.type = "folder" ');
        $this->addSql(' UPDATE FileBase f SET f.alpha = "20" WHERE f.type = "file" ');
        $this->addSql(' UPDATE FileBase f SET f.alpha = "30" WHERE f.type = "link" ');
    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
 
    }
}
