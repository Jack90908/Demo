<?php

namespace BigData;
/**
 * 控制BigData 進來的項目及拿取邏輯的項目
 */
abstract class BigDataConfig
{
    /**
     * 選擇大廳項目
     */
    protected const HELL = [
        'BeijingCarInWord',
        'FastCarIn168',
        'FastCarInWord',
        'FastShipInWord',
        'LuckAusIn168',
        'LuckyFerry',
        'LuckyFerryIn168',
    ];

    /**
     * 標題開頭
     */
    protected const TITLE = [
        'BeijingCarInWord' => '',
        'FastCarIn168' => '168極速賽車',
        'FastCarInWord' => '世界極速賽車',
        'FastShipInWord' => '世界極速飛艇',
        'LuckAusIn168' => '168澳洲幸運10',
        'LuckyFerry' => '',
        'LuckyFerryIn168' => '168幸運飛艇',
    ];

    protected const PATH = 'https://api.apiose122.com/pks/getPksHistoryList.do?';

    protected const LOTCODE = [
        'BeijingCarInWord' => '',
        'FastCarIn168' => '10037',
        'FastCarInWord' => '世界極速賽車',
        'FastShipInWord' => '世界極速飛艇',
        'LuckAusIn168' => '10012',
        'LuckyFerry' => '',
        'LuckyFerryIn168' => '10057',
    ];

    protected const ORIGINDATA = [
        'BeijingCarInWord' => 'beijing_car',
        'FastCarIn168' => 'fast_car',
        'FastCarInWord' => 'fast_car_word',
        'FastShipInWord' => 'fast_ship_word',
        'LuckAusIn168' => 'lucky_aus168',
        'LuckyFerry' => '',
        'LuckyFerryIn168' => 'lucky_ferry168',
    ];
    /**
     * 預設目錄
     */
    protected const DEFAULT = 'LuckyFerryIn168';

    /**
     * 選擇要分析的型態的
     */
    protected const ACT = [
        'hand'   => '手選-當期',
        'goBall' => '跟球-下期',
        'three'  => '往下三碼',
        '4'      => '往下四碼',
        '5'      => '往下五碼',
        '6'      => '往下六碼',
        '7'      => '往下七碼',
        '8'      => '往下八碼',
        '9'      => '往下九碼',
        '10'     => '往下十碼',
    ];
}