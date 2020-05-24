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
    
    public function __construct($data)
    {
        if (!empty($data)) $this->data = $data;
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
        if ($act == 'three') {
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
                    $titleData[$ballK] = $ballK."號球：";
                    foreach ($ballCanter as $canter) {
                        $titleData[$ballK] .= $canter . ',';
                    }
                    $titleData[$ballK] = substr($titleData[$ballK],0,-1);
                }
                return $titleData;
            break;
            case 'three' :
                return array();
            break;
            default:
            break;
        }
    }
    #分析結果用
    public function analysis($act = 'hand', $setBall = null)
    {
        $this->oneBallSel = $this->oneBall($setBall, $act);
        if (!is_array($this->data)) return false;
        switch ($act) {
            case 'hand':
                return $this->handAnal($setBall);
            break;
            case 'goBall' :
                return $this->goBallAnal($setBall);
            break;
            case 'move' :
                return $this->moveAnal($setBall);
            break;
            case 'three' :
                return $this->threeAnal($setBall);
            break;
            default:
            break;
        }

    }

    private function threeAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $beforThree = 0;
        $beforBall = array();
        foreach ($this->data as $dK => $dV) {
            $beforThree ++;
            if ($beforThree > 15) {
                $bingo[substr($dV['period'], -3, 3)] = 0;
                foreach ($setBall as $num) {
                    if (in_array($dV["no{$num}"], $beforBall["no{$num}"])) {
                        $bingo[substr($dV['period'], -3, 3)] ++;
                    }
                }
                #連續藍字判斷
                if ($this->oneBallSel) {
                    $change = ($bingo[substr($dV['period'], -3, 3)] == 0) ? $change + 1 : 0;
                } else {
                    $change = ($bingo[substr($dV['period'], -3, 3)] <= 3) ? $change + 1 : 0;
                    if ($bingo[substr($dV['period'], -3, 3)] <= 2) $bite ++;
                    if ($change == 0) $bite = 0;    
                }
            }

            foreach($this->ball as $num) {
                $beforBall["no{$num}"][] = $dV["no{$num}"];
                
                if (array_count_values($beforBall["no{$num}"])[$dV["no{$num}"]] == 2) {
                    $test = array_search($dV["no{$num}"], $beforBall["no{$num}"]);
                    unset($beforBall["no{$num}"][$test]);    
                }
                if (count($beforBall["no{$num}"]) == 4) $delete = array_shift($beforBall["no{$num}"]);
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
        ];
        return $res;
    }

    private function handAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($this->ball as $num) {
                if (isset($setBall[$num]) && in_array($dV["no{$num}"], $setBall[$num])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[substr($dV['period'], -3, 3)] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
                if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
            }
        }
        $res = [
            'bingo' => $bingo,
            'change' => $change,
            'bite' => $bite,
        ];
        return $res;
    }

    private function goBallAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $beforBall = array();
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($this->ball as $num) {
                if (!isset($beforBall["no{$num}"],$setBall[$beforBall["no{$num}"]])) continue;
                if ($dK != $frist && in_array($dV["no{$num}"], $setBall[$beforBall["no{$num}"]])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[substr($dV['period'], -3, 3)] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
                if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
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
        ];
        return $res;
    }

    private function moveAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $beforBall = array();
        foreach ($this->data as $dK => $dV) {
            $frist = (!isset($frist)) ? $dK : $frist;
            $bingo[substr($dV['period'], -3, 3)] = 0;
            foreach ($this->ball as $num) {
                $move = ($num == 10) ? 1 : $num + 1;
                if ($dK != $frist && in_array($dV["no{$move}"], $setBall[$beforBall["no{$num}"]])) {
                    $bingo[substr($dV['period'], -3, 3)] ++;
                }
            }
            #連續藍字判斷
            if ($this->oneBallSel) {
                $change = ($bingo[substr($dV['period'], -3, 3)] == 0 && $dK != $frist) ? $change + 1 : 0;
            } else {
                $change = ($bingo[substr($dV['period'], -3, 3)] <= 3 && $dK != $frist) ? $change + 1 : 0;
                if ($bingo[substr($dV['period'], -3, 3)] <= 2 && $dK != $frist) $bite ++;
                if ($change == 0) $bite = 0;    
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
        ];
        return $res;
    }
    
}