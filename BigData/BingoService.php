<?php
class BingoService {

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
    private $maxChange = 0;
    private $maxPoints = 0;
    public function __construct($data)
    {
        if (!empty($data)) $this->data = $data;
    }

    #只選單顆球
    public function oneBall($bData, $act)
    {
        if (in_array($act, ['three', 4, 5, 6, 7, 8, 9, 10])) {
            if (count($bData['ball']) == 10) {
                return false;
            }
            return true;
        }
        $m = 0;
        $setRank = 0;
        foreach ($bData as $one) {
            for($i = 1; $i <= 10; $i++) {
                if (!empty($one[$i])) $m ++;
            }
            if ($m > 0) $setRank ++;
            $m = 0;
        }

        if ($setRank == 1) return true;
        return false;

    }

    #分析結果用
    #goBall 目前用於三碼 three
    public function analysis($act, $setRes)
    {
        $this->oneBallSel = $this->oneBall($setRes, $act);
        switch ($act) {
            case 'hand':
                $this->handAnal($setRes);
            break;
            case 'goBall' :
                $this->goBallAnal($setRes);
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
                $this->threeAnal($setRes['ball'], $setRes['goBall'], $act);
            break;
            default:
            break;
        }
        if ($this->oneBallSel) {
            return ['maxChange' => $this->maxChange, 'maxPoints' => 0, 'oneBall' => $this->oneBallSel];
        }
        return ['maxChange' => $this->maxChange, 'maxPoints' => $this->maxPoints, 'oneBall' => $this->oneBallSel];

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
                    $this->maxChange($change);
                } else {
                    $change = ($bingo[$period] <= $this->changeRange) ? $change + 1 : 0;
                    if ($bingo[$period] <= $this->bitRange) $bite ++;
                    if ($change == 0) $bite = 0;
                    #藍字加總分數(期數加總分數)
                    $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
                    $this->maxChange($change, $points);
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
                $this->maxChange($change);
            } else {
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : 0;
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
                $this->maxChange($change, $points);
            }
        }
    }

    private function goBallAnal($setBall)
    {
        $bite = 0;
        $change = 0;
        $points = 0;
        $beforBall = array();
        $rank = $this->ball;
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
                $this->maxChange($change);
            } else {
                $change = ($bingo[$period] <= $this->changeRange && $dK != $frist) ? $change + 1 : 0;
                #藍字加總分數(期數加總分數)
                $points = ($change > 0) ? $points + $bingo[$period] - 3 : 0;
                $this->maxChange($change, $points);
            }
            unset($beforBall);
            foreach($rank as $num) {
                $beforBall["no{$num}"] = $dV["no{$num}"];
            }
        }
    }

    private function maxChange($change, $points = null) {
        if ($this->maxChange < $change) {
            $this->maxChange = $change;
        }
        if (!is_null($points) && $this->maxPoints < abs($points)) {
            $this->maxPoints = abs($points);
        }
    }
}