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
            'isOwner'       => $isOwner,
            'galleryAlbums' => $galleryAlbums,
        ];
    }

    public function albumAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);

        if ($user) {
            $alias = $this->params()->fromRoute('alias');
            $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $albumImages = $this->getGalleryModel()->getAllImagesByAlbum($album);
            } else {
                if ($album->getIsPublic()) {
                    $albumImages = $this->getGalleryModel()->getAllPublicImagesByAlbum($album);
                } else {
                    return $this->redirect()->toRoute('home');
                }
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'albumImages' => $albumImages,
            'album'       => $album,
            'userName'    => $user->getUserName(),
            'isOwner'     => $isOwner,
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

    public function upVoteImageAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        $authenticatedUser = $this->getUserModel()->getUser();
        if ($authenticatedUser) {
            if ($user) {
                $alias = $this->params()->fromRoute('alias');
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
                if ($album && $album->getIsPublic()) {
                    $imageAlias = $this->params()->fromRoute('imageAlias');
                    $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
                    if ($image) {
                        $image = $image[0];
                        if ($image->getIsPublic()) {
                            $galleryImageVoteLog = $this->getGalleryModel()->getImageVoteLogByUserAndImage($authenticatedUser, $image);
                            if (!$galleryImageVoteLog) {
                                $this->getGalleryModel()->upVoteImage($authenticatedUser, $image, null, null);
                                return new JsonModel(['status' => 'ok', 'voteCount' => $image->getVotesCount()]);
                            } else {
                                if ($galleryImageVoteLog->getType() == 'upvote') {
                                    $this->getGalleryModel()->upVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasUp');
                                } else if ($galleryImageVoteLog->getType() == 'downvote') {
                                    $this->getGalleryModel()->upVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasDown');
                                } else if ($galleryImageVoteLog->getType() == 'neutral') {
                                    $this->getGalleryModel()->upVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasNeutral');
                                }
                                return new JsonModel(['status' => 'ok', 'voteCount' => $image->getVotesCount()]);
                            }
                        }
                    }
                }
            }
        } else {
            return new JsonModel(['status' => 'login']);
        }

        return new JsonModel(['status' => 'fail']);
    }

    public function downVoteImageAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        $authenticatedUser = $this->getUserModel()->getUser();
        if ($authenticatedUser) {
            if ($user) {
                $alias = $this->params()->fromRoute('alias');
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
                if ($album && $album->getIsPublic()) {
                    $imageAlias = $this->params()->fromRoute('imageAlias');
                    $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
                    if ($image) {
                        $image = $image[0];
                        if ($image->getIsPublic()) {
                            $galleryImageVoteLog = $this->getGalleryModel()->getImageVoteLogByUserAndImage($authenticatedUser, $image);
                            if (!$galleryImageVoteLog) {
                                $this->getGalleryModel()->downVoteImage($authenticatedUser, $image, null, null);
                                return new JsonModel(['status' => 'ok', 'voteCount' => $image->getVotesCount()]);
                            } else {
                                if ($galleryImageVoteLog->getType() == 'upvote') {
                                    $this->getGalleryModel()->downVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasUp');
                                } else if ($galleryImageVoteLog->getType() == 'downvote') {
                                    $this->getGalleryModel()->downVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasDown');
                                } else if ($galleryImageVoteLog->getType() == 'neutral') {
                                    $this->getGalleryModel()->downVoteImage($authenticatedUser, $image, $galleryImageVoteLog, 'wasNeutral');
                                }
                                return new JsonModel(['status' => 'ok', 'voteCount' => $image->getVotesCount()]);
                            }
                        }
                    }
                }
            }
        } else {
            return new JsonModel(['status' => 'login']);
        }

        return new JsonModel(['status' => 'fail']);
    }

    public function uploadImagesAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
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
                        $images = $this->getGalleryModel()->moveImageFiles($postData, $alias, $user);

                        return new JsonModel(['images' => $images]);
                    }
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'uploadImageForm' => $uploadImageForm,
            'userName'        => $user->getUserName(),
        ];
    }

    public function finishImageUploadAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $imageForm = $this->getServiceLocator()->get('Application\Form\ImageForm');
                $alias = $this->params()->fromRoute('alias');

                $request = $this->getRequest();
                if ($request->isPost()) {
                    $postData = $request->getPost();
                    $imageForm->setData($postData);
                    if ($imageForm->isValid()) {
                        $image = $this->getGalleryModel()->getImageByAlias($alias);
                        if ($image) {
                            if ($image->getAlbum()->getUser() === $user) {
                                if ($this->getGalleryModel()->saveImageInfo($postData, $alias)) {
                                    return new JsonModel(['status' => 'saved']);
                                }
                            }
                        }
                    }
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return new JsonModel(['status' => 'fail']);
    }

    public function editAlbumAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $alias = $this->params()->fromRoute('alias');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
                $albumForm = $this->getServiceLocator()->get('Application\Form\AlbumForm');
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

                            return $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
                        }
                    }
                } else {
                    return $this->redirect()->toRoute('home/gallery/addAlbum', ['userName' => $user->getUserName()]);
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'albumForm' => $albumForm,
            'action'    => 'edit_gallery_album',
        ];
    }

    public function editImageAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $albumAlias = $this->params()->fromRoute('alias');
        $imageAlias = $this->params()->fromRoute('imageAlias');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($albumAlias, $user);
                if ($album) {
                    $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
                    if ($image) {
                        $image = $image[0];
                        $imageForm = $this->getServiceLocator()->get('Application\Form\ImageForm');
                        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                        $imageForm->setHydrator(new DoctrineObject($entityManager));
                        $imageForm->bind($image);

                        $request = $this->getRequest();
                        if ($request->isPost()) {
                            $postData = $request->getPost();
                            $imageForm->setData($postData);
                            if ($imageForm->isValid()) {
                                $this->getGalleryModel()->checkAlbumImage($postData, $album, $image);
                                $this->getGalleryModel()->checkImageTags($postData, $album, $image);
                                $entityManager->flush();

                                return $this->redirect()->toRoute(
                                    'home/gallery', ['userName' => $user->getUserName(), 'alias' => $albumAlias]
                                );
                            }
                        }
                    } else {
                        return $this->redirect()->toRoute('home');
                    }
                } else {
                    return $this->redirect()->toRoute('home/gallery/addAlbum', ['userName' => $user->getUserName()]);
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'imageForm' => $imageForm,
            'image'     => $image,
        ];
    }

    public function deleteAlbumAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $alias = $this->params()->fromRoute('alias');
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
                if ($album) {
                    $this->getGalleryModel()->deleteAlbum($album, $user);
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return $this->redirect()->toRoute('home/gallery', ['userName' => $user->getUserName()]);
    }

    public function deleteImageAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        if ($user) {
            $isOwner = $this->getUserModel()->isUserOwner($user);
            if ($isOwner) {
                $alias = $this->params()->fromRoute('alias');
                $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
                if ($album) {
                    $imageAlias = $this->params()->fromRoute('imageAlias');
                    $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
                    if ($image) {
                        $image = $image[0];
                        $this->getGalleryModel()->deleteImage($image, $album);
                    } else {
                        return $this->redirect()->toRoute(
                            'home/gallery/album', ['userName' => $user->getUserName(), 'alias' => $album->getAlias()]
                        );
                    }
                } else {
                    return $this->redirect()->toRoute('home');
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return $this->redirect()->toRoute(
            'home/gallery/album', ['userName' => $user->getUserName(), 'alias' => $album->getAlias()]
        );
    }

    public function albumImageAction()
    {
        $userName = $this->params()->fromRoute('userName');
        $user = $this->getUserModel()->findUserByUserName($userName);
        $alias = $this->params()->fromRoute('alias');
        $imageAlias = $this->params()->fromRoute('imageAlias');
        if ($user) {
            $album = $this->getGalleryModel()->getAlbumByAliasAndUser($alias, $user);
            if ($album) {
                $image = $this->getGalleryModel()->getImageByAlbumAndAlias($album, $imageAlias);
                if ($image) {
                    $isOwner = $this->getUserModel()->isUserOwner($user);
                    $isLoggedIn = $this->getUserModel()->isLoggedIn();
                    if (!$isOwner && !$album->getIsPublic() && !$image->getIsPublic()) {
                        return $this->redirect()->toRoute('home');
                    }
                } else {
                    return $this->redirect()->toRoute('home');
                }
            } else {
                return $this->redirect()->toRoute('home');
            }
        } else {
            return $this->redirect()->toRoute('home');
        }

        return [
            'image'      => $image[0],
            'isLoggedIn' => $isLoggedIn,
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