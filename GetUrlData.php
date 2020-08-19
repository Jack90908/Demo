<?php
ini_set('display_errors', 'off');
require_once "Model.php";
require_once "FastCarIn168.php";
require_once "FastCarInWord.php";
require_once "FastShipInWord.php";
// 極速賽車
new FastCarIn168();
new FastCarInWord();
new FastShipInWord();
