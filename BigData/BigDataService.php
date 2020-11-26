<?php

namespace BigData;

require_once '../../Model.php';

/**
 * 專門寫Service邏輯
 */
class BigDataService
{
    protected $model;

    protected $downNumber = [
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
    ];

    public function __construct()
    {
        $this->model = new \Model();
    }

    public function getSetting()
    {
        $get = $this->model->get('setting');
        $res = $this->model->fetchAll($get);
        $data = [];
        
        foreach ($res as $setting) {
            $data[$setting['act']][] = [$setting['name'] => ''];
        }
        //往下x碼
        foreach ($this->downNumber as $setting) {
                $data[$setting][] = [
                    $setting . '碼全部' => [
                        'threeBall' => 'all',
                        'goBall' => false,
                    ]
                ];
            for($i = 1; $i <= 10; $i++) {
                $data[$setting][] = [
                    '跟' . $i . '跑道' => [
                        'threeBall' => $i,
                        'goBall' => false,
                    ]
                ];
            }
            for($i = 1; $i <= 10; $i++) {
                $data[$setting][] = [
                    '跟' . $i . '號球' => [
                        'threeBall' => $i,
                        'goBall' => true,
                    ]
                ];
            }
        }

        return $data;
    }
}