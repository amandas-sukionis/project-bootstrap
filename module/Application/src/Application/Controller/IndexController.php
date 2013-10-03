<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    protected $galleryModel;
    protected $authenticationService;

    public function indexAction()
    {
        $adapter = $this->getAuthenticationService()->getAdapter();
        $adapter->setIdentityValue('admin');
        $adapter->setCredentialValue('admin123');
        $authResult = $this->getAuthenticationService()->authenticate();

        if ($authResult->isValid()) {

        }


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

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
        }
        return $this->authenticationService;
    }
}
