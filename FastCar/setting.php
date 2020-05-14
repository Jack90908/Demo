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
.button_sel {
  background-color: #4CAF50; Green
  border: none;
  color: 00000;
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
#content{
    color:#ccc; 
}
.formNoChang{
    margin:0px; display:inline
}
</style>
<?php

require_once "../Model.php";
//中獎量 0123 藍 456789紅色
$db = new Model('cm');
$_POST['setAct'] = (isset($_POST['setAct'])) ? $_POST['setAct'] : '';
if($_POST['setAct'] == 'setting') {
    if ($_POST['content'] == '請輸入暱稱') {
        echo "
        <script>
        alert('請輸入暱稱');
        history.back()
        </script>";
        exit;
    }
    $ball = json_encode($_POST['ball']);
    $inserData = [
        'name' => $_POST['content'],
        'act' => $_POST['act'],
        'data' => $ball

    ];
    $insertSet = json_decode($db->add('setting', $inserData));
    if ($insertSet->error == '00000') {
        $msg = '新增成功';
    } elseif ($insertSet->error == '23000') {
        $msg = '暱稱重複，新增失敗';
    } else {
        $msg = '新增失敗，請系統人員協助'.$insertSet->error;
    }
    echo "<script>";
    if($insertSet->error != '00000') echo "alert('$msg');";
    echo "document.location.href='setting.php'
    </script>";
    exit;
}
if($_POST['setAct'] == 'del') {
    $db->where('name', $_POST['name'])
        ->delete('setting');
    echo "<script>
    document.location.href='setting.php'
    </script>";
    exit;
    
}
$getPeriod = $db->order('id', 'DESC')
                ->get('fast_car', ['id','creat_time', 'period'], 'LIMIT 1');
list($id, $uptime, $period) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);

$getData = $db->order('id', 'DESC')
                ->get('fast_car', '*', "LIMIT 13");
$data = $db->fetchAll($getData);
ksort($data);
$setGet = $db->order('act')
            ->order('name')
            ->get('setting', ['name', 'act', 'data']);
