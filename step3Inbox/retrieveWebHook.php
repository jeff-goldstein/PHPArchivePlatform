<?php

$config = require('config.php');

$processingDirectory = $config['processing']['Directory']; 

$verb = $_SERVER['REQUEST_METHOD']; 
if ($verb == "POST") 
{
    $body = file_get_contents("php://input");
    $file = $processingDirectory . '/' . uniqid() . '.json';
    if (!is_dir($processingDirectory)) 
    {
        // dir doesn't exist, make it
        mkdir($processingDirectory);
    }
    file_put_contents ($file, $body);
}

?>
