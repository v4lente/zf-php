<?php
//if($_SERVER['REMOTE_ADDR'] != "10.76.2.153" && $_SERVER['REMOTE_ADDR'] != "10.76.8.251") {
//    //echo $_SERVER['REMOTE_ADDR'] . "<br>";
//    header('Location: ./public/index_manutencao.php');
//    die(utf8_decode("Em Manutenção..."));
//}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'producao'));

// Define application development
defined('APPLICATION_DEV')
	|| define('APPLICATION_DEV', (getenv('APPLICATION_DEV') ? getenv('APPLICATION_DEV') : 'producao'));

// Define Jasper URL
defined('JASPER_URL')
	|| define('JASPER_URL', (getenv('JASPER_URL') ? getenv('JASPER_URL') : 'http://172.0.0.1:8080'));

// Define path to library directory
defined('LIBRARY_PATH')
	|| define('LIBRARY_PATH', realpath(APPLICATION_PATH . '/../library'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(LIBRARY_PATH, get_include_path())));

/** Zend_Application */
require_once 'Zend/Application.php';  

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV, 
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();