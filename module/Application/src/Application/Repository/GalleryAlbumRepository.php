<?php
namespace Application\Repository;

use Application\Entity\GalleryAlbum;
use Application\Entity\User;
use Doctrine\ORM\EntityRepository;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class GalleryAlbumRepository extends EntityRepository
{
    /**
     * @param      $alias
     * @param User $user
     *
     * @return null|object
     *
     * SELECT t0.name AS name1, t0.alias AS alias2, t0.shortDescription AS shortDescription3, t0.fullDescription AS fullDescription4, t0.isPublic AS isPublic5, t0.location AS location6, t0.locationLat AS locationLat7, t0.locationLng AS locationLng8, t0.createDate AS createDate9, t0.imagesCount AS imagesCount10, t0.id AS id11, t0.mainImageId AS mainImageId12, t0.user_id AS user_id13
     * FROM GalleryAlbum t0
     * WHERE t0.alias = ?
     * AND t0.user_id = ?
     * LIMIT 1
     */
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

    /**
     * @param User $user
     *
     * @return array
     * SELECT g0_.name AS name0, g0_.alias AS alias1, g0_.shortDescription AS shortDescription2, g0_.fullDescription AS fullDescription3, g0_.isPublic AS isPublic4, g0_.location AS location5, g0_.locationLat AS locationLat6, g0_.locationLng AS locationLng7, g0_.createDate AS createDate8, g0_.imagesCount AS imagesCount9, g0_.id AS id10, g0_.mainImageId AS mainImageId11, g0_.user_id AS user_id12
     * FROM GalleryAlbum g0_
     * WHERE ? = g0_.user_id
     */
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

    /**
     * @param User $user
     *
     * @return array
     * SELECT g0_.name AS name0, g0_.alias AS alias1, g0_.shortDescription AS shortDescription2, g0_.fullDescription AS fullDescription3, g0_.isPublic AS isPublic4, g0_.location AS location5, g0_.locationLat AS locationLat6, g0_.locationLng AS locationLng7, g0_.createDate AS createDate8, g0_.imagesCount AS imagesCount9, g0_.id AS id10, g0_.mainImageId AS mainImageId11, g0_.user_id AS user_id12
     * FROM GalleryAlbum g0_
     * WHERE ? = g0_.user_id
     * AND ? = g0_.isPublic
     */
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