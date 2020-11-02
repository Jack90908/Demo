<?php
$templateArray = explode("/", $_SERVER['PHP_SELF']);
$templateUrl = end($templateArray);
$gameType = [
    'gameDB' => 'lucky_ferry168',
    'dataWeb' => '168',
    'urlWeb' => 'https://1680380.com/view/xingyft/pk10kai_history.html',
    'updateData' => '../LuckyFerryIn168.php',
    'title' => '幸運飛艇'
];
require_once '../FastCarTemplate/'.$templateUrl;