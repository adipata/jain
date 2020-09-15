<?php
declare(strict_types = 1);

require_once 'vendor/autoload.php';

spl_autoload_register(function ($class_name) {
    include str_replace('\\', '/', $class_name) . '.php';
});

function generateRandomString($length = 30) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int)ceil($length/strlen($x)) )),1,$length);
}

function customErrorHandler($errno, $errstr, $errfile, $errline) {
    echo "Error: [$errno] $errstr";
}

// Set user-defined error handler function
set_error_handler("customErrorHandler");