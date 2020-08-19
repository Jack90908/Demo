<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'fast_ship_word',
    'dataWeb' => '世界',
    'urlWeb' => 'http://52.193.14.86/html/jisusaiche2/pk10kai.html',
    'updateData' => '../FastShipInWord.php',
    'title' => '極速飛艇',
];
require_once '../FastCarTemplate/'.$templateUrl;