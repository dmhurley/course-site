<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131014150900 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE assigned (your_id INT NOT NULL, their_id INT NOT NULL, INDEX IDX_B9ACC9A743AEC83 (your_id), INDEX IDX_B9ACC9A614CBD61 (their_id), PRIMARY KEY(your_id, their_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE assigned ADD CONSTRAINT FK_B9ACC9A743AEC83 FOREIGN KEY (your_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE assigned ADD CONSTRAINT FK_B9ACC9A614CBD61 FOREIGN KEY (their_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("DROP TABLE graded_by");
        $this->addSql("ALTER TABLE TestTaker DROP FOREIGN KEY FK_B0857D34158E0B66");
        $this->addSql("DROP INDEX IDX_B0857D34158E0B66 ON TestTaker");
        $this->addSql("ALTER TABLE TestTaker ADD gradedByNum INT NOT NULL, DROP target_id, DROP vars, CHANGE numgraded gradedNum INT NOT NULL");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7158E0B66");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7436C51BA");
        $this->addSql("DROP INDEX IDX_B930E4D7436C51BA ON graded");
        $this->addSql("DROP INDEX IDX_B930E4D7158E0B66 ON graded");
        $this->addSql("ALTER TABLE graded DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE graded ADD your_id INT NOT NULL, ADD their_id INT NOT NULL, DROP you_id, DROP target_id");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7743AEC83 FOREIGN KEY (your_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7614CBD61 FOREIGN KEY (their_id) REFERENCES TestTaker (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_B930E4D7743AEC83 ON graded (your_id)");
        $this->addSql("CREATE INDEX IDX_B930E4D7614CBD61 ON graded (their_id)");
        $this->addSql("ALTER TABLE graded ADD PRIMARY KEY (your_id, their_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE graded_by (you_id INT NOT NULL, target_id INT NOT NULL, INDEX IDX_72BE556A436C51BA (you_id), INDEX IDX_72BE556A158E0B66 (target_id), PRIMARY KEY(you_id, target_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE graded_by ADD CONSTRAINT FK_72BE556A158E0B66 FOREIGN KEY (target_id) REFERENCES testtaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded_by ADD CONSTRAINT FK_72BE556A436C51BA FOREIGN KEY (you_id) REFERENCES testtaker (id) ON DELETE CASCADE");
        $this->addSql("DROP TABLE assigned");
        $this->addSql("ALTER TABLE TestTaker ADD target_id INT DEFAULT NULL, ADD vars LONGTEXT NOT NULL COMMENT '(DC2Type:array)', ADD numGraded INT NOT NULL, DROP gradedNum, DROP gradedByNum");
        $this->addSql("ALTER TABLE TestTaker ADD CONSTRAINT FK_B0857D34158E0B66 FOREIGN KEY (target_id) REFERENCES testtaker (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_B0857D34158E0B66 ON TestTaker (target_id)");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7743AEC83");
        $this->addSql("ALTER TABLE graded DROP FOREIGN KEY FK_B930E4D7614CBD61");
        $this->addSql("DROP INDEX IDX_B930E4D7743AEC83 ON graded");
        $this->addSql("DROP INDEX IDX_B930E4D7614CBD61 ON graded");
        $this->addSql("ALTER TABLE graded DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE graded ADD you_id INT NOT NULL, ADD target_id INT NOT NULL, DROP your_id, DROP their_id");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7158E0B66 FOREIGN KEY (target_id) REFERENCES testtaker (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE graded ADD CONSTRAINT FK_B930E4D7436C51BA FOREIGN KEY (you_id) REFERENCES testtaker (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_B930E4D7436C51BA ON graded (you_id)");
        $this->addSql("CREATE INDEX IDX_B930E4D7158E0B66 ON graded (target_id)");
        $this->addSql("ALTER TABLE graded ADD PRIMARY KEY (you_id, target_id)");
    }

    public function preUp(Schema $schema) {
        // $this->connection->executeQuery('DELETE FROM TestTaker WHERE 1=1');
    }
}
