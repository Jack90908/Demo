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
#content{
    color:#ccc; 
}
.formNoChang{
    margin:0px; display:inline
}
</style>
<?php
require_once "default.php";
require_once "../Service/FastCarService.php";
$getConfig = $db->get('ball_config');
$configs = $db->fetchAll($getConfig);
$configArr = [];
foreach ($configs as $config) {
    $configArr[$config['act']] = $config;
}
#總共搜尋幾球
$getCount = 350;
$getData = $db->order('id', 'DESC')
                ->get($gameType['gameDB'], '*', "LIMIT {$getCount}");
$data = $db->fetchAll($getData);
krsort($data);

$setGet = $db->where('act', 'move', 'AND', '!=')
            ->order('act')
            ->order('name')
            ->get('setting', ['name', 'act', 'data', 'red_letter']);
$settingData = $db->fetchAll($setGet);
$fast = new FastCarService($data);
#加入跟球一半的數據
$fast->addHalf($settingData);
#以下為每個最愛近300期每期 少於3筆中獎 12個以上標記顏色
$topRes = [];
foreach ($settingData as $setV) {
    $listChange[$setV['name']] = '';
    $setBall = json_decode($setV['data'], true);
    $goBall = (in_array($setV['name'], ['正常(1-5名)', '正常(6-10名)'])) ? $setV['name'] : false;
    $res = $fast->analysis($setV['act'], $setBall, $goBall, $setV['red_letter']);
    $oneBall = $fast->oneBall($setBall);
    #當連續12次都是低於3次的
    if (!$oneBall) {
        $point = ($setV['red_letter']) ? $configArr[$setV['act']]['red_point'] : $configArr[$setV['act']]['point'];
        if ($res['change'] >= $point) {
            $listChange[$setV['name']] ='change';
            if ($res['bite'] >= $configArr[$setV['act']]['bite']) {
                $listChange[$setV['name']] ='bite';
                if (abs($res['points']) >=  abs($configArr[$setV['act']]['point_period']))
                $topRes[] = ['name' => $setV['name'], 'act' => $setV['act']];    
            }
        }
    } else {
        if ($res['change'] >= $configArr[$setV['act']]['one_ball']) $listChange[$setV['name']] ='change';
    }
}
#往下x碼專用
$downArray = [
    '三碼' => 'three',
    '四碼' => 4,
    '五碼' => 5,
    '六碼' => 6,
];

foreach ($downArray as $k => $downNumber) {
    $res = $fast->analysis($downNumber, $ball);
    $numberAll[$downNumber] = '';
    if ($res['change'] >= $configArr['three']['point']) {
        $numberAll[$downNumber] = 'change';
        if ($res['bite'] >= $configArr['three']['bite']) {
            $numberAll[$downNumber] ='bite';
            if (abs($res['points']) >=  abs($configArr['three']['point_period']))
            $topRes[] = ['name' => $k . '全部', 'act' => $downNumber];    
        }
    }
    foreach ($ball as $bV) {
        $res = $fast->analysis($downNumber, [$bV]);
        $down[$downNumber][$bV] = ($res['change'] >= $configArr['three']['one_ball']) ? 'change' : '';
    }
    foreach ($ball as $bV) {
        $res = $fast->analysis($downNumber, [$bV], true);
        $downGoBall[$downNumber][$bV] = ($res['change'] >= $configArr['three']['one_ball']) ? 'change' : '';
    }
}
$fast->orderBySettingData($settingData);
?>
<HTML>
    <HEAD>
        <TITLE><?=$gameType['title']?></TITLE>
        <LINK rel=stylesheet type=text/css href="css/FastCar.css">
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h1><?=$gameType['dataWeb']?>--<?=$gameType['title']?>--</h1>
官方網站：<a target="_blank" href="<?=$gameType['urlWeb']?>"><?=$gameType['title']?>開獎網</a><br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<span style="font-size:13px;">更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</span><br>
<input class="button" type="button" onclick="location.href='view.php'" target="view_window" title="瀏覽" value ="近期期數">
<input class="button" type="button" onclick="window.open('setting.php')" target="_blank" title="瀏覽" value ="設定最愛">
&nbsp;&nbsp;<button class="summit">手動更新期數</button>    
&nbsp;&nbsp;<input class="button" type="button" onclick="location.href='../index.php'" target="view_window" title="瀏覽" value ="回首頁">

<br>
<table width="100%";  border=1 cellpadding=1 cellspacing=1>
    <tr>
        <td>型態</td>
        <td>藍字<img height="30px" src="new.gif"></td>
        <td>咬度<img height="30px" src="grounde.gif"></td>
        <td>藍字加總顯示</td>
        <td>單球</td>
        <td>紅字</td>
    </tr>
    <?php foreach ($configArr as $k => $value): ?>
    <tr  style="background-color:<?=$typeHead[$k]['color'];?>">
        <td><?=$typeHead[$k]['title']?></td>
        <td><?=$value['point']?></td>
        <td><?=$value['bite']?></td>
        <td><?=$value['point_period']?></td>
        <td><?=$value['one_ball']?></td>
        <td><?=$value['red_point']?></td>
    </tr>
    <?php endforeach;?>
</table>
<span style="font-size:13px;">&nbsp;&nbsp;//備註：::前綴的為選單一球，當有[紅字設定]跟[單一球]時，將會看[單一球]的連續藍字 </span><br>
<h3>查詢結果</h3>

