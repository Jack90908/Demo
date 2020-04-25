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
</style>
<?php
require_once "Model.php";
$db = new Model('cm');
$getPeriod = $db->order('id', 'DESC')
                ->get('game', ['id','creat_time'], 'LIMIT 1');
list($id, $uptime) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$period = substr($id, -3, 3);
$tableStyle = [
    '名次',
    '第一',
    '第二',
    '第三',
    '第四',
    '第五',
    '第六',
    '第七',
    '第八',
    '第九',
    '第十',
];
$ball = [
    1,
    2,
    3,
    4,
    5,
    6,
    7,
    8,
    9,
    10
];
?>
<HTML>
    <HEAD>
        <TITLE>選擇</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h1>請選擇號碼</h1>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<span style="font-size:13px;">更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</span><br>
<input class="button" type="button" onclick="window.open('view.php')" target="view_window" title="瀏覽" value ="近期期數">
<input class="button" type="button" onclick="window.open('result.php?type=goBall')" target="_blank" title="瀏覽" value ="跟球走">
<!-- <input class="button" type="button" onclick="window.open('result.php')" target="_blank" title="瀏覽" value ="偏移球"> -->
<br><br><br><br>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <form action="result.php" method="post" name=formS target="_blank">
        <?php 
        foreach ($tableStyle as $tableKey => $tableValue) :?>
            <tr bgcolor="#afeeee">
            <td><font color=#000000><?=$tableValue?></font></td>
            <?php if ($tableValue == '名次') :?>
            <td>
                查詢區間：
                <input type="radio" name="date" value="day" checked><font color=#000000>當日&nbsp;&nbsp;&nbsp;</font>
                <input type="radio" name="date" value="yesterday"><font color=#000000>昨日&nbsp;&nbsp;&nbsp;</font>
                <!-- <input type="radio" name="date" value="1month"><font color=#000000>一個月&nbsp;&nbsp;&nbsp;</font>
                <input type="radio" name="date" value="2month"><font color=#000000>兩個月&nbsp;&nbsp;&nbsp;</font> -->
            </td>
            <?php 
            continue;
            endif;
            ?>
            <td>
                <?php foreach ($ball as $ballVaule) :?>
                <input type="checkbox" name="ball[<?=$tableKey?>][]" onclick="checkbox_clicked(this)" value="<?=$ballVaule?>"><font color=#000000><?=$ballVaule?>&nbsp;&nbsp;&nbsp;</font>
                <?php endforeach?>
            </td>
            </tr>
        <?php endforeach;?>
        <tr>
            <td>
                <input class="summit" type="submit" value="統計">
            </td>
        </tr>
    </form>
</table>
<button>手動更新期數</button>
<br><br><br><br><br><br>
<footer>
    <a href="historic.php" style="font-size:5px;">更新日誌</a>
</footer>
</HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        var checked_num;
        checked_num = [];
        function checkbox_clicked(flag){  
                if (flag.checked == true){
                        if (checked_num[flag.name] >= 3){
                                flag.checked = false;
                                alert("最多只可選三個喔!!");
                        }else{
                                if(!checked_num[flag.name]) checked_num[flag.name] = 0;
                                checked_num[flag.name] += 1;
                        }
                }else{
                        checked_num[flag.name] -= 1;
                }
        }
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