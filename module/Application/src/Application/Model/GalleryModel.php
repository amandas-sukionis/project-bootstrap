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

    public function moveImageFiles($postData, $alias, User $user)
    {
        if (!file_exists('public/img/gallery')) {
            mkdir('public/img/gallery');
        }

        if (!file_exists('public/img/gallery/' . $user->getEmail())) {
            mkdir('public/img/gallery/' . $user->getEmail());
        }

        $destinationDir = 'public/img/gallery/' . $user->getEmail() . '/' . $alias;
        $destinationUrl = '/img/gallery/' . $user->getEmail() . '/' . $alias;
        if (!file_exists($destinationDir)) {
            mkdir($destinationDir);
        }

        $images = [];
        foreach ($postData['uploadImageFile'] as $image) {
            $tmpFile = $image['tmp_name'];
            $imageAlias = uniqid();
            $newFileName = $destinationDir . '/' . $imageAlias;
            $url = $destinationUrl . '/' . $imageAlias;
            move_uploaded_file($tmpFile, $newFileName);
            $finalWidthOfImage = 150;
            $thumbUrl = $this->createThumbnail($newFileName, $alias, $imageAlias, $user, $finalWidthOfImage);
            $finalWidthOfImage = 500;
            $this->createThumbnail($newFileName, $alias, $imageAlias . '_big', $user, $finalWidthOfImage);
            $album = $this->getAlbumByAliasAndUser($alias, $user);
            $imageAlias = $this->addNewImage($imageAlias, $url, $thumbUrl, $album);
            $images[] = ['alias' => $imageAlias, 'url' => $url, 'thumbUrl' => $thumbUrl];
        }

        return $images;
    }

    public function createThumbnail($filename, $alias, $imageAlias, User $user, $finalWidthOfImage) {
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

        $nx = $finalWidthOfImage;
        $ny = floor($oy * ($finalWidthOfImage / $ox));

        $nm = imagecreatetruecolor($nx, $ny);

        imagecopyresized($nm, $im, 0,0,0,0,$nx,$ny,$ox,$oy);

        $pathToThumbsDirectory = 'public/img/gallery/' . $user->getEmail() . '/' . $alias . '/thumbs';
        $thumbUrl = '/img/gallery/' . $user->getEmail() . '/' . $alias . '/thumbs';
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

    public function getImagesByAlbumAliasAndUser($alias, User $user)
    {
        $album = $this->getAlbumByAliasAndUser($alias, $user);

        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getImagesByAlbum($album);
    }

    public function getImageByAlbumAndNumber(GalleryAlbum $album, $number)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getImagesByAlbumAndNumber($album, $number);
    }

    public function getAlbumByAliasAndUser($alias, User $user)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAlbumByAliasAndUser($alias, $user);
    }

    public function getImageByAlias($alias)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getImageByAlias($alias);
    }

    public function addNewAlbum($postData, User $user)
    {
        $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->addNewAlbum($postData, $user);
    }

    public function deleteAlbum(GalleryAlbum $album, User $user)
    {
        $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->deleteAlbum($album, $user);
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }
}