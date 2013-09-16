<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Bio\PublicBundle\Entity\PublicGlobal;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130916121938 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE PublicGlobal (id INT AUTO_INCREMENT NOT NULL, showing LONGTEXT NOT NULL COMMENT '(DC2Type:array)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE PublicGlobal");
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function postUp(Schema $schema) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist(new PublicGlobal());
        $em->flush();
    }
}
