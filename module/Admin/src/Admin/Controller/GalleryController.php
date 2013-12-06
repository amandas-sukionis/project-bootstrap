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

    /**
     * @return JsonModel
     * returns upload progress
     */
    public function uploadProgressAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $progress = new ProgressBar\Upload\UploadProgress();
        $view = new JsonModel([
                              'id'     => $id,
                              'status' => $progress->getProgress($id),
                              ]);

        return $view;
    }

    /**
     * @return array
     * return all user albums
     */
    public function userAlbumsAction()
    {
        $userId = $this->params()->fromRoute('userId');
        $galleryAlbums = $this->getGalleryModel()->getAllGalleryAlbums();

        return [
            'userId'        => $userId,
            'galleryAlbums' => $galleryAlbums,
        ];
    }

    /**
     * @return array
     * adds album for user
     */
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
            'action'    => 'Add gallery album',
        ];
    }

    /**
     * @return array
     * returns all user album images
     */
    public function manageAlbumImagesAction()
    {
        $alias = $this->params()->fromRoute('alias');
        $userId = $this->params()->fromRoute('userId');
        $user = $this->getUserModel()->findUserById($userId);

        $albumImages = $this->getGalleryModel()->getAllImagesByAlbumAliasAndUser($alias, $user);

        return [
            'albumImages' => $albumImages,
            'alias'       => $alias,
            'userId'      => $userId,
        ];
    }

    /**
     * @return array
     * edit user album image
     */
    public function manageAlbumImageAction()
    {
        $imageAlias = $this->params()->fromRoute('imageAlias');
        $albumAlias = $this->params()->fromRoute('albumAlias');
        $image = $this->getGalleryModel()->getImageByAlias($imageAlias);
        $ImageForm = $this->getServiceLocator()->get('Application\Form\ImageForm');

        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $ImageForm->setHydrator(new DoctrineObject($entityManager));
        $ImageForm->bind($image);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $ImageForm->setData($postData);
            if ($ImageForm->isValid()) {
                $entityManager->flush();
                $this->redirect()->toRoute(
                    'admin/adminGallery/manageAlbumImages', ['alias' => $albumAlias, 'userId' => '1']
                );
            }
        }

        return [
            'imageForm' => $ImageForm,
        ];
    }

    /**
     * delete album image
     */
    public function deleteAlbumImageAction()
    {
        $imageAlias = $this->params()->fromRoute('imageAlias');
        $albumAlias = $this->params()->fromRoute('albumAlias');
        $userId = $this->params()->fromRoute('userId');
        $user = $this->getUserModel()->findUserById($userId);

        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($albumAlias, $user);

        if ($album) {
            $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
            if ($image) {
                $image = $image[0];
                $this->getGalleryModel()->deleteImage($image, $album);
            }
        }

        $this->redirect()->toRoute(
            'admin/adminGallery/manageAlbumImages', ['userId' => $userId, 'alias' => $albumAlias]
        );
    }

    /**
     * @return JsonModel
     * after image is uploaded, additional data is saved
     */
    public function finishImageUploadAction()
    {
        $imageForm = $this->getServiceLocator()->get('Application\Form\ImageForm');
        $alias = $this->params()->fromRoute('alias');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $imageForm->setData($postData);
            if ($imageForm->isValid()) {
                if ($this->getGalleryModel()->saveImageInfo($postData, $alias)) {
                    return new JsonModel(['status' => 'saved']);
                }
            }
        }

        return new JsonModel(['status' => 'fail']);
    }

    /**
     * @return array|JsonModel
     * uploads images
     */
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
            'alias'           => $alias,
            'userId'          => $userId,
        ];
    }

    /**
     * @return array
     * edit album action
     */
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

    /**
     * delete album action
     */
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

    /**
     * @return array|object|\Zend\Authentication\AuthenticationService
     * gets authentication service and stores it in variable
     */
    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

    /**
     * @return \Application\Model\GalleryModel|array|object
     * gets gallery model and stores it in variable
     */
    protected function getGalleryModel()
    {
        if (!$this->galleryModel) {
            $this->galleryModel = $this->getServiceLocator()->get('Application\Model\GalleryModel');
        }

        return $this->galleryModel;
    }

    /**
     * @return \Application\Model\UserModel|array|object
     * gets user model ant stores it in variable
     */
    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()->get('Application\Model\UserModel');
        }

        return $this->userModel;
    }

}