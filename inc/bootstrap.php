<?php

function autoloader($class) {
    $file = __DIR__ . "/class/" . str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";
    if(file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoloader');