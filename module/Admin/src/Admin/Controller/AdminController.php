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

    public function indexAction()
    {
        if ($this->getAuthenticationService()->hasIdentity()) {
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
                $authenticationAdapter->setIdentity($loginFormData['loginFormUsername']);
                $authenticationAdapter->setCredential($loginFormData['loginFormPassword']);
                $loginResult = $this->getAuthenticationService()->authenticate();
                if ($loginResult->getCode() == Result::SUCCESS) {
                    $this->redirect()->toRoute('admin/dashboard');
                } else {
                    $viewParams['error'] = $this->getTranslator()->translate('wrong_username_or_pass');
                }
            }
        }
        $viewParams['loginForm'] = $loginForm;

        return $viewParams;
    }

    protected function dashboardAction()
    {

    }

    protected function galleryAction()
    {
        $galleryCategories = null;

        return [
            'galleryCategories' => $galleryCategories,
        ];
    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

    protected function getTranslator()
    {
        if (!$this->translationService) {
            $this->translationService = $this->getServiceLocator()->get('translator');
        }

        return $this->translationService;
    }

}
