<?php

require 'vendor/autoload.php';

$config = require('config.php');

$width = $config['reporting']['width'];
$height = $config['reporting']['height'];

$returnArray = array('width' => $width, 'height' => $height);
echo json_encode($returnArray);

?>