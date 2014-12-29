<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141125145243 extends AbstractMigration
{
    private $hasTable;

    public function preUp(Schema $schema) {
        $this->hasTable = $schema->hasTable('filebase');
    }

    public function up(Schema $schema)
    {
        if ($this->hasTable) {
            $this->addSql('RENAME TABLE filebase TO tmptable, tmptable TO FileBase');
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
