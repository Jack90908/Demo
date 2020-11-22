<?php
require_once "../Model.php";
$db = new Model('cm');
$dbSchema = $db->query("select * from information_schema.columns where table_name='ball_config' AND column_name = 'act'");
$dbName = $db->fetch($dbSchema);
if (!$dbName) {
    $db->query("ALTER TABLE `ball_config` ADD `act` VARCHAR(10) NOT NULL DEFAULT 'hand' AFTER `point_period`, ADD UNIQUE `act_only` (`act`);");
    $resGet = $db->get('ball_config');
    $res = $db->fetch($resGet);
    $data = [
        'point' => $res['point'],
        'bite_ave' => $res['bite_ave'],
        'bite' => $res['bite'],
        'one_ball' => $res['one_ball'],
        'basis' => $res['basis'],
        'red_point' => $res['red_point'],
        'point_period' => $res['point_period'],
    ];
    $data['act'] = 'goBall';
    $db->add('ball_config', $data);
    $data['act'] = 'three';
    $db->add('ball_config', $data);
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