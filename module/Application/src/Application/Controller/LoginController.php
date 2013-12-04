<?php

namespace Application\Controller;

use Zend\Authentication\Result;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class LoginController extends AbstractActionController
{
    protected $config;
    protected $userModel;
    protected $authenticationService;
    protected $translationService;

    public function createAdminUserFromConfigAction()
    {
        $config = $this->getConfig();
        if (!$this->getUserModel()->findUserByEmail($config['user']['email'])) {
            $this->getUserModel()->createDefaultAdmin($config['user']);
        }

        return new ViewModel();
    }

    public function registerAction() {
        if ($this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $registerForm = $this->getServiceLocator()->get('Application\Form\RegisterForm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $postData['userName'] = str_replace(' ', '', $postData['userName']);
            $registerForm->setData($postData);
            if ($registerForm->isValid()) {
                if ($this->getUserModel()->findUserByEmail($postData['email'])) {
                    $viewParams['error'] = $this->getTranslator()->translate('email_already_in_use');
                } else if ($this->getUserModel()->findUserByUserName($postData['userName'])) {
                    $viewParams['error'] = $this->getTranslator()->translate('username_already_taken');
                } else {
                    $this->getUserModel()->registerUser($postData);
                    $viewParams['success'] = $this->getTranslator()->translate('registration_successful');
                }
            }
        }
        $viewParams['registerForm'] = $registerForm;
        return $viewParams;
    }

    public function loginAction() {
        if ($this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $loginForm = $this->getServiceLocator()->get('Application\Form\LoginForm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $loginForm->setData($postData);
            if ($loginForm->isValid()) {
                $loginFormData = $loginForm->getData();
                $authenticationAdapter = $this->getAuthenticationService()->getAdapter();
                $authenticationAdapter->setIdentity($loginFormData['loginFormEmail']);
                $authenticationAdapter->setCredential($loginFormData['loginFormPassword']);
                $loginResult = $this->getAuthenticationService()->authenticate();
                if ($loginResult->getCode() == Result::SUCCESS) {
                    $this->redirect()->toRoute('home');
                } else {
                    $viewParams['error'] = $this->getTranslator()->translate('wrong_username_or_pass');
                }
            }
        }
        $viewParams['loginForm'] = $loginForm;
        return $viewParams;
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

    protected function getTranslator()
    {
        if (!$this->translationService) {
            $this->translationService = $this->getServiceLocator()->get('translator');
        }

        return $this->translationService;
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