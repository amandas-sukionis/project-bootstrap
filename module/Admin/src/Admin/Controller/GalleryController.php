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
 * @property \Application\Model\UserModel                  $userModel
 */
class GalleryController extends AbstractActionController
{
    protected $galleryModel;
    protected $userModel;
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

    public function userAlbumsAction()
    {
        $userId = $this->params()->fromRoute('userId');
        $galleryAlbums = $this->getGalleryModel()->getAllGalleryAlbums();

        return [
            'userId'        => $userId,
            'galleryAlbums' => $galleryAlbums,
        ];
    }

    public function addAlbumAction()
    {
        $userId = $this->params()->fromRoute('userId');
        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $albumForm->setData($postData);
            if ($albumForm->isValid()) {
                $user = $this->getUserModel()->findUserById($userId);
                $this->getGalleryModel()->addNewAlbum($postData, $user);
                $this->redirect()->toRoute('admin/adminGallery/userAlbums', ['userId' => $userId]);
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
        $userId = $this->params()->fromRoute('userId');
        $user = $this->getUserModel()->findUserById($userId);

        $albumImages = $this->getGalleryModel()->getImagesByAlbumAliasAndUser($alias, $user);

        return [
            'albumImages' => $albumImages,
            'alias'       => $alias,
            'userId'      => $userId,
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

    public function finishImageUploadAction()
    {
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
        $userId = $this->params()->fromRoute('userId');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $uploadImageForm->setData($postData);

            if ($uploadImageForm->isValid()) {
                $uploadImageForm->getData();
                $user = $this->getUserModel()->findUserById($userId);
                $images = $this->getGalleryModel()->moveImageFiles($postData, $alias, $user);

                return new JsonModel(['images' => $images]);
            }
        }

        return [
            'uploadImageForm' => $uploadImageForm,
        ];
    }

    public function editAlbumAction()
    {
        $userId = $this->params()->fromRoute('userId');
        $user = $this->getUserModel()->findUserById($userId);
        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $alias = $this->params()->fromRoute('alias');
        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);

        if ($album) {
            $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $albumForm->setHydrator(new DoctrineObject($entityManager));
            $albumForm->bind($album);

            $request = $this->getRequest();
            if ($request->isPost()) {
                $postData = $request->getPost();
                $albumForm->setData($postData);
                if ($albumForm->isValid()) {
                    $entityManager->flush();
                    $this->redirect()->toRoute('admin/adminGallery/userAlbums', ['userId' => $userId]);
                }
            }
        } else {
            $this->redirect()->toRoute('admin/adminGallery/addAlbum', ['userId' => $userId]);
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'edit_gallery_album',
        ];
    }

    public function deleteAlbumAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $userId = $this->params()->fromRoute('userId');
        $user = $this->getUserModel()->findUserById($userId);
        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);

        if ($album) {
            $this->getGalleryModel()->deleteAlbum($album, $user);
        }

        $this->redirect()->toRoute('admin/adminGallery/userAlbums', ['userId' => $userId]);
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

    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()->get('Application\Model\UserModel');
        }

        return $this->userModel;
    }

}