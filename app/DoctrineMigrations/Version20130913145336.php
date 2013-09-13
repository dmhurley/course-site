<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130913145336 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql('UPDATE Base SET days = CONCAT(\'a:1:{i:0;s:\', CHAR_LENGTH(day), \':"\', day, \'";}\') WHERE type = "section"');
        $this->addSql("ALTER TABLE Base DROP day, CHANGE days days LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Base ADD day VARCHAR(255) DEFAULT NULL, CHANGE days days VARCHAR(255) DEFAULT NULL");
    }
}
