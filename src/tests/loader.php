<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 03/07/14
 * Time: 16:47
 */


spl_autoload_register(function ($classname) {
    include_once __DIR__ . '/../' . str_replace('\\', '/', $classname) . '.php';
});

require_once __DIR__ . '/../../vendor/autoload.php';
