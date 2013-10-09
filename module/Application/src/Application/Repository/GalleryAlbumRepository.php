<?php
namespace Application\Repository;

use Application\Entity\GalleryAlbum;
use Doctrine\ORM\EntityRepository;

class GalleryAlbumRepository extends EntityRepository
{
    public function getAlbumByAlias ($alias) {
        return $this->findOneBy(['alias' => $alias]);
    }

    public function getAllGalleryAlbums () {
        return $this->findAll();
    }

    public function addNewAlbum ($postData) {
        $galleryAlbum = new GalleryAlbum();
        $galleryAlbum->setName($postData['name']);
        $galleryAlbum->setLocation($postData['location']);
        $galleryAlbum->setShortDescription($postData['shortDescription']);
        $galleryAlbum->setFullDescription($postData['fullDescription']);
        $galleryAlbum->setCreateDate(new \DateTime('NOW'));
        $galleryAlbum->setAlias($this->getUniqueAlias($this->slugify($postData['name'])));

        $this->getEntityManager()->persist($galleryAlbum);
        $this->getEntityManager()->flush();
    }

    public function getUniqueAlias($alias)
    {
        $query = $this->getEntityManager()->createQuery(
            "SELECT COUNT(u) FROM Application\Entity\GalleryAlbum u WHERE u.alias LIKE :alias"
        );
        $query->setParameter('alias', $alias . '%');
        $rows = $query->getSingleScalarResult();

        if ($rows != 0) {
            $alias = $alias . '-' . ($rows + 1);
        }

        return $alias;
    }

    protected function slugify($text)
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        $text = trim($text, '-');

        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        $text = strtolower($text);

        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}