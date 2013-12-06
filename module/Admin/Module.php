<?php
namespace Admin;

use Zend\Mvc\MvcEvent;

class Module
{

    protected $serviceManager;
    protected $authenticationService;

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        $app = $e->getApplication();
        $em = $app->getEventManager();
        $this->serviceManager = $app->getServiceManager();
        $this->getAuthenticationService();
        $em->attach(MvcEvent::EVENT_DISPATCH, array($this, 'selectLayoutBasedOnRoute'));
    }

    /**
     * @param MvcEvent $e
     * select between admin and simple layout
     */
    public function selectLayoutBasedOnRoute(MvcEvent $e)
    {
        $match = $e->getRouteMatch();
        $controller = $e->getTarget();
        if ($match->getMatchedRouteName() == 'admin') {
            $controller->layout('layout/admin-login');
        } else {
            if (strpos($match->getParam('controller'), 'Admin\Controller') !== false) {
                if (!$this->getAuthenticationService()->getIdentity()) {
                    $response = $this->serviceManager->get('response');
                    $response->getHeaders()->addHeaderLine('Location', '/admin');
                    $response->sendHeaders();
                    exit;
                } else if ($this->getAuthenticationService()->getIdentity()->getAccessLevel() < 10) {
                    $response = $this->serviceManager->get('response');
                    $response->getHeaders()->addHeaderLine('Location', '/');
                    $response->sendHeaders();
                    exit;
                }
                $controller->layout('layout/admin');
            }
        }
        $controller->layout()->action = $controller->params('action');
    }

    protected function getAuthenticationService()
    {
        if (!$this->authenticationService) {
            $this->authenticationService = $this->serviceManager->get('Zend\Authentication\AuthenticationService');
        }

        return $this->authenticationService;
    }

}
