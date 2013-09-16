<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130916091124 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FileBase ADD mimetype VARCHAR(255) DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE FileBase DROP mimetype");
    }

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function postUp(Schema $schema) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $files = $em->createQueryBuilder()
            ->select('f')
            ->from('BioFolderBundle:File', 'f')
            ->getQuery()
            ->getResult();

        foreach ($files as $file) {
            $f = new File($file->getAbsolutePath());
            $file->setMime($f->getMimeType());
        }
        
        $em->flush();
    }
}
