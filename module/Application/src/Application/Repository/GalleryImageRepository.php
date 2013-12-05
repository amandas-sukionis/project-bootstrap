<?php
namespace Application\Repository;

use Application\Entity\GalleryAlbum;
use Application\Entity\GalleryImage;
use Doctrine\ORM\EntityRepository;

class GalleryImageRepository extends EntityRepository
{
    public function addNewImage($alias, $url, $thumbUrl, GalleryAlbum $album)
    {
        $image = new GalleryImage();
        $image->setAlbum($album);
        $image->setUrl($url);
        $image->setThumbUrl($thumbUrl);
        $image->setAlias($alias);
        $image->setIsPublic(0);
        $image->setVotesCount(0);
        $image->setIsAlbumImage(false);
        $image->setImageUploadDate(new \DateTime('NOW'));

        $album->setImagesCount($album->getImagesCount() + 1);

        $this->getEntityManager()->persist($image);
        $this->getEntityManager()->flush();

        return $image->getAlias();
    }

    public function getImageByAlias($alias)
    {
        return $this->findOneBy(['alias' => $alias]);
    }

    public function getAllImagesByAlbum(GalleryAlbum $album)
    {
        return $this->findBy(['album' => $album], ['id' => 'DESC']);
    }

    public function getAllPublicImagesByAlbum(GalleryAlbum $album)
    {
        return $this->findBy(['album' => $album, 'isPublic' => 1], ['id' => 'DESC']);
    }

    public function getImageByAlbumAndAlias(GalleryAlbum $album, $imageAlias)
    {
        return $this->findBy(['album' => $album, 'alias' => $imageAlias]);
    }

    public function saveImageInfo($postData, GalleryImage $image)
    {
        $image->setName($postData['name']);
        $image->setShortDescription($postData['shortDescription']);
        if ($postData['isAlbumImage']) {
            $oldAlbumMainImage = $image->getAlbum()->getMainImage();
            if ($oldAlbumMainImage) {
                $oldAlbumMainImage->setIsAlbumImage(false);
            }
            $image->getAlbum()->setMainImage($image);
            $image->setIsAlbumImage(true);
        } else {
            $oldAlbumMainImage = $image->getAlbum()->getMainImage();
            if ($image == $oldAlbumMainImage) {
                $image->getAlbum()->setMainImage(null);
            }
        }

        $this->getEntityManager()->flush();
    }

    public function deleteImage(GalleryImage $image, GalleryAlbum $album)
    {
        $album->setImagesCount($album->getImagesCount() - 1);

        $this->getEntityManager()->remove($image);
        $this->getEntityManager()->flush();
    }
}