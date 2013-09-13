<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

use Bio\InfoBundle\Entity\CourseSection;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20130913111233 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

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

    public function setContainer(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function postUp(Schema $schema) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $sections = $em->createQueryBuilder()->select('s')->from('BioInfoBundle:Section', 's')->getQuery()->getResult();

        $cSections = [];

        foreach ($sections as $section) {
            if (!in_array(substr($section->getName(), 0, 1), $cSections)) {
                $cSection = new CourseSection();
                $cSection->setName(substr($section->getName(), 0, 1))
                    ->setDays(['m'])
                    ->setStartTime(new \DateTime('midnight'))
                    ->setEndTime(new \DateTime('midnight'))
                    ->setBldg("HCK\tHitchcock Hall")
                    ->setRoom("0");
                $em->persist($cSection);
                $cSections[] = $cSection->getName();
            }
        }
        $em->flush();
    }


}
