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
#content{
    color:#ccc; 
}
.formNoChang{
    margin:0px; display:inline
}
</style>
<?php

require_once "Model.php";
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
    if($insertSet->error != '00000') echo "alert('$msg')";
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
                ->get('game', ['id','creat_time'], 'LIMIT 1');
list($id, $uptime) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$period = substr($id, -3, 3);
$limit = (isset($_POST['limit'])) ? $_POST['limit'] :100;
$getData = $db->order('id', 'DESC')
            ->get('game', '*', [0, $limit]);
$data = $db->fetchAll($getData);
$setGet = $db->get('setting', ['name', 'act']);
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
?>
<HTML>
    <HEAD>
        <TITLE>設定最愛</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>
<body>
<input class="button" type="button" onclick="location.href='index.php'" target="_self" title="瀏覽" value ="返回首頁">
<button class="summit">手動更新期數</button>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <form action="setting.php" method="post" name=formS>
        <tr>
            <td>最愛暱稱</td>
            <td>型態</td>
            <td>第一名</td>
            <td>第二名</td>
            <td>第三名</td>
            <td>第四名</td>
            <td>第五名</td>
            <td>第六名</td>
            <td>第七名</td>
            <td>第八名</td>
            <td>第九名</td>
            <td>第十名</td>
        </tr>
        <tr>
            <td>
                <input type="text" id="content" name="content" value="請輸入暱稱"/>
            </td>
            <td>
                <select name="act"">
                　<option value="hand" checked>手選-當期</option>
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
<?php foreach ($settingData as $setK => $setV) :?>
    <input style="width:200px" class="button" href="javascript:void(0)" onclick="document.getElementById('list<?=$setK?>').submit();" value="<?=$setV['name']?>" >
    <form class="formNoChang" action="result.php" id='list<?=$setK?>' method="get" target="_blank">
        <input type="hidden" name="name" value="<?=$setV['name']?>">
        <input type="hidden" name="act" value="<?=$setV['act']?>">
    </form>
<?php endforeach;?>

<br>
<span style="font-size:13px;">最後更新資料時間<?=$uptime?></span><br>
<h>更新最新期數：<?=$date['year']?>年<?=$date['month']?>月<?=$date['day']?>日--<?=$period?>期</h>
<br><br><br><br>
<br>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <form action="setting.php" id='del' method="post">
        <tr>
            <td>刪除不要的設定值</td>
            <td>
                <select name="name">
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
</script>

<!-- CREATE TABLE `roberDemo`.`setting` ( `name` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `act` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , `data` TEXT NOT NULL , PRIMARY KEY (`name`)) ENGINE = InnoDB; -->