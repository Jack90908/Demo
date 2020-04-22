<?php 
$tableStyle = [
    '名次',
    '第一',
    '第二',
    '第三',
    '第四',
    '第五',
    '第六',
    '第七',
    '第八',
    '第九',
    '第十',
];
$ball = [
    1,
    2,
    3,
    4,
    5,
    6,
    7,
    8,
    9,
    10
];
?>
<HTML>
    <HEAD>
        <TITLE>介面</TITLE>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </HEAD>

<h>請選擇號碼</h><br><br><br><br>
<table border=1 cellpadding=2 cellspacing=1 width=1020 bgcolor=#fafad2>
    <form action="test2".php method="post" name=formS>
        <?php 
        foreach ($tableStyle as $tableKey => $tableValue) :?>
            <tr bgcolor="#afeeee">
            <td><font color=#000000><?=$tableValue?></font></td>
            <?php if ($tableValue == '名次') continue;?>
            <td>
                <?php foreach ($ball as $ballVaule) :?>
                <input type="checkbox" name="ball[<?=$tableKey?>]" onclick="checkbox_clicked(this)" value="<?=$ballVaule?>"><font color=#000000><?=$ballVaule?>&nbsp;&nbsp;&nbsp;</font>
                <?php endforeach?>
            </td>
            </tr>
        <?php endforeach;?>
        <tr>
            <td>
                <input type="submit" value="統計">
            </td>
        </tr>
    </form>
</table>

</HTML>

<script language="javascript">
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
</script>