<?php

namespace Application\Controller;

use Application\Logger\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;


class IndexController extends AbstractActionController
{
    protected $authenticationService;

    public function indexAction()
    {
        $optionsDatabase = [
            'host' => 'nfqakademija.dev',
            'port' => '3306',
            'user' => 'remoteuser',
            'password' => 'userpassword',
            'dbname' => 'nfq_akademija',
        ];

        $optionsFile = [
            'name' => 'logs.txt',
        ];
        $logger = new Logger();
        if ($logger->addWriter('database', $optionsDatabase)) {
            $logger->log('blogai');
            $logger->log('BUG', 'bugas');
        }

        unset($logger);

        $logger = new Logger();
        if ($logger->addWriter('file', $optionsFile)) {
            $logger->log('blogai');
            $logger->log('BUG', 'bugas');
        }
    }

}
