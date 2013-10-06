<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class LoginController extends AbstractActionController
{
    protected $config;
    protected $userModel;
    protected $authenticationService;

    public function createAdminUserFromConfigAction()
    {
        $config = $this->getConfig();
        $this->getUserModel()->createAdmin($config['user']);

        return new ViewModel();
    }

    public function logoutAction()
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
            $this->getAuthenticationService()->clearIdentity();
        }

        return $this->redirect()->toRoute('home');
    }

    protected function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->getServiceLocator()->get('Config');
        }
        return $this->config;
    }

    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()->get('Application\Model\UserModel');
        }
        return $this->userModel;
    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }
        return $this->authenticationService;
    }

}