<?php
namespace Application\Model;

use Application\Entity\GalleryAlbum;
use Application\Entity\GalleryImage;
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

    public function checkAlbumImage($postData, $album, $image) {
        if ($postData['isAlbumImage']) {
            $oldAlbumMainImage = $album->getMainImage();
            if ($oldAlbumMainImage) {
                $oldAlbumMainImage->setIsAlbumImage(false);
            }
            $album->setMainImage($image);
        } else {
            $oldAlbumMainImage = $album->getMainImage();
            if ($image == $oldAlbumMainImage) {
                $album->setMainImage(null);
            }
        }
    }

    public function checkImageTags($postData, $album, $image) {
        $tags = $image->getTags();
        foreach ($tags as $tag) {
            $image->removeTag($tag);
            $tag->removeImage($image);
        }

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $entityManager->flush();
        $hiddenTags = $postData['hiddenTags'];

        if ($hiddenTags) {
            $pattern = '/[^a-z,]+/';
            $replacement = '';
            $hiddenTags = preg_replace($pattern, $replacement, $hiddenTags);
            $tags = explode(',', $hiddenTags);

            foreach ($tags as $tagString) {
                if (strlen($tagString) > 2 && strlen($tagString) < 13) {
                    $galleryTag = $this->getObjectManager()->getRepository('Application\Entity\GalleryImageTag')->getImageTagByTagString($tagString);
                    if (!$galleryTag) {
                        $this->getObjectManager()->getRepository('Application\Entity\GalleryImageTag')->addNewImageTag($tagString, $image);
                    } else {
                        if (!$image->getTags()->contains($galleryTag)) {
                            $this->getObjectManager()->getRepository('Application\Entity\GalleryImageTag')->addImageToTag($galleryTag, $image);
                        }
                    }
                }
            }
        }
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
            $finalWidthOfImage = 250;
            $cropHeight = 200;
            $this->createThumbnail($newFileName, $alias, $imageAlias . '_big', $user, $finalWidthOfImage);
            $this->createSameSizeThumbnail($newFileName, $finalWidthOfImage, $cropHeight, $user, $alias, $imageAlias);
            $album = $this->getAlbumByAliasAndUser($alias, $user);
            $imageAlias = $this->addNewImage($imageAlias, $url, $thumbUrl, $album);
            $images[] = ['alias' => $imageAlias, 'url' => $url, 'thumbUrl' => $thumbUrl];
        }

        return $images;
    }

    public function createSameSizeThumbnail($oldname, $thumbw, $thumbh, User $user, $alias, $imageAlias)
    {
        if(exif_imagetype($oldname) == IMAGETYPE_JPEG) {
            $resimage = imagecreatefromjpeg($oldname);
        } else if (exif_imagetype($oldname) == IMAGETYPE_GIF) {
            $resimage = imagecreatefromgif($oldname);
        } else if (exif_imagetype($oldname) == IMAGETYPE_PNG) {
            $resimage = imagecreatefrompng($oldname);
        } else {
            return null;
        }

        // Dimension of intermediate thumbnail

        $nh = $thumbh;
        $nw = $thumbw;

        $size = getImageSize($oldname);
        $w = $size[0];
        $h = $size[1];

        // Applying calculations to dimensions of the image

        $ratio = $h / $w;
        $nratio = $nh / $nw;

        if($ratio > $nratio)
        {
            $x = intval($w * $nh / $h);
            if ($x < $nw)
            {
                $nh = intval($h * $nw / $w);
            }
            else
            {
                $nw = $x;
            }
        }
        else
        {
            $x = intval($h * $nw / $w);
            if ($x < $nh)
            {
                $nw = intval($w * $nh / $h);
            }
            else
            {
                $nh = $x;
            }
        }

        // Building the intermediate resized thumbnail
        $newimage = imagecreatetruecolor($nw, $nh);  // use alternate function if not installed
        imageCopyResampled($newimage, $resimage,0,0,0,0,$nw, $nh, $w, $h);

        // Making the final cropped thumbnail

        $viewimage = imagecreatetruecolor($thumbw, $thumbh);
        imagecopy($viewimage, $newimage, 0, 0, 0, 0, $nw, $nh);

        $pathToThumbsDirectory = 'public/img/gallery/' . $user->getEmail() . '/' . $alias . '/thumbs';
        if(!file_exists($pathToThumbsDirectory)) {
            if(!mkdir($pathToThumbsDirectory)) {
                die("There was a problem. Please try again!");
            }
        }
        // saving
        imagejpeg($viewimage, $pathToThumbsDirectory . '/' . $imageAlias . '_cropped');
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

    public function getAllImagesByAlbum(GalleryAlbum $album)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getAllImagesByAlbum($album);
    }

    public function getAllPublicImagesByAlbum(GalleryAlbum $album)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getAllPublicImagesByAlbum($album);
    }

    public function getAllPublicUserGalleryAlbums(User $user)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllPublicUserGalleryAlbums($user);
    }

    public function getAllUserGalleryAlbums(User $user)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryAlbum')->getAllUserGalleryAlbums($user);
    }

    public function getAllImagesByAlbumAliasAndUser($alias, User $user)
    {
        $album = $this->getAlbumByAliasAndUser($alias, $user);
        if ($album) {
            return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getAllImagesByAlbum($album);
        } else {
            return null;
        }
    }

    public function getAllPublicImagesByAlbumAliasAndUser($alias, User $user)
    {
        $album = $this->getAlbumByAliasAndUser($alias, $user);
        if ($album) {
            return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getAllPublicImagesByAlbum($album);
        } else {
            return null;
        }
    }

    public function getImageByAlbumAndAlias(GalleryAlbum $album, $imageAlias)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->getImageByAlbumAndALias($album, $imageAlias);
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

    public function deleteImage(GalleryImage $image, GalleryAlbum $album)
    {
        if ($album->getMainImage() == $image) {
            $album->setMainImage(null);
        }
        $this->getObjectManager()->getRepository('Application\Entity\GalleryImage')->deleteImage($image, $album);
    }

    public function getImageVoteLogByUserAndImage(User $user, GalleryImage $image)
    {
        return $voteLog = $this->getObjectManager()->getRepository('Application\Entity\GalleryImageVoteLog')->getImageVoteLogByUserAndImage($user, $image);
    }

    public function upVoteImage(User $user, GalleryImage $image, $galleryImageVoteLog, $status)
    {
        $type = 'upvote';
        if (!$galleryImageVoteLog) {
            $image->setVotesCount($image->getVotesCount() + 1);
        } else if ($status == 'wasUp') {
            $type = 'neutral';
            $image->setVotesCount($image->getVotesCount() - 1);
        } else if ($status == 'wasDown') {
            $image->setVotesCount($image->getVotesCount() + 2);
        } else if ($status == 'wasNeutral') {
            $image->setVotesCount($image->getVotesCount() + 1);
        }

        $this->getObjectManager()->getRepository('Application\Entity\GalleryImageVoteLog')->logVote($user, $image, $galleryImageVoteLog, $type);
    }

    public function downVoteImage(User $user, GalleryImage $image, $galleryImageVoteLog, $status)
    {
        $type = 'downvote';
        if (!$galleryImageVoteLog) {
            $image->setVotesCount($image->getVotesCount() - 1);
        } else if ($status == 'wasUp') {
            $image->setVotesCount($image->getVotesCount() - 2);
        } else if ($status == 'wasDown') {
            $image->setVotesCount($image->getVotesCount() + 1);
            $type = 'neutral';
        } else if ($status == 'wasNeutral') {
            $image->setVotesCount($image->getVotesCount() - 1);
        }

        $this->getObjectManager()->getRepository('Application\Entity\GalleryImageVoteLog')->logVote($user, $image, $galleryImageVoteLog, $type);
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }
}