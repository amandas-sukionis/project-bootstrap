<?php
namespace Application\Repository;

use Application\Entity\GalleryImage;
use Application\Entity\GalleryImageTag;
use Doctrine\ORM\EntityRepository;

class GalleryImageTagRepository extends EntityRepository
{

    /**
     * @param $tagString
     *
     * @return null|object
     * SELECT t0.tagString AS tagString1, t0.id AS id2
     * FROM GalleryImageTag t0
     * INNER JOIN imagesTags
     * ON t0.id = imagesTags.tagId
     * WHERE imagesTags.imageId = ?
     */
    public function getImageTagByTagString($tagString)
    {
        return $this->findOneBy(['tagString' => $tagString]);
    }

    public function getImageTagsBySearchWords($searchWords)
    {
        $likeString = 'u.tagString LIKE :word' . 0;
        foreach ($searchWords as $key => $word) {
            if ($key != 0) {
                $likeString .= ' OR u.tagString LIKE :word' . $key;
            }
        }
        $query = $this->getEntityManager()->createQuery(
            "SELECT u FROM Application\Entity\GalleryImageTag u WHERE " . $likeString
        );
        foreach ($searchWords as $key => $word) {
            $query->setParameter('word' . $key, $word . '%');
        }
        $tags = $query->getResult();
        return $tags;
    }

    public function addNewImageTag($tagString, GalleryImage $image)
    {
        $galleryImageTag = new GalleryImageTag();
        $galleryImageTag->setTagString($tagString);
        $galleryImageTag->addImage($image);

        $this->getEntityManager()->persist($galleryImageTag);
        $this->getEntityManager()->flush();
    }

    public function addImageToTag(GalleryImageTag $galleryImageTag, GalleryImage $image)
    {
        $galleryImageTag->addImage($image);

        $this->getEntityManager()->persist($galleryImageTag);
        $this->getEntityManager()->flush();
    }

}