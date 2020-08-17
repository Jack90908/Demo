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
$config = $db->fetch($getConfig);
$nowConfig = $config[$config['basis']];
#總共搜尋幾球
$getCount = $config['point'] + 16;
$getData = $db->order('id', 'DESC')
                ->get($gameType['gameDB'], '*', "LIMIT {$getCount}");
$data = $db->fetchAll($getData);
krsort($data);

$setGet = $db->order('act')
            ->order('name')
            ->get('setting', ['name', 'act', 'data', 'red_letter']);
$settingData = $db->fetchAll($setGet);
$fast = new FastCarService($data);
#加入跟球一半的數據
$fast->addHalf($settingData);
#以下為每個最愛近300期每期 少於3筆中獎 12個以上標記顏色
foreach ($settingData as $setV) {
    $listChange[$setV['name']] = '';
    $setBall = json_decode($setV['data'], true);
    $res = $fast->analysis($setV['act'], $setBall, false, $setV['red_letter']);
    $oneBall = $fast->oneBall($setBall);
    #當連續12次都是低於3次的
    if (!$oneBall) {
        if ($res['change'] >= $config['point']) {
            $listChange[$setV['name']] ='change';
            if ($config['basis'] == 'bite_ave') {
                if ($res['bite'] / $res['change'] > $config['bite_ave'] ) $listChange[$setV['name']] ='bite';
            } else {
                if ($res['bite'] >= $config['bite']) $listChange[$setV['name']] ='bite';
            }
        }
    } else {
        if ($res['change'] >= $config['one_ball']) $listChange[$setV['name']] ='change';
    }
}
#三碼專用
$res = $fast->analysis('three', $ball);
$threeAll = '';
if ($res['change'] >= $config['point']) {
    $threeAll = 'change';
    if ($config['basis'] == 'bite_ave') {
        if ($res['bite'] / $res['change'] > $config['bite_ave'] ) $threeAll ='bite';
    } else {
        if ($res['bite'] >= $config['bite']) $threeAll ='bite';
    }
}
foreach ($ball as $bV) {
    $res = $fast->analysis('three', [$bV]);
    $three[$bV] = ($res['change'] >= $config['one_ball']) ? 'change' : '';
}
foreach ($ball as $bV) {
    $res = $fast->analysis('three', [$bV], true);
    $threeGoBall[$bV] = ($res['change'] >= $config['one_ball']) ? 'change' : '';
}
$fast->orderBySettingData($settingData);
?>
<HTML>
    <HEAD>
        <TITLE>極速賽車</TITLE>
        <LINK rel=stylesheet type=text/css href="css/FastCar.css">
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<h1><?=$gameType['dataWeb']?>--極速賽車---請選擇號碼</h1>
官方網站：<a target="_blank" href="<?=$gameType['urlWeb']?>"><?=$gameType['dataWeb']?>開獎網</a><br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<span style="font-size:13px;">更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</span><br>
<input class="button" type="button" onclick="location.href='view.php'" target="view_window" title="瀏覽" value ="近期期數">
<input class="button" type="button" onclick="window.open('setting.php')" target="_blank" title="瀏覽" value ="設定最愛">
&nbsp;&nbsp;<button class="summit">手動更新期數</button>    
<br>

<span style="font-size:20px;">
    ----藍字：<?=$config['point']?><img src="new.gif">/
    &nbsp;&nbsp;<?=$configView[$config['basis']]?>：<?=$nowConfig?><img src="grounde.gif">/
    &nbsp;&nbsp;單球：<?=$config['one_ball']?><img src="new.gif">----
</span>
<span style="font-size:13px;">&nbsp;&nbsp;//備註：只有超過連續藍字咬度才生效 ::前綴的為選單一球 </span><br>

<h3>查詢結果</h3>

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
<br>------往下三碼------<br>
<?php 
$remind = '';
$remind = ($threeAll == 'change') ? "background-image:url('new.gif');" : '';
$remind = ($threeAll == 'bite') ? "background-image:url('grounde.gif');" : $remind;
?>
<input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listTest').submit();" value="三碼全部" >
<form class="formNoChang" action="result.php" id='listTest' method="get" target="_blank">
    <input type="hidden" name="name" value="三碼全部">
    <input type="hidden" name="act" value="three">
    <input type="hidden" name="threeBall" value="all">
    <input type="hidden" name="goBall" value="false">
</form>
<br>
<?php foreach ($ball as $threeV) :
$remind = '';
$remind = ($three[$threeV] == 'change') ? "background-image:url('new.gif');" : '';   
?>
<input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listThree<?=$threeV?>').submit();" value="跟<?=$threeV?>跑道" >
<form class="formNoChang" action="result.php" id='listThree<?=$threeV?>' method="get" target="_blank">
    <input type="hidden" name="name" value="跟<?=$threeV?>跑道">
    <input type="hidden" name="act" value="three">
    <input type="hidden" name="threeBall" value="<?=$threeV?>">
    <input type="hidden" name="goBall" value="false">
</form>
<?php endforeach?>
<br><br>
<?php foreach ($ball as $threeV) :
$remind = '';
$remind = ($threeGoBall[$threeV] == 'change') ? "background-image:url('new.gif');" : '';
?>
<input type="button" style="width:200px; <?=$remind?> background-repeat:no-repeat;background-position:center; background-color:<?=$typeHead['three']['color']?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('listThreeBall<?=$threeV?>').submit();" value="跟<?=$threeV?>號球" >
<form class="formNoChang" action="result.php" id='listThreeBall<?=$threeV?>' method="get" target="_blank">
    <input type="hidden" name="name" value="跟<?=$threeV?>號球">
    <input type="hidden" name="act" value="three">
    <input type="hidden" name="threeBall" value="<?=$threeV?>">
    <input type="hidden" name="goBall" value="true">
</form>
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