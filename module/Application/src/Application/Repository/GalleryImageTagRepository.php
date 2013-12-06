<?php
namespace Application\Repository;

use Application\Entity\GalleryImage;
use Application\Entity\GalleryImageTag;
use Doctrine\ORM\EntityRepository;

class GalleryImageTagRepository extends EntityRepository
{

    public function getImageTagByTagString($tagString)
    {
        return $this->findOneBy(['tagString' => $tagString]);
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