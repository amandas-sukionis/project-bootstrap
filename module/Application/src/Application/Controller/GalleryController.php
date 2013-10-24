<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GalleryController extends AbstractActionController
{
    protected $userModel;
    protected $authenticationService;
    protected $galleryModel;

    public function addCategory()
    {
        return new ViewModel();
    }

    public function indexAction()
    {
        $galleryAlbums = $this->getGalleryModel()->getAllGalleryAlbums();

        $isAdmin = false;
        if ($this->getAuthenticationService()->hasIdentity()) {
            $isAdmin = true;
        }

        return [
            'galleryAlbums' => $galleryAlbums,
            'isAdmin'       => $isAdmin,
        ];
    }

    public function albumAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);

        return [
            'albumImages' => $album->getImages(),
        ];
    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

    protected function getGalleryModel()
    {
        if (!$this->galleryModel) {
            $this->galleryModel = $this->getServiceLocator()->get('Application\Model\GalleryModel');
        }

        return $this->galleryModel;
    }

}