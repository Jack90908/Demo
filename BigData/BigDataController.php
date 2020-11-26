<?php

namespace BigData;

require_once 'BigDataConfig.php';
require_once 'BigDataService.php';

use BigData\BigDataConfig;
use BigData\BigDataService;

/**
 * 控制BigData 進來的項目及拿取邏輯的項目
 */
class BigDataController extends BigDataConfig
{
    /**
     * get參數
     */
    protected array $get;

    /**
     * 邏輯層
     *
     * @var class
     */
    protected $service;

    public function __construct()
    {
        $this->get = $_GET;
        $this->service = new BigDataService();
    }
    /**
     * 回傳主頁需要的項目
     *
     * @return array
     */
    public function index()
    {
        $data['title'] = $this->getTitle();
        $data['act'] = self::ACT;
        $data['setting'] = $this->service->getSetting();
        return $data;
    }

    private function getTitle()
    {
        if(isset($this->get['hell']) && isset(self::TITLE[$this->get['hell']])) {
            return self::TITLE[$this->get['hell']];
        }

        return self::TITLE[self::DEFAULT];
    }
}