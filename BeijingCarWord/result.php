<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'beijing_car',
    'dataWeb' => '世界',
    'urlWeb' => 'http://52.193.14.86/html/PK10/pk10kai.html',
    'updateData' => '../BeijingCarInWord.php',
    'title' => '極速賽車'
];
require_once '../FastCarTemplate/'.$templateUrl;