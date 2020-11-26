<?php

namespace BigData;

require_once '../Model.php';
require_once 'BigDataConfig.php';
require_once 'BigDataGetData.php';
require_once 'BingoService.php';

use BigData\BigDataConfig;
use BigData\BigDataGetData;

/**
 * 專門寫Api邏輯
 */
class BigDataApiService extends BigDataConfig
{
    protected $model;    
    protected $type;
    protected $table;

    public function __construct($type = null)
    {
        $this->model = new \Model();
        if (!in_array($type, self::HELL)) $type = self::DEFAULT;
        if (is_null($type)) $type = self::DEFAULT;
        $this->type = $type;
        $this->table = 'big_' . $this->toUnderScore($this->type);
    }

    // 取得資料判斷
    public function getData()
    {
        if ($this->firstCreat()) {
            $this->getAllData();
        } elseif ($getLostData = $this->lostData()) {
            $dayStrtotime = strtotime(date('Y-m-d')) - strtotime($getLostData['date']);
            $diffDay = round($dayStrtotime/3600/24);
            $this->getAllData($diffDay);
        } else {
            $this->getDataBase();
        }
        return true;
    }

    // 判斷是否為第一次建立
    private function firstCreat()
    {
        $existQ = $this->model->query("SHOW TABLES LIKE '%{$this->table}%'");
        $existTable = $this->model->fetch($existQ);
        #如果返回 true 代表不是第一次創建 回傳 false
        if ($existTable) return false;
        $this->model->query("CREATE TABLE `{$this->table}` LIKE `lucky_aus168`");

        return true;
    }

    // 第一次取得一年份資料
    private function getAllData($day = null)
    {
        $searchDay = ($day == null) ? 365 : $day;
        $getData = new \BigDataGetData();
        for ($i = $searchDay; $i >= 0; $i--) {
            $date = date('Y-m-d',strtotime("-{$i} day"));
            $url = self::PATH . 'date=' . $date . '&lotCode=' . self::LOTCODE[$this->type];
            // $getData->setUrlAndDb($url, $this->table);
            // if ($getData->curl_get()) {
            //     $getData->fastCarSOP();
            // }
        }
    }

    // 如資料庫跟大數據資料庫不同, 一樣從官網獲得
    private function lostData()
    {
        $get = $this->model->order('id', 'ASC')
            ->get(self::ORIGINDATA[$this->type], '*', 'LIMIT 1');
        $originRes = $this->model->fetch($get);

        $getBig = $this->model->order('id', 'DESC')
            ->get($this->table, '*', 'LIMIT 1');
        $bigRes = $this->model->fetch($getBig);

        // 如果原資料的最小值比 數據庫的最大值還大 代表有斷層則需要找資料庫 
        if ($originRes['id'] > $bigRes['id']) {
            return $bigRes;
        }
        return false;
    }

    // 從資料庫取得
    private function getDataBase()
    {
        $origin = self::ORIGINDATA[$this->type];
        $query = "INSERT INTO $this->table (SELECT * FROM {$origin} WHERE id > (SELECT MAX(id) FROM $this->table))";
        $this->model->query($query);
    }
    //驼峰命名轉底線命名
    private function toUnderScore($str)
    {
        $dstr = preg_replace_callback('/([A-Z]+)/',function($matchs)
        {
            return '_'.strtolower($matchs[0]);
        },$str);
        return trim(preg_replace('/_{2,}/','_',$dstr),'_');
    }

    // 取得連續藍字
    public function getBingo($act, $setting)
    {
        // 最愛參數
        if (in_array($act, ['hand', 'goBall'])) {
            $getSet = $this->model->where('name', $setting)
                        ->where('act', $act)
                        ->get('setting');
            $setRes = $this->model->fetch($getSet)['data'];
            $setRes = json_decode($setRes, true);
        } else {
            $setRes = $this->handleBall($setting);
        }


        // 配置藍字等等
        $getConfig = $this->model->where('act', $act)
                    ->get('ball_config');
        $configRes = $this->model->fetch($getConfig);

        $getData = $this->model->get($this->table);
        $dataRes = $this->model->fetchAll($getData);
        krsort($dataRes);
        $bingo = new \BingoService($dataRes);
        return $bingo->analysis($act, $setRes, $configRes);
    }

    // 處理往下幾碼的字串改data
    private function handleBall($setting) {

        if (strpos($setting, 'ball') !== false) {
            return [
                'ball' => [substr($setting, 4)],
                'goBall' => true
            ];
        }

        if (strpos($setting, 'all') !== false) {
            return [
                'ball' => [
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
                ],
                'goBall' => false
            ];
        }

        return [
            'ball' => [$setting],
            'goBall' => false,
        ];
    }
}