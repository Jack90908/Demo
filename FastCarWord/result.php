<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'fast_car_word',
    'dataWeb' => '世界',
    'urlWeb' => 'http://52.193.14.86/html/jisusaiche/pk10kai.html',
    'updateData' => '../FastCarInWord.php',
    'title' => '極速賽車'
];
require_once '../FastCarTemplate/'.$templateUrl;