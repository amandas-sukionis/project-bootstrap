<?php

namespace Admin\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\ProgressBar;
use Zend\View\Model\JsonModel;

/**
 * @property \Zend\Authentication\AuthenticationService    $authenticationService
 * @property \Application\Model\GalleryModel               $galleryModel
 */
class GalleryController extends AbstractActionController
{
    protected $galleryModel;
    protected $authenticationService;

    public function uploadProgressAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $progress = new ProgressBar\Upload\UploadProgress();
        $view = new JsonModel(array(
                                   'id'     => $id,
                                   'status' => $progress->getProgress($id),
                              ));

        return $view;
    }

    public function addAlbumAction()
    {
        $AlbumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $AlbumForm->setData($postData);
            if ($AlbumForm->isValid()) {
                $this->getGalleryModel()->addNewAlbum($postData);
                $this->redirect()->toRoute('admin/adminGallery');
            }
        }

        return [
            'albumForm' => $AlbumForm,
            'action'    => 'add_gallery_album',
        ];
    }

    public function manageAlbumImagesAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $albumImages = $this->getGalleryModel()->getImagesByAlbumAlias($alias);

        return [
            'albumImages' => $albumImages,
            'alias'       => $alias,
        ];
    }

    public function manageAlbumImageAction()
    {
        //TODO
        //$alias = $this->params()->fromRoute('alias');
        //$image = $this->getGalleryModel()->getAlbumImageByAlias($alias);
        return [
            'image' => 'image',
        ];
    }

    public function uploadAlbumImagesAction()
    {
        $uploadImageForm = $this->getServiceLocator()->get('Application\Form\UploadImageForm');
        $alias = $this->params()->fromRoute('alias');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $uploadImageForm->setData($postData);

            if ($uploadImageForm->isValid()) {
                $uploadImageForm->getData();
                $images = $this->getGalleryModel()->moveImageFiles($postData, $alias);

                return new JsonModel(['images' => $images]);
            }
        }

        return [
            'uploadImageForm' => $uploadImageForm,
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
                $this->redirect()->toRoute('admin/adminGallery');
            }
        }

        return [
            'albumForm' => $AlbumForm,
            'action'    => 'edit_gallery_album',
        ];
    }

    public function deleteAlbumAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAlias($alias);
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $entityManager->remove($album);
        $entityManager->flush();
        $this->redirect()->toRoute('admin/adminGallery');
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