<?php
namespace Application\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GalleryModel implements ServiceLocatorAwareInterface
{
    protected $objectManager;
    protected $serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function uploadImageFile($postData)
    {
        if (!file_exists('public/img/gallery')) {
            mkdir('public/img/gallery');
        }

        $oldName = $postData['uploadImageFile']['name'];
        $tmpFile = $postData['uploadImageFile']['tmp_name'];

        $newFileName = 'public/img/gallery/' . $oldName;
        $url = '/img/gallery/' . $oldName;
        if (file_exists($newFileName)) {
            $newFileName = 'public/img/gallery/' . date('Y-m-d') . '-' . $oldName;
            $url = '/img/gallery/' . date('Y-m-d') . '-' . $oldName;
        }

        move_uploaded_file($tmpFile, $newFileName);

        return $url;
    }

    public function getAllGalleryAlbums()
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllGalleryAlbums();
    }

    public function getAlbumByAlias($alias)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAlbumByAlias($alias);
    }

    public function addNewAlbum($postData)
    {
        $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->addNewAlbum($postData);
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }
}