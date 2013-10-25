<?php
namespace Application\Model;

use Application\Entity\GalleryAlbum;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @property \Doctrine\ORM\EntityManager $objectManager
 */
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

    public function moveImageFiles($postData, $alias)
    {
        if (!file_exists('public/img/gallery')) {
            mkdir('public/img/gallery');
        }

        if (!file_exists('public/img/gallery/' . $alias)) {
            mkdir('public/img/gallery/' . $alias);
        }

        $images = [];
        foreach ($postData['uploadImageFile'] as $image) {
            $tmpFile = $image['tmp_name'];
            $imageAlias = uniqid();
            $newFileName = 'public/img/gallery/' . $alias . '/' . $imageAlias;
            $url = '/img/gallery/' . $alias . '/' . uniqid();
            move_uploaded_file($tmpFile, $newFileName);

            $album = $this->getAlbumByAlias($alias);
            $id = $this->addNewImage($imageAlias, $url, $album);

            $images[] = ['id' => $id, 'url' => $url];
        }

        return $images;
    }

    public function addNewImage($url, GalleryAlbum $album)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->addNewImage($url, $album);
    }

    public function getAllGalleryAlbums()
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllGalleryAlbums();
    }

    public function getImagesByAlbumAlias($alias)
    {
        $album = $this->getAlbumByAlias($alias);

        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->findBy(['album' => $album]);
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