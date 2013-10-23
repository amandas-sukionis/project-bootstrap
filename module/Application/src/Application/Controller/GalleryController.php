<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GalleryController extends AbstractActionController
{
    protected $userModel;
    protected $authenticationService;

    public function addCategory()
    {
        return new ViewModel();
    }

    public function indexAction()
    {
        $uploadImageForm = $this->getServiceLocator()->get('Application\Form\UploadImageForm');
        $viewModel = new ViewModel();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            $uploadImageForm->setData($postData);
            if ($uploadImageForm->isValid()) {

            }
        }
        $viewModel->setVariable('uploadImageForm', $uploadImageForm);

        return $viewModel;
    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

}