<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;


class IndexController extends AbstractActionController
{
    protected $authenticationService;

    public function indexAction()
    {
        /*ini_set('memory_limit', '-1');
        
        if (!is_dir('csv')) {
            mkdir('csv');
        }

        $fileName = 'csv/albums_1000000.txt';
        if (!file_exists($fileName)) {
            $ourFileHandle = fopen($fileName, 'w') or die("can't open file");
            fclose($ourFileHandle);
        }

        //$current = file_get_contents($fileName);
        $current = '';


        $dateNow = date("Y-m-d");

        for ($i = 1; $i < 1000001; $i++) {
            //albums
            $cityNr = rand(1, 3);
            if ($cityNr == 1) {
                $city = 'Kaunas';
            } else if ($cityNr == 2) {
                $city = 'Vilnius';
            } else {
                $city = 'Klaipeda';
            }
            $current .= $i . "," . $i . ",short deskr" . $i . ",long long long description " . $i . "," . $city . "," . $dateNow . "," . rand(50,150) . "," . $cityNr . "\n";


            //images
            /*$dateNow = date('Y-m-d', strtotime( '-'.mt_rand(0,720).' days'));
            $current .= rand(1,3) . "," . $i . "," . $i . ",short description" . rand(1, 100) . "," . rand(50,150) . "," . $dateNow . "," . rand(25,75) . "\n";

        }*/

        //file_put_contents($fileName, $current);

    }
}
