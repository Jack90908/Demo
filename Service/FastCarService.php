<?php
class FastCarService {

    private $data = array();
    private $ball = [
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
    private $oneBallSel = false;
    private $changeRange = 3;
    private $bitRange = 2;
    #藍字分數計算期數 
    private $pointsPeriod = 100;
    public function __construct($data)
    {
        if (!empty($data)) $this->data = $data;
    }

    #加入一半數據的跟球
    public function addHalf(&$setData, $select = null)
    {
        $half = [
            0 => [
                'name' => '正常(1-5名)',
                'act' => 'goBall',
                'red_letter' => '2',
                'data' => json_encode([
                    '1' => ['1' => 1, '2' => 4, '3' => 7],
                    '2' => ['1' => 2, '2' => 5, '3' => 8],
                    '3' => ['1' => 3, '2' => 6, '3' => 9],
                    '4' => ['1' => 1, '2' => 4, '3' => 7],
                    '5' => ['1' => 2, '2' => 5, '3' => 8],
                    '6' => ['1' => 3, '2' => 6, '3' => 9],
                    '7' => ['1' => 1, '2' => 4, '3' => 7],
                    '8' => ['1' => 2, '2' => 5, '3' => 8],
                    '9' => ['1' => 3, '2' => 6, '3' => 9],
                    '10' => ['1' => 1, '2' => 5, '3' => 10],
                ]),
            ],
            1 => [
                'name' => '正常(6-10名)',
                'act' => 'goBall',
                'red_letter' => '2',
                'data' => json_encode([
                    '1' => ['1' => 1, '2' => 4, '3' => 7],
                    '2' => ['1' => 2, '2' => 5, '3' => 8],
                    '3' => ['1' => 3, '2' => 6, '3' => 9],
                    '4' => ['1' => 1, '2' => 4, '3' => 7],
                    '5' => ['1' => 2, '2' => 5, '3' => 8],
                    '6' => ['1' => 3, '2' => 6, '3' => 9],
                    '7' => ['1' => 1, '2' => 4, '3' => 7],
                    '8' => ['1' => 2, '2' => 5, '3' => 8],
                    '9' => ['1' => 3, '2' => 6, '3' => 9],
                    '10' => ['1' => 1, '2' => 5, '3' => 10],
                ]),
            ],
        ];
        if ($setData) $setData = array_merge($setData, $half);
        if ($select == '正常(1-5名)') return $half[0];
        if ($select == '正常(6-10名)') return $half[1];
    }
    public function orderBySettingData(&$setData)
    {
        $orderArr = array();
        foreach ($setData as $setV) {
            $setBall = json_decode($setV['data'], true);
            if ($this->oneBall($setBall)) {
                $orderArr[$setV['act']][1][] = $setV;
            } else {
                $orderArr[$setV['act']][0][] = $setV;
            }
        }
        $change = array();
        #將三維陣列改為一維陣列
        foreach ($orderArr as $order) {
            ksort($order);
            foreach ($order as $key => $orderK) {
                $oneBall = ($key == 1) ?: false;
                foreach ($orderK as $orderV) {
                    $orderV['oneBall'] = $oneBall;
                    $change[] = $orderV;
                }    
            }
        }
        $setData = $change;
    }
    #只選單顆球
    public function oneBall($bData, $act = null)
    {
        if (in_array($act, ['three', 4, 5, 6, 7, 8, 9, 10])) {
            if (count($bData) == 10)return false;
            return true;
        }
        $m = 0;
        if (!$bData) return false;
        foreach ($bData as $one) {
            if (!empty($one[1]) || !empty($one[2]) || !empty($one[3]))$m ++;
        }
        if ($m == 1) return true;
        return false;

    }
    #開頭字樣
    public function title($act, $bData)
    {
        if (!$bData) return array();
        switch ($act) {
            case 'hand':
                #塞入開頭資訊
                foreach ($this->ball as $ballList) {
                    $str = '';
                    if (isset($bData[$ballList])) {
                        foreach ($bData[$ballList] as $num) {
                            if (!empty($num))
                            $str .= $num.',';
                        }
                        $str = substr($str,0,-1);
                    }
                    $titleData[] = "第{$ballList}名：" . $str;
                }
                return $titleData;
            break;
            case 'goBall' :
            case 'move' :
                    foreach ($bData as $ballK => $ballCanter) {
                    $titleData[$ballK] = $ballK."號球:";
                    foreach ($ballCanter as $canter) {
                        if (!empty($canter))
                        $titleData[$ballK] .= $canter . ',';
                    }
                    $titleData[$ballK] = substr($titleData[$ballK],0,-1);
                }
                return $titleData;
            break;
            case 'three' :
            case 'pan' :
                return array();
            break;
            default:
                return array();
            break;
        }
    }
    #分析結果用
    #goBall 目前用於三碼 three
    public function analysis($act = 'hand', $setBall = null, $goBall = false, $redLetter = 0)
    {
        if ($redLetter != 0) {
            $this->changeRange = 0;
            $this->bitRange = 0;
        } else {
            $this->changeRange = 3;
            $this->bitRange = 2;
        }

        $this->red = ($redLetter == 2) ? true : false;
        $this->oneBallSel = $this->oneBall($setBall, $act);
        if (!is_array($this->data)) return false;
        switch ($act) {
            case 'hand':
                return $this->handAnal($setBall);
            break;
            case 'goBall' :
                return $this->goBallAnal($setBall, $goBall);
            break;
            case 'move' :
                return $this->moveAnal($setBall);
            break;
            case 'three' :
            case 1 :
            case 2 :
            case 3 :
            case 4 :
            case 5 :
            case 6 :
            case 7 :
            case 8 :
            case 9 :
            case 10 :
                return $this->threeAnal($setBall, $goBall, $act);
            break;
            case 'pan' :
                return $this->panAnal($setBall);
            break;
            default:
            break;
        }

    }
    private function panAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $beforBall = array();
        $ballInNo = array();
        $points = 0;
        #強制不是一球
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $period = substr($dV['period'], -3, 3);
            $bingo[$period] = 0;
            foreach ($ballInNo as $num) {
                if (!isset($beforBall["no{$num}"])) continue;
                if ($dV["no{$num}"] == $beforBall["no{$num}"]) {
                    $bingo[$period] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[$period] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $clear = ($this->red && $bingo[$period] == 1) ? $change : 0;
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : $clear;
                if ($bingo[$period] <= $this->bitRange && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
            }
            unset($beforBall);
            foreach($this->ball as $num) {
                if ($dV["no{$num}"] == $setBall[0]) {
                    $ballInNo[0] = ($num == 1) ? 10 : $num - 1;
                    $ballInNo[1] = $num;
                    $ballInNo[2] = ($num == 10) ? 1 : $num + 1;
                }
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
            'points' => $points,
        ];
        return $res;
    }
    private function threeAnal($setBall, $goBall = false, $act = 'three')
    {
        $bite = 0;
        $change = 0;
        $beforThree = 0;
        $ballInNo = 0;
        $points = 0;
        $number = ($act == 'three') ? 4 : $act + 1;
        $beforBall = array();
        foreach ($this->data as $dK => $dV) {
            $period = substr($dV['period'], -3, 3);
            $beforThree ++;
            if ($beforThree > 15) {
                $bingo[$period] = 0;
                #跟跑道
                if (!$goBall) {
                    foreach ($setBall as $num) {
                        if (in_array($dV["no{$num}"], $beforBall["no{$num}"])) {
                            $bingo[$period] ++;
                        }
                    }
                } else {
                #跟球跑
                    if (in_array($dV["no{$ballInNo}"], $beforBall["no{$ballInNo}"])) {
                        $bingo[$period] ++;
                    }
                }
                #連續藍字判斷
                if ($this->oneBallSel) {
                    $change = ($bingo[$period] == 0) ? $change + 1 : 0;
                } else {
                    $clear = ($this->red && $bingo[$period] == 1) ? $change : 0;
                    $change = ($bingo[$period] <= $this->changeRange) ? $change + 1 : $clear;
                    if ($bingo[$period] <= $this->bitRange) $bite ++;
                    if ($change == 0) $bite = 0;    
                    #藍字加總分數(期數加總分數)
                    $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
                }
            }

            foreach($this->ball as $num) {
                #跟球跑
                if ($goBall && $dV["no{$num}"] == $setBall[0]) {
                    $ballInNo = $num;
                }
                $beforBall["no{$num}"][] = $dV["no{$num}"];
                if (array_count_values($beforBall["no{$num}"])[$dV["no{$num}"]] == 2) {
                    $test = array_search($dV["no{$num}"], $beforBall["no{$num}"]);
                    unset($beforBall["no{$num}"][$test]);    
                }
                if (count($beforBall["no{$num}"]) == $number) $delete = array_shift($beforBall["no{$num}"]);
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
            'points' => $points,
        ];
        return $res;
    }

    private function handAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $points = 0;
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $period = substr($dV['period'], -3, 3);
            $bingo[$period] = 0;
            foreach ($this->ball as $num) {
                if (isset($setBall[$num]) && in_array($dV["no{$num}"], $setBall[$num])) {
                    $bingo[$period] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[$period] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $clear = ($this->red && $bingo[$period] == 1) ? $change : 0;
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : $clear;
                if ($bingo[$period] <= $this->bitRange && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
            'points' => $points,
        ];
        return $res;
    }

    private function goBallAnal($setBall, $goBall = null)
    {
        $bite = 0;
        $change = 0;
        $points = 0;
        $beforBall = array();
        $rank = $this->ball;
        if ($goBall == '正常(1-5名)') $rank = array_slice($rank, 0, 5, true);
        if ($goBall == '正常(6-10名)') $rank = array_slice($rank, 5, 5, true);
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $period = substr($dV['period'], -3, 3);
            $bingo[$period] = 0;
            foreach ($rank as $num) {
                if (!isset($beforBall["no{$num}"],$setBall[$beforBall["no{$num}"]])) continue;
                if ($dK != $frist && in_array($dV["no{$num}"], $setBall[$beforBall["no{$num}"]])) {
                    $bingo[$period] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[$period] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $clear = ($this->red && $bingo[$period] == 1) ? $change : 0;
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : $clear;
                if ($bingo[$period] <= $this->bitRange && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
            }
            unset($beforBall);
            foreach($rank as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
            'points' => $points,
        ];
        return $res;
    }

    private function moveAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $points = 0;
        $beforBall = array();
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $period = substr($dV['period'], -3, 3);
            $bingo[$period] = 0;
            foreach ($this->ball as $num) {
                $move = ($num == 10) ? 1 : $num + 1;
                if ($dK != $frist && in_array($dV["no{$move}"], $setBall[$beforBall["no{$num}"]])) {
                    $bingo[$period] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[$period] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $clear = ($this->red && $bingo[$period] == 1) ? $change : 0;
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : $clear;
                if ($bingo[$period] <= $this->bitRange && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
            }
            unset($beforBall);
            foreach($this->ball as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
            'points' => $points,
        ];
        return $res;
    }
    
}