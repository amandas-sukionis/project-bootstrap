<?php
namespace Application\Repository;

use Application\Entity\GalleryImage;
use Application\Entity\GalleryImageVoteLog;
use Application\Entity\User;
use Doctrine\ORM\EntityRepository;

class GalleryImageVoteLogRepository extends EntityRepository
{

    public function getImageVoteLogByUserAndImage(User $user, GalleryImage $image)
    {
        return $this->findOneBy(['user' => $user, 'image' => $image]);
    }

    public function logVote(User $user, GalleryImage $image, $galleryImageVoteLog, $type)
    {
        if (!$galleryImageVoteLog) {
            $galleryImageVoteLog = new GalleryImageVoteLog();
        }

        $galleryImageVoteLog->setType($type);
        $galleryImageVoteLog->setActionDate(new \DateTime('NOW'));
        $galleryImageVoteLog->setUser($user);
        $galleryImageVoteLog->setImage($image);

        $this->getEntityManager()->persist($galleryImageVoteLog);
        $this->getEntityManager()->flush();
    }

}