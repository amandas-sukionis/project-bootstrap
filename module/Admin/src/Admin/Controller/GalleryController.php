<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;


class GalleryController extends AbstractActionController
{
    protected $userModel;
    protected $authenticationService;

    public function addAlbumAction()
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