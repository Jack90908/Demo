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
$row = [
    1,
    61,
    121
];
$list = 60;
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
$seach = ($_POST['date'] == 'day') ? date('Ymd') : date("Ymd", strtotime('-1 day'));
$getData = $db->where("id LIKE '$seach%'")
            ->get('game');
$data = $db->fetchAll($getData);
$bingo = array();
foreach ($data as $dV) {
    $bingo[$dV['period']] = 0;
    foreach ($ball as $num) {
        if (isset($_POST['ball'][$num]) && in_array($dV["no{$num}"], $_POST['ball'][$num])) {
            $bingo[$dV['period']] ++;
        }
    }
}
?>
<HTML>
    <HEAD>
        <TITLE>分析</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h>已選號碼</h><br>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <?php foreach ($ball as $ballList):?>
        <td> 第<?=$ballList?>名：
            <?php 
            if (isset($_POST['ball'][$ballList])):
                $str = '';
            foreach ($_POST['ball'][$ballList] as $num):
                $str .= $num.',';
            endforeach;
            echo substr($str,0,-1);
            endif;
            ?>
        </td>
    <?php endforeach;?>
</table>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<a href="index.php">返回搜尋頁</a>
<br><br><br><br>
<h>更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</h>
<br>
<h>分析日期<?=$seach?></h>
<table border=1 cellpadding=3 cellspacing=2 width=1020 bgcolor=#fafad2>
    <?php for ($j = 0; $j < $list; $j++):?>
    <tr>
        <?php 
        foreach ($row as $rowV):
        $periodList = $rowV + $j;
        ?>
        <td width="340">
            期數：<?=$periodList?>
            <?php if (isset($bingo[$periodList])) :
            $color = ($bingo[$periodList] > 3) ? 'red' : 'blue';
            ?>
             中獎量：<font color=<?=$color?>><?= $bingo[$periodList]?></font>
            <?php else: ?>
            尚未開獎
            <?php endif;?>
        </td>
        <?php endforeach;?>
    </tr>
    <?php endfor?>
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