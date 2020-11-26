<?php

namespace BigData;

require_once 'BigDataApiService.php';

use BigData\BigDataApiService;

new BigDataApi();
/**
 * 專門寫Api邏輯
 */
class BigDataApi
{
    protected $service;

    public function __construct()
    {
        $this->service = new BigDataApiService($_GET['type'] ?? null);

        if ($_GET['method'] == 'getData') {
            $this->getData();
        }
        if ($_GET['method'] == 'getBingo') {
            $this->getBingo($_GET['act'], $_GET['setting']);
        }
    }

    protected function getData()
    {
        $this->service->getData();
    }

    protected function getBingo($act, $setting)
    {
        $res = $this->service->getBingo($act, $setting);
        echo json_encode($res);
    }
}