<?php 
require_once  'vendor/autoload.php';

function Boot($pClassName) {
    $path = __DIR__ . DIRECTORY_SEPARATOR . $pClassName . '.php';
    if(is_file($path)){
    	include_once $path;
    }
}
spl_autoload_register('Boot');

?>