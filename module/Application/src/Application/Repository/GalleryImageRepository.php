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

    /**
     * @param GalleryAlbum $album
     *
     * @return array
     *
     * SELECT t0.name AS name1, t0.url AS url2, t0.thumbUrl AS thumbUrl3, t0.votesCount AS votesCount4, t0.alias AS alias5, t0.shortDescription AS shortDescription6, t0.isAlbumImage AS isAlbumImage7, t0.isPublic AS isPublic8, t0.imageUploadDate AS imageUploadDate9, t0.id AS id10, t0.album_id AS album_id11
     * FROM GalleryImage t0
     * WHERE t0.album_id = ?
     * ORDER BY t0.id DESC
     */
    public function getAllImagesByAlbum(GalleryAlbum $album)
    {
        return $this->findBy(['album' => $album], ['id' => 'DESC']);
    }

    /**
     * @param GalleryAlbum $album
     *
     * @return array
     *
     * SELECT t0.name AS name1, t0.url AS url2, t0.thumbUrl AS thumbUrl3, t0.votesCount AS votesCount4, t0.alias AS alias5, t0.shortDescription AS shortDescription6, t0.isAlbumImage AS isAlbumImage7, t0.isPublic AS isPublic8, t0.imageUploadDate AS imageUploadDate9, t0.id AS id10, t0.album_id AS album_id11
     * FROM GalleryImage t0
     * WHERE t0.album_id = ?
     * AND t0.isPublic = ?
     * ORDER BY t0.id DESC
     */
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