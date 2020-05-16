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
<?php
require_once "../Model.php";
$db = new Model('cm');
$dbSchema = $db->query("SHOW TABLES LIKE 'fast_car_word'");
$dbName = $db->fetch($dbSchema);
if (!$dbName) {
    $db->query("CREATE TABLE `fast_car_word` LIKE `game`");
    $db->query("ALTER TABLE `fast_car_word` CHANGE `period` `period` BIGINT(40) NOT NULL;");
}

$getPeriod = $db->order('id', 'DESC')
                ->get('fast_car_word', ['id','creat_time', 'period'], 'LIMIT 1');
list($id, $uptime, $period) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
if (isset($_POST['changeurl'])) {
    $db->set('getUrl', ['status' => 'Y']);
    $db->where('url_id', $_POST['changeurl'])->set('getUrl', ['status' => 'N']);
    header('Location: index.php');
    exit;
}
$getUrlData = $db->where('status', 'Y')
                ->get('getUrl', ['setUrlName', 'domain', 'url_id']);
$urlData = $db->fetch($getUrlData);
$tableStyle = [
    '名次',
    '一',
    '二',
    '三',
    '四',
    '五',
    '六',
    '七',
    '八',
    '九',
    '十',
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
$act = (!isset($_GET['act'])) ? 'hand' : $_GET['act'];
$typeHead = [
    'hand' => [
        'title' => '手選-當期',
        'color' => '#fafad2',
        'type'  => '第'
    ],
    'goBall' => [
        'title' => '跟球-下期',
        'color' => 'antiquewhite',
        'type'  => '號球'
    ], 
    'move'=> [
        'title' => '偏移-下期',
        'color' => 'lavender',
        'type'  => '號球'
    ], 
];
?>
<HTML>
    <HEAD>
        <TITLE>選擇</TITLE>
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h1>世界--極速快車---請選擇號碼</h1>
官方網站：<a target="_blank" href="http://52.193.14.86/html/jisusaiche/pk10kai.html">世界開獎網</a><br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<span style="font-size:13px;">更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</span><br>
<input class="button" type="button" onclick="location.href='view.php'" target="view_window" title="瀏覽" value ="近期期數">
<input class="button" type="button" onclick="window.open('setting.php')" target="_blank" title="瀏覽" value ="設定最愛">

<br><br><br><br>
<form action="index.php" method="get" name="changeAct">
    <select id="act" name="act" onchange="selectChange()">
        <?php foreach ($typeHead as $tK => $titleValue) : 
        $checked = ($tK == $act) ? 'selected' : '';
        ?>
            <option <?=$checked?> value="<?=$tK?>"><?=$titleValue['title']?></option>
        <?php endforeach;?>
    </select>
</form>
<h><?=$typeHead[$act]['title']?></h>
<font size="1px"> <?php if ($act != 'hand') echo '(不帶任何數值為預設ex:1->1,4,7...)';?></font>
<br>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=<?=$typeHead[$act]['color']?>>
    <form action="result.php" method="get" name=formS target="_blank">
        <input type="hidden" name="act" value="<?=$act?>">
        <?php 
        foreach ($tableStyle as $tableKey => $tableValue) :
            $tdName = ($act == 'hand') ? $typeHead[$act]['type'] . $tableValue .'名' : $tableValue . $typeHead[$act]['type'];
            if ($tableValue == '名次') $tdName = '名次';
        ?>
            <tr>
            <td><font color=#000000><?=$tdName?></font></td>
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
                <input type="checkbox" id="chk<?=$tableKey?>-<?=$ballVaule?>" name="ball[<?=$tableKey?>][]" onclick="checkbox_clicked(this)" value="<?=$ballVaule?>"><font color=#000000><?=$ballVaule?>&nbsp;&nbsp;&nbsp;</font>
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
<input class="summit" type="button" id="clearCookie" value="清除點選記錄">
&nbsp;&nbsp;&nbsp;&nbsp;<button class="summit">手動更新期數</button>
&nbsp;&nbsp;&nbsp;&nbsp;
<!-- <form action="index.php" method="post" name="url">
    <input type="hidden" name="changeurl" value="<?=$urlData['url_id']?>">
    <input class="summit" type="submit" value="切換開獎網">
</form>
當前網站：<a target="_blank" href="<?=$urlData['domain']?>"><?=$urlData['setUrlName']?></a>
<footer>
    <a href="historic.php" style="font-size:5px;">更新日誌</a> -->
</footer>
</HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        function selectChange() {
            changeAct.submit();
        }
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
            var data = '<?=$id?>';
                    $.ajax({
                            url: '../FastCarInWord.php',
                            type: 'POST',
                            dataType: 'json',
                            async: true,
                            cache: false,
                            data:data,
                            success: function(data) {
                                window.location.reload();
                            },
                            error: function(data){
                                alert('更新失敗, 請稍候動作');
                            }
                        });
                });
    });

    $(document).ready(function () {
        $("input:checkbox").change(function () {
            var arrCheckedCheckboxes = [];

            $("input[id^=chk]").each(function () {
                var id = $(this).attr('id');

                if ($(this).is(':checked')) {
                    arrCheckedCheckboxes.push($(id).selector);

                    sessionStorage.setItem('checked-checkboxes', JSON.stringify(arrCheckedCheckboxes));
                    document.cookie = 'checked-checkboxes' + "=" + JSON.stringify(arrCheckedCheckboxes);
                }
            });
        });
        if (sessionStorage.getItem('checked-checkboxes') && $.parseJSON(sessionStorage.getItem('checked-checkboxes')).length !== 0) {
            var arrCheckedCheckboxes = $.parseJSON(sessionStorage.getItem('checked-checkboxes'));
            $("input[id^=chk]").each(function () {
                var id = $(this).attr('id');
                var chk = $(this).attr('id');
                if (arrCheckedCheckboxes.indexOf(chk) != '-1') {
                    document.getElementById(chk).checked=true
                }
            });
        }

        $("input[id^=clearCookie]").click(function () {
            sessionStorage.clear();
            window.location.reload();
        });
    });
</script>