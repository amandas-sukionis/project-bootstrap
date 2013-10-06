<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $galleryModel;
    protected $authenticationService;

    public function indexAction()
    {
        /*$adapter = $this->getAuthenticationService()->getAdapter();
        $adapter->setIdentityValue('admin');
        $adapter->setCredentialValue('admin123');
        $authResult = $this->getAuthenticationService()->authenticate();

        if ($authResult->isValid()) {

        }*/


        $uploadImageForm = $this->getServiceLocator()->get('Application\Form\UploadImageForm');
        //die(var_dump($uploadImageForm->getMyInputFilter()));

        $viewModel = new ViewModel();
        $request    = $this->getRequest();
        if ($request->isPost()) {
            $postData = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $uploadImageForm->setData($postData);
            if ($uploadImageForm->isValid()) {
                $imageUrl = $this->getGalleryModel()->uploadImageFile($postData);
                return $this->redirect()->toUrl($imageUrl);
            }
            else {
                $uploadImageForm->setData($request->getPost()->toArray());
                $viewModel->setVariable('error', 'file was not uploaded, available image extensions: jpg, png');
            }
        }
        $viewModel->setVariable('uploadImageForm', $uploadImageForm);

        return $viewModel;
    }

    protected function getGalleryModel()
    {
        if (!$this->galleryModel) {
            $this->galleryModel = $this->getServiceLocator()->get('Application\Model\GalleryModel');
        }
        return $this->galleryModel;
    }

}
