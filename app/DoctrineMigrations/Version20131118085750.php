<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131118085750 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Grade DROP FOREIGN KEY FK_989B8130873EE83B");
        $this->addSql("ALTER TABLE Grade ADD CONSTRAINT FK_989B8130873EE83B FOREIGN KEY (grader_id) REFERENCES TestTaker (id) ON DELETE SET NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Grade DROP FOREIGN KEY FK_989B8130873EE83B");
        $this->addSql("ALTER TABLE Grade ADD CONSTRAINT FK_989B8130873EE83B FOREIGN KEY (grader_id) REFERENCES testtaker (id) ON DELETE CASCADE");
    }
}
