<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Bio\FolderBundle\Entity\Folder;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130912120149 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FileBase ADD title VARCHAR(255) DEFAULT NULL, ADD address VARCHAR(2048) DEFAULT NULL");
        $this->addSql("INSERT INTO FileBase (parent_id, type, name, private, path, title, address) SELECT f.id, 'link', NULL, 0, NULL, l.title, l.address FROM Base l JOIN FileBase f ON f.name = 'sidebar' WHERE l.type = 'link'");
        $this->addSql("ALTER TABLE Base DROP address, DROP location");
        $this->addSql("DELETE FROM Base WHERE type = 'link'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        $this->addSql("ALTER TABLE Base ADD address VARCHAR(2048) DEFAULT NULL, ADD location VARCHAR(255) DEFAULT NULL");

        // TODO DOWN
        $this->addSql("ALTER TABLE FileBase DROP title, DROP address");
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function preUp(Schema $schema) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $sidebar = $em->createQueryBuilder()
            ->select('f')
            ->from('BioFolderBundle:Folder', 'f')
            ->where('f.id = 1')->getQuery()->getSingleResult();

        $sidebar->setName('sidebar');

        $mainpage = new Folder();
        $mainpage->setName('mainpage')
            ->setPrivate(false);
        $em->persist($mainpage);
        $em->flush();
    }
}
