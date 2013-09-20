<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130913111233 extends AbstractMigration
{

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Info DROP days, DROP startTime, DROP endTime, DROP bldg, DROP room");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE Info ADD days LONGTEXT NOT NULL COMMENT '(DC2Type:array)', ADD startTime TIME NOT NULL, ADD endTime TIME NOT NULL, ADD bldg VARCHAR(255) NOT NULL, ADD room VARCHAR(255) NOT NULL");
    }

    public function postUp(Schema $schema) {
        $conn = $this->connection;
        $sections = $conn->fetchAll("SELECT name FROM Base WHERE type = 'section'");

        $cSections = [];

        foreach ($sections as $section) {
            if (!in_array(substr($section['name'], 0, 1), $cSections)) {
                $conn->insert('Base', array(
                    'person_id' => NULL,
                    'type' => 'course-section', 
                    'timestamp' => NULL,
                    'expiration' => NULL,
                    'text' => NULL,
                    'title' => NULL,
                    'fName' => NULL,
                    'lName' => NULL,
                    'email' => NULL,
                    'bldg' => 'HCK    Hitchcock Hall',
                    'room' => '0',
                    'name' => substr($section['name'], 0, 1),
                    'start' => '00:00:00',
                    'end' => '00:00:00',
                    'days' => 'a:0:{}',
                    'byAppointment' => NULL
                    )
                );

                $cSections[] = substr($section['name'], 0, 1);
            }
        }
    }
}
