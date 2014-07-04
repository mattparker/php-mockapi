<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:47
 */



require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/../MockServer/SilexApplicationHandler.php';
require_once __DIR__ . '/../MockServer/SilexApplicationHandlerSet.php';
require_once __DIR__ . '/../MockServer/SilexApplicationGenerator.php';
require_once __DIR__ . '/../MockServer/Parser.php';
require_once __DIR__ . '/../MockServer/RestServer.php';

/*set_include_path(__DIR__ . '/../' . PATH_SEPARATOR . get_include_path());

spl_autoload_register(function ($classname) {
    include_once  str_replace('\\', '/', $classname) . '.php';
}, false, false);
*/