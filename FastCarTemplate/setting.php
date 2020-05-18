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
<br>
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