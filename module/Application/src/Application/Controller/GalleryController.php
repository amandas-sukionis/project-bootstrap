<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
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
        $logged = false;
        $galleryAlbums = null;
        if ($this->getAuthenticationService()->hasIdentity()) {
            $logged = true;
            $user = $this->getAuthenticationService()->getIdentity();
            $galleryAlbums = $this->getGalleryModel()->getAllUserGalleryAlbums($user);
        }

        return [
            'galleryAlbums' => $galleryAlbums,
            'logged'       => $logged,
        ];
    }

    public function addAlbumAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $albumForm->setData($postData);
            if ($albumForm->isValid()) {
                $user = $this->getAuthenticationService()->getIdentity();
                $this->getGalleryModel()->addNewAlbum($postData, $user);
                $this->redirect()->toRoute('home/gallery');
            }
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'add_gallery_album',
        ];
    }

    public function editAlbumAction()
    {
        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $albumForm->setHydrator(new DoctrineObject($entityManager));
        $albumForm->bind($album);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $albumForm->setData($postData);
            if ($albumForm->isValid()) {
                $entityManager->flush();
                $this->redirect()->toRoute('home/gallery');
            }
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'edit_gallery_album',
        ];
    }

    public function albumAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);
        $albumImages = $this->getGalleryModel()->getImagesByAlbumAlias($alias);

        return [
            'albumImages' => $albumImages,
            'album'       => $album,
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