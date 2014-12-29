<?php

namespace Bio\FolderBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FolderRepository extends EntityRepository {

    /**
     * Returns the sidebar folder
     * @return Folder
     */
    public function getSidebarFolder() {
        return $this->findOneBy(array(
                'name' => 'sidebar',
                'parent' => null
            ));
    }

    /**
     * Returns the sidebar folder
     * @return Folder
     */
    public function getMainpageFolder() {
        return $this->findOneBy(array(
                'name' => 'mainpage',
                'parent' => null
            ));
    }
}
