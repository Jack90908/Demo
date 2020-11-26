<?php
require_once '../BigDataController.php';
use BigData\BigDataController;
$big_data = new BigDataController();
$index = $big_data->index();
?>
<html>
    <head>
        <TITLE>大量數據分析</TITLE>
        <LINK rel=stylesheet type=text/css href="../../FastCarTemplate/FastCar.css">
        <LINK rel=stylesheet type=text/css href="BigData.css">
        <link rel="icon" href="fastCar.ico" type="image/x-icon"/>
        <META http-equiv=Content-Type content="text/html; charset=utf-8">
    </head>
    <body>
        <!-- 瀏覽Bar -->
        <nav>
            <ul class="flex-nav">
                <li><a href="../../">回首頁</a></li>
                <li><a href="?hell=FastCarIn168">168極速賽車</a></li>
                <li><a href="?hell=LuckyFerryIn168">168幸運飛艇</a></li>
                <li><a href="?hell=LuckAusIn168">168澳洲幸運10</a></li>
                <!-- <li><a href="?hell=FastCarInWord">世界極速賽車</a></li> -->
                <!-- <li><a href="?hell=FastShipInWord">世界極速飛艇</a></li> -->
            </ul>
        </nav>
        <!-- 標題 -->
        <h2 class="title-center"><?=$index['title']?>--數據分析</h2>
        <div>
            <form class="getBingo">
                <table border=1 cellpadding=2 cellspacing=1 class="title-center resultTable">
                    <tr>
                        <td>大廳</td>
                        <td>時間</td>
                        <td>型態</td>
                        <td>最愛</td>
                        <td><div class="loading">建置環境中...請稍候</div></td>
                    </tr>
                    <tr>
                        <td><?=$index['title']?></td>
                        <td>一年</td>
                        <td>
                            <select id="act" name="act">
                                <?php foreach ($index['act'] as $k => $act): ?>
                                <option value="<?=$k?>"><?=$act?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td>
                            <select id="setting" name="setting">
                                <?php foreach ($index['setting']['hand'] as $setting): ?>
                                <option value="<?=key($setting)?>"><?=key($setting)?></option>
                                <?php endforeach;?>
                            </select>
                        </td>
                        <td><input class="submit_ball" type="button" disabled value="查詢"></td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="show_result">
            <table border=1 cellpadding=2 cellspacing=1 class="title-center resultTable">
                <tr>
                    <td>最大連續藍字</td>
                    <td>最大藍字加總分(單球無此加總分)</td>
                    <td>是否為單球</td>
                </tr>
                <tbody id="tbody-result">
                </tbody>
            </table>
        </div>
    </body>
</html>
<script src="https://cdn.staticfile.org/jquery/1.10.2/jquery.min.js">
</script>
<script language="javascript">
    $(document).ready(function(){
        // 查看建設是否完成
        get_data_url = '../BigDataApi.php';
        get_data = {
            method: 'getData',
            type: $.getUrlVar("hell")
        };
        $.ajax({
            type: "GET",
            url: '../BigDataApi.php',
            data: get_data,
            success: function()
            {
                $('.submit_ball').attr('disabled', false)
                $('.loading').hide()
            }
        });

        var tbody = window.document.getElementById("tbody-result");
        $(".submit_ball").click(function(e) {
            e.preventDefault();
            var form = $('.getBingo').serialize()
            $.ajax({
                type: "GET",
                url: '../BigDataApi.php?method=getBingo&type=' + $.getUrlVar("hell"),
                data: form, // serializes the form's elements.
                contentType: false,
                cache: false,
                processData: false,
                success: function(data)
                {
                    tbody.innerHTML = ''
                    res = JSON.parse(data)
                    one = (res.oneBall == true) ? '單球' : '非單球';
                    str = 
                    '<td>' + res.maxChange + '</td>' +
                    '<td>' + res.maxPoints + '</td>' +
                    '<td>' + one + '</td>';
                    tbody.innerHTML = str;
                }
            });
        });
        // 處理型態搜尋最愛判斷
        var setting_data = <?= json_encode($index['setting'], JSON_UNESCAPED_UNICODE)?>;
        $('#act').change(function (){
            $('#setting').empty();
            if (this.value == 'three') {
                var setting_index = 3
            } else {
                var setting_index = this.value;
            }
            settings = setting_data[setting_index];
            $(settings).each(function(keys, setting) {
                for (var value in setting) {
                    if (setting_index == 'hand' || setting_index == 'goBall') {
                        setting_input = value;
                    } else {
                        setting_input = setting[value].threeBall;
                        if (setting[value].goBall == true) {
                            setting_input = 'ball' + setting_input;
                        }
                    }
                    $("#setting").append($("<option></option>").attr("value", setting_input).text(value));
                }
            })
        });
    });

    $.extend({ 
        getUrlVars: function(){ 
            var vars = [], hash; 
            var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&'); 
            for(var i = 0; i < hashes.length; i++) 
            { 
            hash = hashes[i].split('='); 
            vars.push(hash[0]); 
            vars[hash[0]] = hash[1]; 
            } 
            return vars; 
        }, 
        getUrlVar: function(name){ 
            return $.getUrlVars()[name]; 
        } 
	}); 
</script>