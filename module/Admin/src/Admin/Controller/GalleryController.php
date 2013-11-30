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
        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $albumForm->setData($postData);
            if ($albumForm->isValid()) {
                $this->getGalleryModel()->addNewAlbum($postData, null);
                $this->redirect()->toRoute('admin/adminGallery');
            }
        }

        return [
            'albumForm' => $albumForm,
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
        $imageAlias = $this->params()->fromRoute('imageAlias');
        $albumAlias = $this->params()->fromRoute('albumAlias');
        $image = $this->getGalleryModel()->getImageByAlias($imageAlias);
        $saveImageForm = $this->getServiceLocator()->get('Application\Form\SaveImageForm');

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $saveImageForm->setHydrator(new DoctrineObject($entityManager));
        $saveImageForm->bind($image);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $saveImageForm->setData($postData);
            if ($saveImageForm->isValid()) {
                $entityManager->flush();
                $this->redirect()->toRoute('admin/adminGallery/manageAlbumImages', ['alias' => $albumAlias]);
            }
        }

        return [
            'saveImageForm' => $saveImageForm,
        ];
    }

    public function finishImagesUploadAction() {
        $saveImageForm = $this->getServiceLocator()->get('Application\Form\SaveImageForm');
        $alias = $this->params()->fromRoute('alias');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $saveImageForm->setData($postData);
            if ($saveImageForm->isValid()) {
                if ($this->getGalleryModel()->saveImageInfo($postData, $alias)) {
                    return new JsonModel(['status' => 'saved']);
                }
            }
        }
        return new JsonModel(['status' => 'fail']);
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
                $this->redirect()->toRoute('admin/adminGallery');
            }
        }

        return [
            'albumForm' => $albumForm,
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