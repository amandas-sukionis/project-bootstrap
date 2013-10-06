<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class GalleryController extends AbstractActionController
{
    protected $userModel;
    protected $authenticationService;

    public function addCategoryAction()
    {

    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }
        return $this->authenticationService;
    }

}