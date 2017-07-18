<?php
date_default_timezone_set("Asia/Shanghai");
//echo phpinfo();exit;
error_reporting(E_ALL);
ini_set('display_errors',1);

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the Oa root now.
 */
chdir(dirname(__DIR__));//  修改当前运行目录

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}
//defined('LIBS_EXTERNAL_FOLDER') || define('LIBS_EXTERNAL_FOLDER', getenv('LIBS_EXTERNAL_FOLDER'));
define('LIBS_EXTERNAL_FOLDER','/var/www/Zend2') ;
define('APP', realpath(dirname(__DIR__)));
define('EXCLUDE','010002,010003,010681,010007');
define('NIGHT','03:00:00');
define('DOMAIN','http://192.168.100.20:8888');
ini_set('session.save_path',APP.'/data/session/');
// Setup autoloading
require 'init_autoloader.php';

// Run the Oa!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
