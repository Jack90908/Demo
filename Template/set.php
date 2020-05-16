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
.titleName {
    width: 200px;
}
.titleData {
    width: 110px;
}
</style>
<?php

require_once "../Model.php";
//中獎量 0123 藍 456789紅色
$db = new Model('cm');
$_POST['setAct'] = (isset($_POST['setAct'])) ? $_POST['setAct'] : '';

if($_POST['setAct'] == 'update') {
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

$limit = (isset($_POST['limit'])) ? $_POST['limit'] :100;
$getData = $db->order('id', 'DESC')
            ->get('fast_car', '*', [0, $limit]);
$data = $db->fetchAll($getData);
$setGet = $db->order('act')
            ->get('setting', ['name', 'act', 'data']);
$settingData = $db->fetchAll($setGet);
foreach ($settingData as $setK => $setV) {
    $setV['data'] = json_decode($setV['data'], true);
    $groupData[$setV['act']][] = $setV;
}
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
        <TITLE>編輯最愛</TITLE>
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<input class="button" type="button" onclick="location.href='index.php'" target="_self" title="瀏覽" value ="返回首頁">
<input class="button" type="button" onclick="location.href='setting.php'" target="_self" title="瀏覽" value ="回最愛">
<h3>檢視最愛</h3>
<?foreach ($groupData as $gK => $gV) :?>
<table border=1 cellpadding=2 cellspacing=1 width=1320 bgcolor=<?=$typeHead[$gK]['color']?>>
    <tr>
        <td class="titleName"><?=$typeHead[$gK]['title']?></td>
        <?
        foreach ($tableStyle as $tableKey => $tableValue) :
        $tdName = ($gK == 'hand') ? $typeHead[$gK]['type'] . $tableValue .'名' : $tableValue . $typeHead[$gK]['type'];
        ?>
        <td><?=$tdName?></td>
        <?php endforeach;?>
    </tr>
    <?foreach ($gV as $data) :
    ?>
    <tr>
        <td class="titleName"><?=$data['name']?></td>
        <?foreach ($ball as $ballV) :
        $canter = implode(',', $data['data'][$ballV]);
        ?>
        <td class="titleData" onclick="test(this)"><?=$canter?></td>
        <?endforeach?>
    </tr>
    <?endforeach?>
</table>
<hr>
<?endforeach;?>
<hr size="8px" color=#00000>

<br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<h>更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</h>

</HTML>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
        $(document).ready(function(){
        $('button').click(function(){
            var data = '<?=$id?>';
                    $.ajax({
                            url: '../FastCarIn168.php',
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
    function test(test){
        console.log(test);
    }
</script>

<!-- CREATE TABLE `roberDemo`.`setting` ( `name` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `act` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `data` TEXT NOT NULL , PRIMARY KEY (`name`)) ENGINE = InnoDB; -->