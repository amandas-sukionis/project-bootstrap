<?php
namespace Application\Model;

use Application\Entity\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserModel implements ServiceLocatorAwareInterface
{
    protected $objectManager;
    protected $serviceLocator;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function createAdmin($config)
    {
        if (!$this->findUserByUserName($config['userName'])) {
            $salt = self::getRandomSalt();
            $user = new User();
            $user->setUserName($config['userName']);
            $user->setPassword(self::getPasswordHash($config['password'], $salt));
            $user->setSalt($salt);

            $this->getObjectManager()->persist($user);
            $this->getObjectManager()->flush();
        }
    }

    public function findUserByUserName($userName)
    {
        return $user = $this->getObjectManager()->getRepository('Application\Entity\User')->findOneBy(
            [
            'userName' => $userName,
            ]
        );
    }

    public static function getPasswordHash($password, $salt)
    {
        $hashedSalt = hash('sha512', $salt);
        $doubleHashedSalt = hash('sha512', $hashedSalt);
        $password = $hashedSalt . hash('sha256', $password) . $doubleHashedSalt;
        $passwordHash = hash('sha512', $password);

        return $passwordHash;
    }

    protected static function getSaltString()
    {
        $saltVariables = [
            hash('sha512', mt_rand()),
            hash('sha512', microtime()),
            hash('sha512', uniqid(mt_rand(), true)),
        ];

        return implode('_', $saltVariables);
    }

    protected static function getRandomSalt()
    {
        $saltString = self::getSaltString();

        return hash('sha512', $saltString);
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }

}