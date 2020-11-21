<?php
require_once 'Model.php';
require_once 'LuckAusIn168.php';
$db = new Model('cm');
$tables = $db->query("SHOW TABLES Like 'lucky_aus168'");
if (!$db->fetch($tables)) {
  $db->query('CREATE TABLE `lucky_aus168` LIKE `lucky_ferry168`');
  new LuckAusIn168('https://api.apiose122.com/pks/getPksHistoryList.do?date=2020-11-20&lotCode=10012');
  new LuckAusIn168('https://api.apiose122.com/pks/getPksHistoryList.do?date=2020-11-21&lotCode=10012');
  new LuckAusIn168();
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
<input class="button" style="background-repeat:no-repeat; background-image:url('LuckyFerryIn168/fastCar.ico');" type="button" onclick="location.href='LuckyFerryIn168/index.php'" target="view_window" title="168" value ="168幸運飛艇">
<input class="button" style="background-repeat:no-repeat; background-image:url('LuckyAusIn168/fastCar.ico');" type="button" onclick="location.href='LuckyAusIn168/index.php'" target="view_window" title="168" value ="168澳洲幸運10">
<input class="button" style="background-repeat:no-repeat; background-image:url('FastCarWord/fastCar.ico');" type="button" onclick="location.href='FastCarWord/index.php'" target="view_window" title="世界" value ="世界極速賽車">
<input class="button" style="background-repeat:no-repeat; background-image:url('FastShipWord/fastCar.ico');" type="button" onclick="location.href='FastShipWord/index.php'" target="view_window" title="世界" value ="世界極速飛艇">
<input class="button" style="background-repeat:no-repeat; background-image:url('BeijingCarWord/fastCar.ico');" type="button" onclick="location.href='BeijingCarWord/index.php'" target="view_window" title="世界" disabled value ="世界北京賽車-暫停">
