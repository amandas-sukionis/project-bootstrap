<?php
namespace Application\Model;

use Application\Entity\GalleryAlbum;
use Application\Entity\User;
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

    public function saveImageInfo($postData, $alias) {
        $image = $this->getImageByAlias($alias);
        if ($image) {
            $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->saveImageInfo($postData, $image);
            return true;
        }
        return false;
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
            $url = '/img/gallery/' . $alias . '/' . $imageAlias;
            move_uploaded_file($tmpFile, $newFileName);
            $thumbUrl = $this->createThumbnail($newFileName, $alias, $imageAlias);
            $album = $this->getAlbumByAlias($alias);
            $alias = $this->addNewImage($imageAlias, $url, $thumbUrl, $album);
            $images[] = ['alias' => $alias, 'url' => $url, 'thumbUrl' => $thumbUrl];
        }

        return $images;
    }

    public function createThumbnail($filename, $alias, $imageAlias) {

        $final_width_of_image = 100;

        if(exif_imagetype($filename) == IMAGETYPE_JPEG) {
            $im = imagecreatefromjpeg($filename);
        } else if (exif_imagetype($filename) == IMAGETYPE_GIF) {
            $im = imagecreatefromgif($filename);
        } else if (exif_imagetype($filename) == IMAGETYPE_PNG) {
            $im = imagecreatefrompng($filename);
        } else {
            return null;
        }

        $ox = imagesx($im);
        $oy = imagesy($im);

        $nx = $final_width_of_image;
        $ny = floor($oy * ($final_width_of_image / $ox));

        $nm = imagecreatetruecolor($nx, $ny);

        imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);

        $pathToThumbsDirectory = 'public/img/gallery/' . $alias . '/thumbs';
        $thumbUrl = '/img/gallery/' . $alias . '/thumbs';
        if(!file_exists($pathToThumbsDirectory)) {
            if(!mkdir($pathToThumbsDirectory)) {
                die("There was a problem. Please try again!");
            }
        }
        imagejpeg($nm, $pathToThumbsDirectory . '/' . $imageAlias);
        return $thumbUrl . '/' . $imageAlias;
    }

    public function addNewImage($imageAlias, $url, $thumbUrl, GalleryAlbum $album)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->addNewImage($imageAlias, $url, $thumbUrl, $album);
    }

    public function getAllGalleryAlbums()
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllGalleryAlbums();
    }

    public function getAllUserGalleryAlbums(User $user)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllUserGalleryAlbums($user);
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

    public function getImageByAlias($alias)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getImageByAlias($alias);
    }

    public function addNewAlbum($postData, User $user)
    {
        $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->addNewAlbum($postData, $user);
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }
}