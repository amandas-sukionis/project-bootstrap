<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;


class IndexController extends AbstractActionController
{
    protected $galleryModel;
    protected $userModel;

    public function indexAction()
    {
        $image = $this->getGalleryModel()->getMostLikedImage();

        return
            [
                'image' => $image,
            ];
    }

    public function searchAction()
    {
        $isLoggedIn = $this->getUserModel()->isLoggedIn();
        if ($isLoggedIn) {
            $request = $this->getRequest();
            if ($request->isPost()) {
                $postData = $request->getPost();
                $images = $this->getGalleryModel()->getImagesBySearchWords($postData);
            }
        }

        return
            [
                'images' => $images,
            ];
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
