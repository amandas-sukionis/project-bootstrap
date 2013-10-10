<?php

namespace Admin\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;


class GalleryController extends AbstractActionController
{
    protected $galleryModel;
    protected $authenticationService;

    public function addAlbumAction()
    {
        $AlbumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $AlbumForm->setData($postData);
            if ($AlbumForm->isValid()) {
                $this->getGalleryModel()->addNewAlbum($postData);
                $this->redirect()->toRoute('admin/gallery');
            }
        }

        return [
            'albumForm' => $AlbumForm,
            'action' => 'add_gallery_album',
        ];
    }

    public function editAlbumAction()
    {
        $AlbumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $AlbumForm->setHydrator(new DoctrineObject($entityManager));
        $AlbumForm->bind($album);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $AlbumForm->setData($postData);
            if ($AlbumForm->isValid()) {
                $entityManager->flush();
                $this->redirect()->toRoute('admin/gallery');
            }
        }

        return [
            'albumForm' => $AlbumForm,
            'action' => 'edit_gallery_album',
        ];
    }

    public function deleteAlbumAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $entityManager->remove($album);
        $entityManager->flush();
        $this->redirect()->toRoute('admin/gallery');
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