<?php 
$setAct = '';
$one = '';
echo '<br>------咬度結果------<br>';
foreach ($topRes as $setK => $setV) :
    $backGroud = $typeHead[$setV['act']]['color'];
    $remind = "background-image:url('grounde.gif');";
    ?>
    <input type="button" style="width:200px;<?=$remind?> background-repeat:no-repeat;background-position:center;  background-color:<?=$backGroud?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listT<?=$setK?>').submit();" value="<?=$one?><?=$setV['name']?>" >
    <form class="formNoChang" action="result.php" id='listT<?=$setK?>' method="get" target="_blank">
        <input type="hidden" name="name" value="<?=$setV['name']?>">
        <input type="hidden" name="act" value="<?=$setV['act']?>">
        <?php if ($setV['name'] == '三碼全部'):?>
        <input type="hidden" name="threeBall" value="all">
        <input type="hidden" name="goBall" value="false">
        <?php endif;?>
    </form>
<?php $setAct = $setV['act'];
endforeach;?>

<?php 
$setAct = '';
$one = '';
foreach ($settingData as $setK => $setV) :
    if ($setV['act'] != $setAct) echo '<br>------' . $typeHead[$setV['act']]['title'] . '------<br>';
    $backGroud = $typeHead[$setV['act']]['color'];
    $remind = ($listChange[$setV['name']] == 'change') ? "background-image:url('new.gif');" : '';
    $remind = ($listChange[$setV['name']] == 'bite') ? "background-image:url('grounde.gif');" : $remind;
    if ($one == '' && $setV['oneBall']) echo '<br><br>';
    $one = ($setV['oneBall']) ? '::' : '';
    $redLetterFont = ($setV['red_letter'] > 0) ? " - {$setV['red_letter']}" : '';
    ?>
    <input type="button" style="width:200px;<?=$remind?> background-repeat:no-repeat;background-position:center;  background-color:<?=$backGroud?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('list<?=$setK?>').submit();" value="<?=$one?><?=$setV['name']?><?=$redLetterFont?>" >
    <form class="formNoChang" action="result.php" id='list<?=$setK?>' method="get" target="_blank">
        <input type="hidden" name="name" value="<?=$setV['name']?>">
        <input type="hidden" name="act" value="<?=$setV['act']?>">
    </form>
<?php $setAct = $setV['act'];
endforeach;?>
<!-- 往下x碼 -->
<?php foreach ($downArray as $k => $downNumber):
   $remind = '';
   $remind = ($numberAll[$downNumber] == 'change') ? "background-image:url('new.gif');" : '';
   $remind = ($numberAll[$downNumber] == 'bite') ? "background-image:url('grounde.gif');" : $remind; 
?>
<br>------往下<?=$k?>------<br>
<input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listTest<?=$k?>').submit();" value="<?=$k?>全部" >
<form class="formNoChang" action="result.php" id='listTest<?=$k?>' method="get" target="_blank">
    <input type="hidden" name="name" value="<?=$k?>全部">
    <input type="hidden" name="act" value="<?=$downNumber?>">
    <input type="hidden" name="threeBall" value="all">
    <input type="hidden" name="goBall" value="false">
</form>
<br>
    <?php foreach ($ball as $threeV) :
    $remind = '';
    $remind = ($down[$downNumber][$threeV] == 'change') ? "background-image:url('new.gif');" : '';   
    ?>
    <input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listThree<?=$k?><?=$threeV?>').submit();" value="跟<?=$threeV?>跑道" >
    <form class="formNoChang" action="result.php" id='listThree<?=$k?><?=$threeV?>' method="get" target="_blank">
        <input type="hidden" name="name" value="跟<?=$threeV?>跑道">
        <input type="hidden" name="act" value="<?=$downNumber?>">
        <input type="hidden" name="threeBall" value="<?=$threeV?>">
        <input type="hidden" name="goBall" value="false">
    </form>
    <?php endforeach;?>
    <br><br>
    <?php foreach ($ball as $threeV) :
    $remind = '';
    $remind = ($downGoBall[$downNumber][$threeV] == 'change') ? "background-image:url('new.gif');" : '';
    ?>
    <input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listThreeBall<?=$k?><?=$threeV?>').submit();" value="跟<?=$threeV?>號球" >
    <form class="formNoChang" action="result.php" id='listThreeBall<?=$k?><?=$threeV?>' method="get" target="_blank">
        <input type="hidden" name="name" value="跟<?=$threeV?>號球">
        <input type="hidden" name="act" value="<?=$downNumber?>">
        <input type="hidden" name="threeBall" value="<?=$threeV?>">
        <input type="hidden" name="goBall" value="true">
    </form>
    <?php endforeach?>
<?php endforeach?>
<br>------平移三碼------<br>
<?php foreach ($ball as $panV) :?>
<input type="button" style="width:200px; background-color:<?=$typeHead['pan']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listPan<?=$panV?>').submit();" value="平移<?=$panV?>號球" >
<form class="formNoChang" action="result.php" id='listPan<?=$panV?>' method="get" target="_blank">
    <input type="hidden" name="name" value="平移<?=$panV?>號球">
    <input type="hidden" name="act" value="pan"">
    <input type="hidden" name="panBall" value="<?=$panV?>">
</form>
<?php endforeach?>
<br><br><br><br>
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