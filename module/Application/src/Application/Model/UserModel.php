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

    public function createDefaultAdmin($config)
    {
        $salt = self::getRandomSalt();
        $password = self::getPasswordHash($config['password'], $salt);
        $this->getObjectManager()->getRepository('Application\Entity\User')->createDefaultAdmin($config, $salt, $password);
    }

    public function getAllUsers() {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->getAllUsers();
    }

    public function registerUser($postData)
    {
        $salt = self::getRandomSalt();
        $password = self::getPasswordHash($postData['password'], $salt);
        $this->getObjectManager()->getRepository('Application\Entity\User')->registerUser($postData, $salt, $password);
    }

    public function findUserByEmail($email)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->findUserByEmail($email);
    }

    public function findUserById($id)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->findUserById($id);
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