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

    public function findUserByUserName($userName)
    {
        return $this->findOneBy(['userName' => $userName]);
    }

    public function findUserById($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function getAllUsers() {
        return $this->findAll();
    }

}