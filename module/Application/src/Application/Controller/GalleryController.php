<?php

namespace Application\Controller;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
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
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);

        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $galleryAlbums = $this->getGalleryModel()->getAllUserGalleryAlbums($user);
            } else {
                $galleryAlbums = $this->getGalleryModel()->getAllPublicUserGalleryAlbums($user);
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'userName'      => $user->getUserName(),
            'owner'         => $isOwner,
            'galleryAlbums' => $galleryAlbums,
        ];
    }

    public function albumAction()
    {
        $viewParams = [];
        $userName = $this->params()->fromRoute('userName');
        $alias = $this->params()->fromRoute('alias');
        $user = $this->getUserModel()->findUserByUserName($userName);

        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $galleryAlbums = $this->getGalleryModel()->getAllUserGalleryAlbums($user);
            } else {
                $galleryAlbums = $this->getGalleryModel()->getAllPublicUserGalleryAlbums($user);
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        if ($this->getAuthenticationService()->hasIdentity()) {
            $user = $this->getAuthenticationService()->getIdentity();
            $viewParams['userName'] = $user->getUserName();
            if ($userName == $user->getUserName()) {
                $viewParams['owner'] = true;
                $galleryAlbums = $this->getGalleryModel()->getAllUserGalleryAlbums($user);
            }
        }

        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);

        if ($album) {
            $albumImages = $this->getGalleryModel()->getImagesByAlbumAliasAndUser($alias, $user);
        } else {
            return $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
        }

        return [
            'albumImages' => $albumImages,
            'album'       => $album,
        ];
    }

    public function addAlbumAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
                $request = $this->getRequest();
                if ($request->isPost()) {
                    $postData = $request->getPost();
                    $albumForm->setData($postData);
                    if ($albumForm->isValid()) {
                        $user = $this->getAuthenticationService()->getIdentity();
                        $this->getGalleryModel()->addNewAlbum($postData, $user);
                        $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
                    }
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'add_gallery_album',
        ];
    }

    public function uploadImagesAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

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
                $user = $this->getAuthenticationService()->getIdentity();
                $images = $this->getGalleryModel()->moveImageFiles($postData, $alias, $user);

                return new JsonModel(['images' => $images]);
            }
        }

        return [
            'uploadImageForm' => $uploadImageForm,
        ];
    }

    public function finishImageUploadAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $saveImageForm = $this->getServiceLocator()->get('Application\Form\SaveImageForm');
        $alias = $this->params()->fromRoute('alias');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postData = $request->getPost();
            $saveImageForm->setData($postData);
            if ($saveImageForm->isValid()) {
                $image = $this->getGalleryModel()->getImageByAlias($alias);
                if ($image) {
                    if ($image->getAlbum()->getUser() === $this->getAuthenticationService()->getIdentity()) {
                        if ($this->getGalleryModel()->saveImageInfo($postData, $alias)) {
                            return new JsonModel(['status' => 'saved']);
                        }
                    }
                }
            }
        }

        return new JsonModel(['status' => 'fail']);
    }

    public function editAlbumAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
        $user = $this->getAuthenticationService()->getIdentity();
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
                    $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
                }
            }
        } else {
            $this->redirect()->toRoute('home/gallery/addAlbum', ['userName' => $user->getUserName()]);
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'edit_gallery_album',
        ];
    }

    public function deleteAlbumAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            $this->redirect()->toRoute('home');
        }

        $alias = $this->params()->fromRoute('alias');
        $user = $this->getAuthenticationService()->getIdentity();
        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);

        if ($album) {
            $this->getGalleryModel()->deleteAlbum($album, $user);
        }
        $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
    }

    public function albumImageAction()
    {
        if (!$this->getAuthenticationService()->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $alias = $this->params()->fromRoute('alias');
        $imageNumber = $this->params()->fromRoute('imageNumber');
        $user = $this->getAuthenticationService()->getIdentity();
        $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
        $images = $this->getGalleryModel()->getImageByAlbumAndNumber($album, $imageNumber);
        if (!$images) {
            return $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
        }

        if ($imageNumber == 0) {
            $previousUrl = null;
        } else {
            $previousUrl = $this->url()->fromRoute(
                'home/gallery/album/image',
                ['alias' => $alias, 'imageNumber' => $imageNumber - 1, 'userName' => $user->getUserName()]
            );
        }

        if ($imageNumber == $album->getImagesCount() - 1) {
            $nextUrl = null;
        } else {
            $nextUrl = $this->url()->fromRoute(
                'home/gallery/album/image',
                ['alias' => $alias, 'imageNumber' => $imageNumber + 1, 'userName' => $user->getUserName()]
            );
        }

        return [
            'image'       => $images[0],
            'nextUrl'     => $nextUrl,
            'previousUrl' => $previousUrl,
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

    protected function getUserModel()
    {
        if (!$this->userModel) {
            $this->userModel = $this->getServiceLocator()->get('Application\Model\UserModel');
        }

        return $this->userModel;
    }
}