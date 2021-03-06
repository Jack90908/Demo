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
//中獎量 0123 藍 456789紅色
$db = new Model('cm');
$getPeriod = $db->order('id', 'DESC')
                ->get('game', ['id','creat_time'], 'LIMIT 1');
list($id, $uptime) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$date['Ymd'] = $date['year'].$date['month'].$date['day'];
$period = substr($id, -3, 3);
##預設值##
$row = [
    1,
    21,
    41,
    61,
    81,
    101,
    121,
    141,
    161,
];
$list = 20;
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
#必要的參數
$titleData = array();
if (!isset($_GET['date'])) $_GET['date'] ='day';
$seach = ($_GET['date'] == 'yesterday') ? $date['Ymd'] -1 : $date['Ymd'];
$bingo = array();
$act = (!isset($_GET['act'])) ? 'hand' : $_GET['act'];
$typeHead = [
    'hand' => [
        'title' => '手選-當期',
        'color' => '#fafad2'
    ],
    'goBall' => [
        'title' => '跟球-下期',
        'color' => 'antiquewhite'
    ], 
    'move'=> [
        'title' => '偏移-下期',
        'color' => 'lavender'
    ], 
];
##########

switch ($act) {
    case 'hand':
        #找期數
        $getData = $db->where("id LIKE '$seach%'")
                    ->get('game');
        $data = $db->fetchAll($getData);
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
        foreach ($data as $dV) {
            $bingo[$dV['period']] = 0;
            foreach ($ball as $num) {
                if (isset($bData[$num]) && in_array($dV["no{$num}"], $bData[$num])) {
                    $bingo[$dV['period']] ++;
                }
            }
        }
    break;
    case 'goBall':
        $between = $seach -1 . '180';
        $getData = $db->where("id between '$between' AND '{$seach}180'")
                ->get('game');
        $data = $db->fetchAll($getData);

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
            $bingo[$dV['period']] = 0;
            foreach ($ball as $num) {
                if (!isset($beforBall["no{$num}"],$dataGroup[$beforBall["no{$num}"]])) continue;
                if ($dK != 0 && in_array($dV["no{$num}"], $dataGroup[$beforBall["no{$num}"]])) {
                    $bingo[$dV['period']] ++;
                }
            }
            unset($beforBall);
            foreach($ball as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
    break;
    case 'move':
        $between = $seach -1 . '180';
        $getData = $db->where("id between '$between' AND '{$seach}180'")
                ->get('game');
        $data = $db->fetchAll($getData);

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
            $bingo[$dV['period']] = 0;
            foreach ($ball as $num) {
                $move = ($num == 10) ? 1 : $num + 1;
                if ($dK != 0 && in_array($dV["no{$move}"], $dataGroup[$beforBall["no{$num}"]])) {
                    $bingo[$dV['period']] ++;
                }
            }
            unset($beforBall);
            foreach($ball as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
    break;
    default:
    break;
}
?>
<HTML>
    <HEAD>
        <TITLE><?=$typeHead[$act]['title']?></TITLE>
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
<h>分析日期<?=$seach?></h>
<table border=1 cellpadding=3 cellspacing=2 width=1180 bgcolor=<?=$typeHead[$act]['color']?>>
    <?php for ($j = 0; $j < $list; $j++):?>
    <tr>
        <?php 
        foreach ($row as $rowV):
        $periodList = $rowV + $j;
        ?>
        <td width="340">
            期數<?=$periodList?>：
            <?php if (isset($bingo[$periodList])) :
            $color = ($bingo[$periodList] > 3) ? 'red' : 'blue';
            ?>
            <font color=<?=$color?>><?= $bingo[$periodList]?></font>
            <?php else: ?>
            未開
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
                            url: 'GetUrlData.php',
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