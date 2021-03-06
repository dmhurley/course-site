<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

use Symfony\Component\HttpFoundation\File\File as RealFile;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130916091124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE filebase ADD mimetype VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE filebase DROP mimetype");
    }

    public function postUp(Schema $schema) {
        $dir = __DIR__.'/../../web/files/';
        $conn = $this->connection;
        $results = $conn->fetchAll("SELECT id, path FROM filebase WHERE type = 'file'");
        foreach($results as $result) {
            $conn->update('filebase', array('mimetype' => (new RealFile($dir.$result['path']))->getMimeType()), array('id' => $result['id']));
        }
    }
}