$settingData = $db->fetchAll($setGet);
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
#以下為每個最愛近300期每期 少於3筆中獎 12個以上標記顏色
foreach ($settingData as $setV) {
    $listChange[$setV['name']] = '';
    $change = 0;
    $setBall = json_decode($setV['data'], true);
    switch ($setV['act']) {
        case 'hand' :
            #設定球的中獎
            #13期的期數，以名次為群組
            foreach ($data as $dV) {
                $bingo[substr($dV['period'], -3, 3)] = 0;
                foreach ($ball as $num) {
                    if (isset($setBall[$num]) && in_array($dV["no{$num}"], $setBall[$num])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                if ($bingo[substr($dV['period'], -3, 3)] < 3) $change ++;
            }
        break;
        case 'goBall' :
            $beforBall = array();
            foreach ($data as $dK => $dV) {
                $frist = (!isset($frist)) ? $dK : $frist;
                $bingo[substr($dV['period'], -3, 3)] = 0;
                foreach ($ball as $num) {
                    if (!isset($beforBall["no{$num}"],$setBall[$beforBall["no{$num}"]])) continue;
                    if ($dK != $frist && in_array($dV["no{$num}"], $setBall[$beforBall["no{$num}"]])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                if ($bingo[substr($dV['period'], -3, 3)] < 3) $change ++;
                unset($beforBall);
                foreach($ball as $num) {
                    $beforBall["no{$num}"] = $dV["no{$num}"];
                }
            }
        break;
        case 'move' :
            $beforBall = array();
            foreach ($data as $dK => $dV) {
                $frist = (!isset($frist)) ? $dK : $frist;
                $bingo[substr($dV['period'], -3, 3)] = 0;
                foreach ($ball as $num) {
                    if (!isset($beforBall["no{$num}"],$setBall[$beforBall["no{$num}"]])) continue;
                    if ($dK != $frist && in_array($dV["no{$num}"], $setBall[$beforBall["no{$num}"]])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                if ($bingo[substr($dV['period'], -3, 3)] < 3) $change ++;
                unset($beforBall);
                foreach($ball as $num) {
                    $beforBall["no{$num}"] = $dV["no{$num}"];
                }
            }
        break;
    }
    #當連續12次都是低於3次的
    if ($change >= 12) $listChange[$setV['name']] ='change';
}
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
$tableStyle = [
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
?>
<HTML>
    <HEAD>
        <TITLE>設定最愛</TITLE>
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<input class="button" type="button" onclick="location.href='index.php'" target="_self" title="瀏覽" value ="返回首頁">
<input class="button" type="button" onclick="location.href='set.php'" target="_self" title="瀏覽" value ="檢視最愛">
<h3>設定最愛</h3>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=<?=$typeHead[$act]['color']?>>
    <form action="setting.php" method="post" name=formS>
        <tr>
            <td>最愛暱稱</td>
            <td>型態</td>
            <?php 
            foreach ($tableStyle as $tableKey => $tableValue) :
            $tdName = ($act == 'hand') ? $typeHead[$act]['type'] . $tableValue .'名' : $tableValue . $typeHead[$act]['type'];
            ?>
            <td><?=$tdName?></td>
            <?php endforeach;?>
        </tr>
        <tr>
            <td>
                <input type="text" id="content" name="content" value="請輸入暱稱"/>
            </td>
            <td>
                <select id="act" name="act" onchange="chageAct()">
                    <?php foreach ($typeHead as $tK => $titleValue) : 
                    $checked = ($tK == $act) ? 'selected' : '';
                    ?>
                    <option <?=$checked?> value="<?=$tK?>"><?=$titleValue['title']?></option>
                    <?php endforeach;?>
                </select>
            </td>
            <?php foreach($ball as $ballVaule) :?>
            <td>
                <?php for($i = 1; $i <= 3; $i++):?>
                <select name="ball[<?=$ballVaule?>][<?=$i?>]">
                    <?php foreach($ball as $seachV):?>
            　      <option value="<?=$seachV?>" checked><?=$seachV?></option>
                    <?php endforeach?>
                </select><br>
                <?php endfor;?>
            </td>
            <?php endforeach;?>
        </tr>
        <tr>
            <td>
                <input class="summit" type="submit" value="設定">
                <input type="hidden" name='setAct' value="setting">
            </td>
        </tr>
    </form>
</table>
<hr size="8px" color=#00000>
<h3>查詢結果</h3>

<?php 
$setAct = '';
foreach ($settingData as $setK => $setV) :
    if ($setV['act'] != $setAct) echo '<br>------' . $typeHead[$setV['act']]['title'] . '------<br>';
    $backGroud = $typeHead[$setV['act']]['color'];
    $remind = ($listChange[$setV['name']] == 'change') ? "background-image:url('new.gif');" : '';
    ?>
    <input type="button" style="width:200px;<?=$remind?>  background-color:<?=$backGroud?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('list<?=$setK?>').submit();" value="<?=$setV['name']?>" >
    <form class="formNoChang" action="result.php" id='list<?=$setK?>' method="get" target="_blank">
        <input type="hidden" name="name" value="<?=$setV['name']?>">
        <input type="hidden" name="act" value="<?=$setV['act']?>">
    </form>
<?php $setAct = $setV['act'];
endforeach;?>

<br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<h>更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</h>
<h3>刪除不要的設定值</h3>
<table border=1 cellpadding=2 cellspacing=1 width=300 bgcolor=#fafad2>
    <form action="setting.php" id='del' method="post">
        <tr>
            <td style="width:200px">
                <select style="width:200px" name="name">
                    <?php foreach ($settingData as $setK => $setV) :?>
                    　<option value="<?=$setV['name']?>"><?=$setV['name']?></option>
                    <?php endforeach;?>
                </select>
            </td>
            <td>            
                <input class="summit" type="submit" value="刪除"> 
                <input type="hidden" name='setAct' value="del">
            </td>
        </tr>
    </form>
</table>
<hr size="8px" color=#00000>
    </HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        $(document).ready(function(){
        $('button').click(function(){
            var data = '<?=$id?>';
                    $.ajax({
                            url: '../GetUrlData.php',
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
    $("#content").focus(function(){
        if(this.value == this.defaultValue) {
            this.value=''; $(this).css('color','#000'); 
            } });
    $("#content").blur(function(){
        if(this.value == '') {
            this.value=this.defaultValue;
            $(this).css('color','#ccc'); 
        } 
    });
    function chageAct() {
            var act = document.getElementById("act").value;
            var url = "setting.php?act=" + act;
            location.href=url;
    }
</script>

<!-- CREATE TABLE `roberDemo`.`setting` ( `name` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `act` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `data` TEXT NOT NULL , PRIMARY KEY (`name`)) ENGINE = InnoDB; -->