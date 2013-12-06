<?php
namespace Admin\Controller;

use Zend\Authentication\Result;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * @property \Zend\Authentication\AuthenticationService    $authenticationService
 *
 */
class AdminController extends AbstractActionController
{
    protected $authenticationService;
    protected $translationService;
    protected $galleryModel;
    protected $userModel;

    /**
     * @return array
     * login action
     */
    public function indexAction()
    {
        if ($this->getAuthenticationService()->hasIdentity()
            && $this->getAuthenticationService()->getIdentity()->getAccessLevel() >= 10
        ) {
            $this->redirect()->toRoute('admin/dashboard');
        }
        $viewParams = [];
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
                    $this->redirect()->toRoute('admin/dashboard');
                } else {
                    $viewParams['error'] = $this->getTranslator()->translate('Wrong username or password');
                }
            }
        }
        $viewParams['loginForm'] = $loginForm;

        return $viewParams;
    }

    /**
     * No logic or parameters to return
     */
    protected function dashboardAction()
    {

    }

    /**
     * @return array
     * returns all users
     */
    protected function adminGalleryAction()
    {
        $users = $this->getUserModel()->getAllUsers();

        return [
            'users' => $users,
        ];
    }

    /**
     * @return array|object|\Zend\Authentication\AuthenticationService
     * gets authentication service and stores in variable
     */
    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

    /**
     * @return array|object
     * gets translator and stores in variable
     */
    protected function getTranslator()
    {
        if (!$this->translationService) {
            $this->translationService = $this->getServiceLocator()->get('translator');
        }

        return $this->translationService;
    }

    /**
     * @return array|object
     * gets user model and stores it in variable
     */
    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()->get('Application\Model\UserModel');
        }

        return $this->userModel;
    }
}
