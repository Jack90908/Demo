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
</style>
<?php
require_once "Model.php";
//中獎量 0123 藍 456789紅色
$db = new Model('cm');
$getPeriod = $db->order('id', 'DESC')
                ->get('game', ['id','creat_time'], 'LIMIT 1');
list($id, $uptime) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$period = substr($id, -3, 3);
$limit = (isset($_POST['limit'])) ? $_POST['limit'] :100;
$getData = $db->order('id', 'DESC')
            ->get('game', '*', [0, $limit]);
$data = $db->fetchAll($getData);

$ball = [
    '1' => 1,
    '2' => 2,
    '3' => 3,
    '4' => 4,
    '5' => 5,
    '6' => 6,
    '7' => 7,
    '8' => 8,
    '9' => 9,
    '10' => 10
];
?>
<HTML>
    <HEAD>
        <TITLE>資訊</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<input class="button" type="button" onclick="location.href='index.php'" target="_self" title="瀏覽" value ="返回首頁">
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <tr>
    <td>日期</td>
    <td>期數</td>
    <td>第一名</td>
    <td>第二名</td>
    <td>第三名</td>
    <td>第四名</td>
    <td>第五名</td>
    <td>第六名</td>
    <td>第七名</td>
    <td>第八名</td>
    <td>第九名</td>
    <td>第十名</td>
    </tr>
    <?php foreach ($data as $value) :?>
    <tr>
        <td><?=substr($value['date'], -4, 4);?></td>
        <td bgcolor="#a5f99a"><?=$value['period']?></td>
            <?php foreach ($ball as $num) :?>
        <td><?=$value["no{$num}"];?></td>
            <?php endforeach?>
    <?php endforeach?>
    </tr>
</table>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<input class="button" type="button" onclick="window.open('index.php')" target="_self" title="瀏覽" value ="返回首頁">
<br><br><br><br>
<h>更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</h>
<br>
<table border=1 cellpadding=3 cellspacing=2 width=1020 bgcolor=#fafad2>

</table>
<button>手動更新期數</button>
</HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        $(document).ready(function(){
        $('button').click(function(){
                    $.ajax({
                            url: 'GetUrlData.php',
                            type: 'POST',
                            dataType: 'json',
                            async: true,
                            cache: false,
                            success: function(data) {
                                alert('更新成功');
                                window.location.reload();
                            },
                            error: function(data){
                                alert('更新失敗, 請稍候動作');
                            }
                        });
                });
    });
</script>