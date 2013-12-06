<?php
namespace Application\Model;

use Application\Entity\User;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserModel implements ServiceLocatorAwareInterface
{
    protected $objectManager;
    protected $serviceLocator;
    protected $authenticationService;

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param $config
     * creates default admin from config
     */
    public function createDefaultAdmin($config)
    {
        $salt = self::getRandomSalt();
        $password = self::getPasswordHash($config['password'], $salt);
        $this->getObjectManager()->getRepository('Application\Entity\User')->createDefaultAdmin($config, $salt, $password);
    }

    /**
     * @return mixed
     * gets all users
     */
    public function getAllUsers() {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->getAllUsers();
    }

    /**
     * @param $postData
     * registers a user from postdata
     */
    public function registerUser($postData)
    {
        $salt = self::getRandomSalt();
        $password = self::getPasswordHash($postData['password'], $salt);
        $this->getObjectManager()->getRepository('Application\Entity\User')->registerUser($postData, $salt, $password);
    }

    /**
     * @param $email
     *
     * @return mixed
     * finds a user by email
     */
    public function findUserByEmail($email)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->findUserByEmail($email);
    }

    /**
     * @return bool
     * get user if he is logged in
     */
    public function getUser()
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
            return $this->getAuthenticationService()->getIdentity();
        } else {
            return false;
        }
    }

    /**
     * @return bool
     * check if user is logged in
     */
    public function isLoggedIn()
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param User $user
     *
     * @return bool
     * check if logged in user is owner of album or image
     */
    public function isUserOwner(User $user)
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
            $authenticatedUser = $this->getAuthenticationService()->getIdentity();
            if ($user === $authenticatedUser) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $userName
     *
     * @return mixed
     * find a user by username
     */
    public function findUserByUserName($userName)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->findUserByUserName($userName);
    }

    /**
     * @param $id
     *
     * @return mixed
     * find a user by user id
     */
    public function findUserById($id)
    {
        return $this->getObjectManager()->getRepository('Application\Entity\User')->findUserById($id);
    }

    /**
     * @param $password
     * @param $salt
     *
     * @return string
     * creates password hash
     */
    public static function getPasswordHash($password, $salt)
    {
        $hashedSalt = hash('sha512', $salt);
        $doubleHashedSalt = hash('sha512', $hashedSalt);
        $password = $hashedSalt . hash('sha256', $password) . $doubleHashedSalt;
        $passwordHash = hash('sha512', $password);

        return $passwordHash;
    }

    /**
     * @return string
     * creates salt string
     */
    protected static function getSaltString()
    {
        $saltVariables = [
            hash('sha512', mt_rand()),
            hash('sha512', microtime()),
            hash('sha512', uniqid(mt_rand(), true)),
        ];

        return implode('_', $saltVariables);
    }

    /**
     * @return string
     * creates random salt
     */
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

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

}