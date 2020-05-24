<?php
require_once "../Model.php";
$db = new Model('cm');
$dbSchema = $db->query("SHOW TABLES LIKE 'ball_config'");
$dbName = $db->fetch($dbSchema);
if (!$dbName) {
    $db->query("-- phpMyAdmin SQL Dump
    -- version 5.0.2
    -- https://www.phpmyadmin.net/
    --
    -- 主機： localhost
    -- 產生時間： 2020 年 05 月 20 日 18:58
    -- 伺服器版本： 10.4.11-MariaDB
    -- PHP 版本： 7.2.29
    
    SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
    START TRANSACTION;
    SET time_zone = '+00:00';
    
    --
    -- 資料庫： `roberDemo`
    --
    
    -- --------------------------------------------------------
    
    --
    -- 資料表結構 `ball_config`
    --
    
    CREATE TABLE `ball_config` (
      `id` int(11) NOT NULL,
      `point` int(11) NOT NULL DEFAULT 12,
      `bite_ave` decimal(11,2) NOT NULL DEFAULT 1.00,
      `bite` int(11) NOT NULL DEFAULT 10,
      `one_ball` int(11) NOT NULL DEFAULT 10,
      `basis` varchar(20) NOT NULL DEFAULT 'bite_ave'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    
    --
    -- 傾印資料表的資料 `ball_config`
    --
    
    INSERT INTO `ball_config` (`id`, `point`, `bite_ave`, `bite`, `one_ball`, `basis`) VALUES
    (1, 12, '0.70', 12, 12, 'bite');
    
    --
    -- 已傾印資料表的索引
    --
    
    --
    -- 資料表索引 `ball_config`
    --
    ALTER TABLE `ball_config`
      ADD PRIMARY KEY (`id`);
    
    --
    -- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
    --
    
    --
    -- 使用資料表自動遞增(AUTO_INCREMENT) `ball_config`
    --
    ALTER TABLE `ball_config`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
    COMMIT;
    ");
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