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
        $image->setIsAlbumImage(false);

        $album->setImagesCount($album->getImagesCount() + 1);

        $this->getEntityManager()->persist($image);
        $this->getEntityManager()->flush();

        return $image->getAlias();
    }

    public function getImageByAlias($alias)
    {
        return $this->findOneBy(['alias' => $alias]);
    }

    public function saveImageInfo($postData, GalleryImage $image) {
        $image->setName($postData['name']);
        $image->setShortDescription($postData['shortDescription']);

        $this->getEntityManager()->flush();
    }
}