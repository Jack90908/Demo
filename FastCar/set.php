<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'fast_car',
    'dataWeb' => '168',
    'urlWeb' => 'https://1680380.com/view/jisusaiche/pk10kai.html',
    'updateData' => '../FastCarIn168.php',
    'title' => '極速賽車'
];
require_once '../FastCarTemplate/'.$templateUrl;