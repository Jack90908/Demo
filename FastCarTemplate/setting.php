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
require_once "default.php";
$_POST['setAct'] = (isset($_POST['setAct'])) ? $_POST['setAct'] : '';
$_POST['config'] = (isset($_POST['config'])) ? $_POST['config'] : '';
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
        'data' => $ball,
        'red_letter' => $_POST['redLetter']

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
if ($_POST['config']) {
    $configChange = [
        '平均咬度' => 'bite_ave',
        '共咬幾球' => 'bite'
    ];
    $array = [
        'point' => $_POST['point'],
        'bite' => $_POST['bite'],
        'bite_ave' => $_POST['bite_ave'],
        'basis' => $configChange[$_POST['basis']],
        'one_ball' => $_POST['one_ball'],
    ];
    $db->set('ball_config', $array);
    echo "<script>";
    echo "alert('修改偏好成功！');";
    echo "document.location.href='setting.php'
    </script>";

}
$getPeriod = $db->order('id', 'DESC')
                ->get($gameType['gameDB'], ['id','creat_time', 'period'], 'LIMIT 1');
list($id, $uptime, $period) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$getConfig = $db->get('ball_config');
$config = $db->fetch($getConfig);
$setGet = $db->order('act')
            ->order('name')
            ->get('setting', ['name', 'act', 'data']);
$settingData = $db->fetchAll($setGet);
$act = (!isset($_GET['act'])) ? 'hand' : $_GET['act'];
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
<h3>設定偏好</h3>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=lightgray>
    <form action="setting.php" method="post" name=formS>
        <input type="hidden" name='config' value="update">
        <tr>
            <td style="width:200px">連續藍字提示</td>
            <td style="width:200px">連續咬幾球</td>
            <td style="width:200px">平均咬幾球</td>
            <td style="width:200px">選擇種類</td>
            <td style="width:200px">單球連續藍字</td>
            <td style="width:200px"></td>
        </tr>
        <tr>
            <td><input name="point" type="text" value="<?=$config['point']?>"></td>
            <td><input name="bite" type="text" value="<?=$config['bite']?>"></td>
            <td><input name="bite_ave" type="text" value="<?=$config['bite_ave']?>"></td>
            <td>
                <select style="width:200px" name="basis">
                    <?php foreach ($configView as $setK => $setV) :
                    $chk = ($config['basis'] == $setK) ? 'selected' : '';
                    ?>
                    　<option value="<?=$setV?>" <?=$chk?>><?=$setV?></option>
                    <?php endforeach;?>
                </select>
            </td>
            <td><input name="one_ball" type="text" value="<?=$config['one_ball']?>"></td>
            <td><input class="summit" type="submit" value="修改"></td>
        </tr>
    </form>
</table>
<hr size="8px" color=#00000>
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
                    if (in_array($tK, ['three', 'pan'])) continue;
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
            　      <option value="" checked></option>
                    <?php foreach($ball as $seachV):?>
            　      <option value="<?=$seachV?>"><?=$seachV?></option>
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
            <td>
                顯示紅字設定<br>
                <input type="radio" name="redLetter" value="0" checked>預設<br>
                <input type="radio" name="redLetter" value="1">一次<br>
                <input type="radio" name="redLetter" value="2">兩次
            </td>
        </tr>
    </form>
</table>
<!-- <h3>編輯最愛</h3>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=<?=$typeHead[$act]['color']?>>
    <form action="setting.php" method="post" name=formS>
    </form>
</table> -->
<hr size="8px" color=#00000>
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