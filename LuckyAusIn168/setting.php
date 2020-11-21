<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'lucky_aus168',
    'dataWeb' => '168',
    'urlWeb' => 'https://www.1680380.com/view/aozxy10/pk10kai.html',
    'updateData' => '../LuckAusIn168.php',
    'title' => '澳洲幸運10'
];
require_once '../FastCarTemplate/'.$templateUrl;