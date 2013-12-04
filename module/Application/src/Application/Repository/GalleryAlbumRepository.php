<?php
namespace Application\Repository;

use Application\Entity\GalleryAlbum;
use Application\Entity\User;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class GalleryAlbumRepository extends EntityRepository
{
    public function getAlbumByAliasAndUser($alias, User $user)
    {
        return $this->findOneBy(['alias' => $alias, 'user' => $user]);
    }

    public function getAllGalleryAlbums()
    {
        return $this->findAll();
    }

    public function deleteAlbum(GalleryAlbum $album, User $user)
    {
        $user->setAlbumsCount($user->getAlbumsCount() - 1);

        $this->getEntityManager()->remove($album);
        $this->getEntityManager()->flush();
    }

    public function getAllUserGalleryAlbums(User $user)
    {
        $resultQuery = $this->getEntityManager()->createQuery(
            'SELECT u FROM Application\Entity\GalleryAlbum u WHERE :user = u.user'
        );
        $resultQuery->setParameters(
            array(
                 'user' => $user,
            )
        );

        $albums = $resultQuery->getResult();

        return $albums;
    }

    public function getAllPublicUserGalleryAlbums(User $user)
    {
        $resultQuery = $this->getEntityManager()->createQuery(
            'SELECT u FROM Application\Entity\GalleryAlbum u WHERE :user = u.user AND :isPublic = u.isPublic'
        );
        $resultQuery->setParameters(
            array(
                 'user' => $user,
                 'isPublic' => 1,
            )
        );

        $albums = $resultQuery->getResult();

        return $albums;
    }

    public function addNewAlbum($postData, User $user)
    {
        $hydrator = new DoctrineObject(
            $this->getEntityManager(),
            'Application\Entity\GalleryAlbum'
        );

        $postData = (Array)$postData;
        $galleryAlbum = new GalleryAlbum();
        $galleryAlbum = $hydrator->hydrate($postData, $galleryAlbum);
        $galleryAlbum->setCreateDate(new \DateTime('NOW'));
        $galleryAlbum->setAlias($this->getUniqueAlias($this->slugify($postData['name'])));
        $galleryAlbum->setImagesCount(0);
        $galleryAlbum->setUser($user);

        $user->setAlbumsCount($user->getAlbumsCount() + 1);

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