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
$getData = $db->order('id', 'DESC')
                ->get($gameType['gameDB'], '*', "LIMIT 13");
$data = $db->fetchAll($getData);
krsort($data);
$setGet = $db->order('act')
            ->order('name')
            ->get('setting', ['name', 'act', 'data']);
$settingData = $db->fetchAll($setGet);

#以下為每個最愛近300期每期 少於3筆中獎 12個以上標記顏色
foreach ($settingData as $setV) {
    $listChange[$setV['name']] = '';
    $change = 0;
    $setBall = json_decode($setV['data'], true);
    switch ($setV['act']) {
        case 'hand' :
            #設定球的中獎
            #13期的期數，以名次為群組
            foreach ($data as $dK => $dV) {
                $frist = (!isset($frist)) ? $dK : $frist;
                $bingo[substr($dV['period'], -3, 3)] = 0;
                foreach ($ball as $num) {
                    if (isset($setBall[$num]) && in_array($dV["no{$num}"], $setBall[$num])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                if ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) $change ++;
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
                if ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) $change ++;
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
                    $move = ($num == 10) ? 1 : $num + 1;
                    if ($dK != $frist && in_array($dV["no{$move}"], $setBall[$beforBall["no{$num}"]])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                if ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) $change ++;
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
<h3>查詢結果</h3>

<?php 
$setAct = '';
foreach ($settingData as $setK => $setV) :
    if ($setV['act'] != $setAct) echo '<br>------' . $typeHead[$setV['act']]['title'] . '------<br>';
    $backGroud = $typeHead[$setV['act']]['color'];
    $remind = ($listChange[$setV['name']] == 'change') ? "background-image:url('new.gif');" : '';
    ?>
    <input type="button" style="width:200px;<?=$remind?> background-repeat:no-repeat;background-position:center;  background-color:<?=$backGroud?>" class="button_sel" href="javascript:void(0)" onclick="document.getElementById('list<?=$setK?>').submit();" value="<?=$setV['name']?>" >
    <form class="formNoChang" action="result.php" id='list<?=$setK?>' method="get" target="_blank">
        <input type="hidden" name="name" value="<?=$setV['name']?>">
        <input type="hidden" name="act" value="<?=$setV['act']?>">
    </form>
<?php $setAct = $setV['act'];
endforeach;?>

<br><br><br><br>
&nbsp;&nbsp;<button class="summit">手動更新期數</button>    
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