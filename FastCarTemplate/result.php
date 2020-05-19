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

##預設值##
$perTitle = substr($period, 0, 6);
$perLast = substr($period, -3, 3);
$total = 300;
$list = 20;
$change = 0;
$bite = 0;
$selectCount = 12;
$bitString = '';
#必要的參數
$titleData = array();
if (!isset($_GET['date'])) $_GET['date'] ='day';
$seach = ($_GET['date'] == 'yesterday') ? $date['Ymd'] -1 : $date['Ymd'];
$bingo = array();
$act = (!isset($_GET['act'])) ? 'hand' : $_GET['act'];
##########

switch ($act) {
    case 'hand':
        #找期數
        $getData = $db
                    ->order('id', 'DESC')
                    ->get($gameType['gameDB'], '*', "LIMIT {$total}");
        $data = $db->fetchAll($getData);
        krsort($data);
        #找是否有偏好設定
        if (isset($_GET['name'])) {
            $typeHead[$_GET['act']]['title'] = $_GET['name'] . '-' . $typeHead[$_GET['act']]['title'];
            $getData = $db->where("name", $_GET['name'])
                    ->get('setting', 'data');
            $resData = $db->fetch($getData);
            $bData = json_decode($resData['data'], true);
        } else {
            $bData = $_GET['ball'];
        }

        #塞入開頭資訊
        foreach ($ball as $ballList) {
            $str = '';
            if (isset($bData[$ballList])) {
                foreach ($bData[$ballList] as $num) {
                    $str .= $num.',';
                }
                $str = substr($str,0,-1);
            }
            $titleData[] = "第{$ballList}名：" . $str;
        }
        #塞入結果
        foreach ($data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($ball as $num) {
                if (isset($bData[$num]) && in_array($dV["no{$num}"], $bData[$num])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
            if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
            if ($change == 0) $bite = 0;
        }
    break;
    case 'goBall':
        #只選單顆球
        $oneBall = false;
        $m = 0;
        $total ++;
        $getData = $db
                    ->order('id', 'DESC')
                    ->get($gameType['gameDB'], '*', "LIMIT {$total}");
        $data = $db->fetchAll($getData);
        krsort($data);

        #塞入開頭資訊
        if (isset($_GET['name'])) {
            $typeHead[$_GET['act']]['title'] = $_GET['name'] . '-' . $typeHead[$_GET['act']]['title'];
            $getData = $db->where("name", $_GET['name'])
                    ->get('setting', 'data');
            $resData = $db->fetch($getData);
            $bData = json_decode($resData['data'], true);
        }
        foreach ($bData as $one) {
            if (!empty($one[1]) && !empty($one[2]) && !empty($one[3]))$m ++;
        }
        #只選單顆球的話
        if ($m == 1) $oneBall = true;
        if (isset($_GET['ball']) || isset($_GET['name'])) {

            $titleData = array();
            $setBall = (isset($_GET['ball'])) ? $_GET['ball'] : $bData;
            foreach ($setBall as $ballK => $ballCanter) {
                $titleData[$ballK] = $ballK."號球：";
                foreach ($ballCanter as $canter) {
                    $titleData[$ballK] .= $canter . ',';
                }
                $titleData[$ballK] = substr($titleData[$ballK],0,-1);
            }
        } else {
            $titleData = [
                '1,4,7號： 下期中獎1,4,7',
                '2,5,8號： 下期中獎2,5,8',
                '3,6,9號： 下期中獎3,6,9',
                '10號： 下期中獎1,5,10',
            ];
        }
        #塞入結果
        if (isset($setBall)) {
            $dataGroup = array();
            foreach ($setBall as $ballK => $ballCanter) {
                foreach ($ballCanter as $canter) {
                    $dataGroup[$ballK][] = $canter;
                }
            }
        } else {
            $dataGroup = [
                '1' => [1, 4, 7],
                '2' => [2, 5, 8],
                '3' => [3, 6, 9],
                '4' => [1, 4, 7],
                '5' => [2, 5, 8],
                '6' => [3, 6, 9],
                '7' => [1, 4, 7],
                '8' => [2, 5, 8],
                '9' => [3, 6, 9],
                '10' => [1, 5, 10],
            ];
        }
        $beforBall = array();
        foreach ($data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($ball as $num) {
                if (!isset($beforBall["no{$num}"],$dataGroup[$beforBall["no{$num}"]])) continue;
                if ($dK != $frist && in_array($dV["no{$num}"], $dataGroup[$beforBall["no{$num}"]])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            unset($beforBall);
            foreach($ball as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
            $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
            if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
            if ($change == 0) $bite = 0;
        }
    break;
    case 'move':
        $total ++;
        $getData = $db
                    ->order('id', 'DESC')
                    ->get($gameType['gameDB'], '*', "LIMIT {$total}");
        $data = $db->fetchAll($getData);
        krsort($data);

        #塞入開頭資訊
        if (isset($_GET['name'])) {
            $typeHead[$_GET['act']]['title'] = $_GET['name'] . '-' . $typeHead[$_GET['act']]['title'];
            $getData = $db->where("name", $_GET['name'])
                    ->get('setting', 'data');
            $resData = $db->fetch($getData);
            $bData = json_decode($resData['data'], true);
        }
        if (isset($_GET['ball']) || isset($_GET['name'])) {

            $titleData = array();
            $setBall = (isset($_GET['ball'])) ? $_GET['ball'] : $bData;
            foreach ($setBall as $ballK => $ballCanter) {
                $titleData[$ballK] = $ballK."號：";
                foreach ($ballCanter as $canter) {
                    $titleData[$ballK] .= $canter . ',';
                }
                $titleData[$ballK] = substr($titleData[$ballK],0,-1);
            }
        } else {
            $titleData = [
                '1,4,7號： 下期右側中獎1,4,7',
                '2,5,8號： 下期右側中獎2,5,8',
                '3,6,9號： 下期右側中獎3,6,9',
                '10號： 下期右側中獎1,5,10',
            ];
        }

        #塞入結果
        if (isset($setBall)) {
            $dataGroup = array();
            foreach ($setBall as $ballK => $ballCanter) {
                foreach ($ballCanter as $canter) {
                    $dataGroup[$ballK][] = $canter;
                }
            }
        } else {
            $dataGroup = [
                '1' => [1, 4, 7],
                '2' => [2, 5, 8],
                '3' => [3, 6, 9],
                '4' => [1, 4, 7],
                '5' => [2, 5, 8],
                '6' => [3, 6, 9],
                '7' => [1, 4, 7],
                '8' => [2, 5, 8],
                '9' => [3, 6, 9],
                '10' => [1, 5, 10],
            ];
        }
        $beforBall = array();
        foreach ($data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($ball as $num) {
                $move = ($num == 10) ? 1 : $num + 1;
                if ($dK != $frist && in_array($dV["no{$move}"], $dataGroup[$beforBall["no{$num}"]])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            unset($beforBall);
            foreach($ball as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
            $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
            if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
            if ($change == 0) $bite = 0;
        }
    break;
    default:
    break;
}
for ($i = 0; $i < floor($total / $list); $i++) {
    #倒著寫回來，因為要抓取最大的
    $orderPeriod = substr(end($data)['period'],-3 ,3) - ($list * $i) - $list +1;
    if ($orderPeriod < 0) $orderPeriod += 1000;
    $row[] = $orderPeriod;
}
krsort($row);
if ($change >= $selectCount) {
    $bitString = "連續{$change}期藍字，共咬{$bite}期";
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
            $color = ($bingo[$periodList] > 3) ? 'red' : 'blue';
            if ($act == 'goBall' && $oneBall) $color = 'red';
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