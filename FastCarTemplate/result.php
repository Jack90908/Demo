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
require_once "default.php";
require_once "../Service/FastCarService.php";
##預設值##
$perTitle = substr($period, 0, 6);
$perLast = substr($period, -3, 3);
#搜尋筆數
$total = 315;
#顯示
$totalView = 300;
$list = 20;
$change = 0;
$bite = 0;
$selectCount = 12;
$bitString = '';
#只選單顆球
#必要的參數
$titleData = array();
if (!isset($_GET['date'])) $_GET['date'] ='day';
$seach = ($_GET['date'] == 'yesterday') ? $date['Ymd'] -1 : $date['Ymd'];
$bingo = array();
$act = (!isset($_GET['act'])) ? 'hand' : $_GET['act'];
##########
$getData = $db
        ->order('id', 'DESC')
        ->get($gameType['gameDB'], '*', "LIMIT {$total}");
$data = $db->fetchAll($getData);
krsort($data);
$fast = new FastCarService($data);

$getData = $db->where("name", $_GET['name'])
    ->get('setting', ['data', 'red_letter']);
$resData = $db->fetch($getData);
$goBall = false;

if (in_array($_GET['name'], ['正常(1-5名)', '正常(6-10名)'])) {
    $resData = $fast->addHalf($resData, $_GET['name']);
    $goBall = $_GET['name'];
}
$bData = json_decode($resData['data'], true);
$getConfig = $db->get('ball_config');
$config = $db->fetch($getConfig);
#開頭
$typeHead[$_GET['act']]['title'] = $_GET['name'] . '-' . $typeHead[$_GET['act']]['title'];
#結果集結
if ($_GET['act'] == 'three') {
    $bData = ($_GET['threeBall'] == 'all') ? $ball : [$_GET['threeBall']];
    $goBall = ($_GET['goBall'] == 'true') ?: false;
}
if ($_GET['act'] == 'pan') {
    $bData[] = $_GET['panBall'];
}
$res = $fast->analysis($act, $bData, $goBall, $resData['red_letter']);
#選擇的球
$titleData = $fast->title($act, $bData);
#中幾球
$bingo = $res['bingo'];
#連續幾球藍字
$change = $res['change'];
#咬度
$bite = $res['bite'];
#是否只選單顆球
$oneBall = $fast->oneBall($bData, $act);
$maxPeriod = substr(end($data)['period'],-3 ,3);
for ($i = 0; $i < floor($totalView / $list); $i++) {
    #倒著寫回來，因為要抓取最大的
    $orderPeriod = $maxPeriod - ($list * $i) - $list +1;
    if ($orderPeriod < 0) $orderPeriod += 1000;
    $row[] = $orderPeriod;
}
krsort($row);
if (!$oneBall) {
    if ($res['change'] >= $config['point']) $bitString = "連續{$change}期藍字，共咬{$bite}期";
} else {
    if ($res['change'] >= $config['one_ball']) $bitString = "連續{$change}期藍字";
}
?>
<HTML>
    <HEAD>
        <TITLE><?=$typeHead[$act]['title']?></TITLE>
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h><?=$typeHead[$act]['title']?></h><br>
<table border=1 cellpadding=2 cellspacing=1 width=1250 bgcolor=<?=$typeHead[$act]['color']?>>
    <?php foreach ($titleData as $titleValue):?>
        <td style="width:250px">
            <?=$titleValue?>
        </td>
    <?php endforeach;?>
</table>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<span style="font-size:13px;">更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</span><br>

<input class="button" type="button" onclick="location.href='index.php'" target="_self" title="瀏覽" value ="返回首頁">
&nbsp;&nbsp;&nbsp;&nbsp;<button class="summit">手動更新期數</button>
<br>
<h>分析日期<?=$seach?>-近300期-期數前綴<?=$perTitle?></h>&nbsp;&nbsp;&nbsp;&nbsp;
<font><?=$bitString?></font>
<table border=1 cellpadding=3 cellspacing=2 width=1180 bgcolor=<?=$typeHead[$act]['color']?>>
    <?php for ($j = 0; $j < $list; $j++):?>
    <tr>
        <?php 
        foreach ($row as $rowV):
        $periodList = $rowV + $j;
        if ($periodList >= 1000) $periodList = $periodList - 1000;
        if ($periodList < 100) {
            $periodStr = 3 - strlen($periodList);
            if ($periodStr == 2) {
                $periodList = '00'.$periodList;
            } else {
                $periodList = '0'.$periodList;
            }
        }
        ?>
        <td width="340">
            <font color=darkviolet><?=$periodList?>：</font>
            <?php if (isset($bingo[$periodList])) :
            #一般的顏色區分
            $redLetter = ($resData['red_letter'] > 0) ? $resData['red_letter'] : 4;
            $color = ($bingo[$periodList] >= $redLetter) ? 'red' : 'blue';
            if (($bingo[$periodList] >= 3) && in_array($_GET['name'], ['正常(1-5名)', '正常(6-10名)'])) {
                $color = '#15481e';
            }
            #跟球走但只選一球
            if ($oneBall || $act == 'pan') {
                $color = ($bingo[$periodList] >= 1) ?'red' : 'blue';
            }
            ?>
            <font color=<?=$color?>><?= $bingo[$periodList]?></font>
            <?php else: ?>
            -
            <?php endif;?>
        </td>
        <?php endforeach;?>
    </tr>
    <?php endfor?>
</table>
<button class="summit">手動更新期數</button>
</HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        $(document).ready(function(){
        $('button').click(function(){
            var data = '<?=$id?>';
                    $.ajax({
                            url: '<?=$gameType['updateData']?>',
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
</script>