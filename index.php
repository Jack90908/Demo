<?php
require_once 'Model.php';
require_once 'BeijingCarInWord.php';
$db = new Model('cm');
$tables = $db->query("SHOW TABLES Like 'beijing_car'");
if (!$db->fetch($tables)) {
  $db->query('CREATE TABLE `beijing_car` LIKE `fast_car`');
  new BeijingCarInWord();
}
?>
<style>
.button {
  background-color: #4CAF50; Green
  border: none;
  color: white;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
}
.summit {
  background-color: #ab4646; Green
  border: none;
  color: white;
  padding: 10px 15px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
}
form {
    margin:0px; display:inline
}
</style>
<HTML>
    <HEAD>
        <TITLE>選擇</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<input class="button" style="background-repeat:no-repeat; background-image:url('FastCar/fastCar.ico');" type="button" onclick="location.href='FastCar/index.php'" target="view_window" title="168" value ="168極速賽車">
<input class="button" style="background-repeat:no-repeat; background-image:url('FastCarWord/fastCar.ico');" type="button" onclick="location.href='FastCarWord/index.php'" target="view_window" title="世界" value ="世界極速賽車">
<input class="button" style="background-repeat:no-repeat; background-image:url('FastShipWord/fastCar.ico');" type="button" onclick="location.href='FastShipWord/index.php'" target="view_window" title="世界" value ="世界極速飛艇">
<input class="button" style="background-repeat:no-repeat; background-image:url('BeijingCarWord/fastCar.ico');" type="button" onclick="location.href='BeijingCarWord/index.php'" target="view_window" title="世界" value ="世界北京賽車">
