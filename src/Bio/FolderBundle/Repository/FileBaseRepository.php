<?php

namespace Bio\FolderBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Bio\FolderBundle\Entity\Folder;
use Bio\FolderBundle\Entity\FileBase;

class FileBaseRepository extends EntityRepository {

    /**
     * Saves a new filebase
     * @param  {FileBase} $entity
     * @param {Folder} $parent
     * @return {Array} - result
     */
    public function create(FileBase $entity, Folder $parent) {
        $em = $this->getEntityManager();

        $type = $this->getType($entity);
        $isFile = $type === 'File';
        $isFolder = $type === 'Folder';
        $isLink = $type === 'Link';

        if ($isFolder) {
            $entity->setPrivate($parent->getPrivate());
        }

        $entity->setParent($parent);
        $parent->addChild($entity);
        $em->persist($entity);

        try {
            $em->flush();
            return array(
                'success' => true,
                'message' => $isFile ? 'File uploaded as "'.$entity->getName().'".' :
                             ($isFolder ? 'Folder "'.$entity->getName().'" created.' :
                             ($isLink ? 'Link added.' : ''))
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $isFolder ? 'Folder could not be added.' :
                             ($isLink ? 'Link could not be added.' :
                             ($isFile ? 'File could not be added.' :
                             'An unknown error occured.'))
            );
        }
    }

    /**
     * Deletes a FileBase entity
     * @param  {FileBase} $entity
     * @return {Array} - result
     */
    public function delete(FileBase $entity) {
        if (!$entity) {
            return array(
                'success' => false,
                'message' => 'Could not find that file.'
            );
        }

        $type = $this->getType($entity);

        if ($entity->getParent() === null) {
            return array(
                'success' => false,
                'message' => 'Root folders cannot be deleted.'
            );
        }

        try {
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();

            return array(
                'success' => true,
                'message' => $type.' was deleted.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => $type.' was not be deleted.'
            );
        }
    }

    /**
     * Removes all files and subfolders
     * TODO make single query
     * @return {Array} - result
     */
    public function clearAll() {
        $em = $this->getEntityManager();
        $repo = $em->getRepository('BioFolderBundle:Folder');

        try {
            $sidebar = $repo->getSidebarFolder();
            $mainpage = $repo->getMainpageFolder();

            foreach($sidebar->getChildren() as $child) {
                $em->remove($child);
            }

            foreach($mainpage->getChildren() as $child) {
                $em->remove($child);
            }

            $em->flush();

            return array(
                'success' => true,
                'message' => 'All folders cleared.'
            );
        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => 'Oops. Folders were not deleted.'
            );
        }
    }

    /**
     * Returns the type of the FileBase
     * @param {FileBase} $entity
     * @return {String|null}
     */
    public function getType(FileBase $entity) {
        // figure out type
        $class = get_class($entity);
        $isFile = $class === 'Bio\FolderBundle\Entity\File';
        $isFolder = $class === 'Bio\FolderBundle\Entity\Folder';
        $isLink = $class === 'Bio\FolderBundle\Entity\Link';

        return $isFile ? "File" :
               ($isFolder ? "Folder" :
               ($isLink ? "Link" : null));
    }
}
