<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'fast_car_word',
    'dataWeb' => '世界',
    'urlWeb' => 'http://52.193.14.86/html/jisusaiche/pk10kai.html',
    'updateData' => '../FastCarIn168.php'
];
require_once '../FastCarTemplate/'.$templateUrl;
?>