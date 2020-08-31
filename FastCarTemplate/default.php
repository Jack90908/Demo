<?php
require_once "../Model.php";
$db = new Model('cm');
$dbSchema = $db->query("select * from information_schema.columns where table_name='ball_config' AND column_name = 'red_point'");
$dbName = $db->fetch($dbSchema);
if (!$dbName) {
    $db->query("ALTER TABLE `ball_config` ADD `red_point` INT(10) NOT NULL DEFAULT '7' AFTER `basis`;");
}

$getPeriod = $db->order('id', 'DESC')
                ->get($gameType['gameDB'], ['id','creat_time', 'period'], 'LIMIT 1');
list($id, $uptime, $period) = $db->fetch($getPeriod, PDO::FETCH_NUM);
$date['year'] = substr($id, 0, 4);
$date['month'] = substr($id, 4, 2);
$date['day'] = substr($id, 6, 2);
$date['Ymd'] = $date['year'].$date['month'].$date['day'];
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
    'three' => [
        'title' => '三碼',
        'color' => 'gainsboro',
        'type'  => '號球' 
    ],
    'pan' => [
        'title' => '平移',
        'color' => 'beige',
        'type'  => '號球'
    ]
];
$configView = [
    'bite_ave' => '平均咬度',
    'bite' => '共咬幾球'
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