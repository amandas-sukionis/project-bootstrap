<?php
namespace Application\Repository;

use Application\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function createDefaultAdmin($config, $salt, $password)
    {
        if (!$this->findUserByEmail($config['email'])) {
            $user = new User();
            $user->setEmail($config['email']);
            $user->setUserName($config['userName']);
            $user->setAlbumsCount(0);
            $user->setPassword($password);
            $user->setSalt($salt);
            $user->setAccessLevel(10);

            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();
        }
    }

    public function registerUser($postData, $salt, $password)
    {
        $user = new User();
        $user->setEmail($postData['email']);
        $user->setPassword($password);
        $user->setSalt($salt);
        $user->setAccessLevel(0);
        $user->setAlbumsCount(0);
        $user->setUserName($postData['userName']);
        $user->setFirstName($postData['firstName']);
        $user->setLastName($postData['lastName']);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findUserByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @param $userName
     *
     * @return null|object
     * SELECT t0.email AS email1, t0.userName AS userName2, t0.firstName AS firstName3, t0.lastName AS lastName4, t0.password AS password5, t0.salt AS salt6, t0.albumsCount AS albumsCount7, t0.accessLevel AS accessLevel8, t0.id AS id9
     * FROM User t0
     * WHERE t0.userName = ?
     * LIMIT 1
     */
    public function findUserByUserName($userName)
    {
        return $this->findOneBy(['userName' => $userName]);
    }

    /**
     * @param $id
     *
     * @return null|object
     *
     * SELECT t0.email AS email1, t0.userName AS userName2, t0.firstName AS firstName3, t0.lastName AS lastName4, t0.password AS password5, t0.salt AS salt6, t0.albumsCount AS albumsCount7, t0.accessLevel AS accessLevel8, t0.id AS id9
     * FROM User t0
     * WHERE t0.id = ?
     */
    public function findUserById($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getAllUsers()
    {
        return $this->findAll();
    }

}