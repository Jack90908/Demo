<?php
require_once "Model.php";
#資料庫語法
if ($_POST) {
    new BeijingCarInWord('', $_POST);
}
// CREATE TABLE `fast_car_word` LIKE `fast_car`
class BeijingCarInWord {
    private $url = 'http://www.1988660.com/Api/pks/getPksHistoryList?lotCode=10001';
    private $dbName = 'beijing_car';
    public $tableKey = '{"message":"';
    private $yestoday = false;
    public $dataKeyLen = '';
    private $fixOpen = false; #不修正資料則判斷最大期數
    private $_db = '';//資料庫連線
    public $dataKey = '<tdstyle="width:164px">';
    private $dateSe = [
        'all' => [0, 17],
        'date' => [0, 8],
        'time' => [-8, 8],
        'period' => [9, 3]
    ];

    public function __construct($getData = '', $post = array())
    {
        $this->_db = new Model('cm');
        if($getData != '') {
            $this->url = $getData;
            $this->yestoday = true;
        }
        #抓最大ID
        $getMaxID = $this->_db->get($this->dbName, 'max(id)');
        list($this->maxID) = $this->_db->fetch($getMaxID, PDO::FETCH_NUM);
        #手動更新如果資料已有則不抓資料
        if ($post) $this->getNowId(key($post));
        $this->data = $this->curl_get($this->url);
        $this->fastCarSOP();
    }
    private function getNowId($id) 
    {
        if ($id != $this->maxID) {
            echo json_encode('success');
            exit;
        } else {
            return;
        }
    }
    #用curl 來抓取路徑上的網頁資料
    function curl_get($url)
    {
        $header = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
            'Accept-Encoding: gzip, deflate, br',
            'Accept-Language: zh-TW,zh;q=0.9,zh-CN;q=0.8',
            'Connection: keep-alive',
            'Cookie: ccsalt=80e74d1bdf27ff0396bcead4df2d2a4a; UM_distinctid=171935360ec4a2-022c3cb45309f4-396f7f07-13c680-171935360ed405; _ga=GA1.2.2136753933.1587314647; _gid=GA1.2.1380147242.1587314647; CNZZDATA5418000=cnzz_eid%3D722683215-1587310994-%26ntime%3D1587313753; Hm_lvt_dad24abebba647625189f407f7103e48=1587314648,1587318123; Hm_lpvt_dad24abebba647625189f407f7103e48=1587318170',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: same-origin',
            'Upgrade-Insecure-Requests: 1',
            'Cache-Control: private',
            'Content-Encoding: gzip',
            'Content-Length: 14673',
            'Content-Type: text/html; charset=utf-8',
            'Date: Sun, 19 Apr 2020 17:58:47 GMT',
            'Server: nginx',
            'Vary: Accept-Encoding',
            'X-AspNet-Version: 4.0.30319',
            'X-AspNetMvc-Version: 5.0',
            'X-Cache: EXPIRED',
            'X-Powered-By: ASP.NET',
    
    
        ];
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HEADER,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result=curl_exec($ch);
        $code=curl_getinfo($ch);
        
        if($code!='404' && $result){
            return $result;
        }
        curl_close($ch);
    }
    #用key來移除這張table不要的參數
    private function getTable ()
    {
        #抓到至table的字數
        $tableLen = strpos($this->data, $this->tableKey);
        #移除前面不要的
        $this->data = substr($this->data, $tableLen);
    }
    private function BeijingCarFix($data = false)
    {
        if (!$data) return;
        $timeR = str_replace('-', "", $data['preDrawTime']);
        $date = substr($timeR, $this->dateSe['date'][0], $this->dateSe['date'][1]);
        $time = substr($timeR, $this->dateSe['time'][0], $this->dateSe['time'][1]);
        $lastPeriod = $data['preDrawIssue'] - 1;
        $date = $date - 1;
        $getLast = $this->_db->where('id', $date . $lastPeriod)->get($this->dbName);
        if ($time < '11:30:00' && !$this->_db->fetch($getLast)) {
            $getHisDay = date('Y-m-d', strtotime("-1 day"));
            $url = "http://52.193.14.86/Api/pks/getPksHistoryList?date=" . $getHisDay ."&lotCode=10001";
            new self($url);
        }
    }
    #極速快車api
    private function fastCarSOP()
    {
        $this->getTable();
        $this->data = json_decode($this->data, true);
        #修正北京賽車
        if (!$this->yestoday) $this->BeijingCarFix(end($this->data['result']['data']));
        if (isset($this->data['result'])) {
            foreach ($this->data['result']['data'] as $resB) {
                $timeR = str_replace('-', "", $resB['preDrawTime']);
                $date = substr($timeR, $this->dateSe['date'][0], $this->dateSe['date'][1]);
                $time = substr($timeR, $this->dateSe['time'][0], $this->dateSe['time'][1]);
                $period = $resB['preDrawIssue'];    
                $dbID = $date . $period;
                #重複的不新增，用資料庫最大值去判斷
                if ($dbID <= $this->maxID && !$this->fixOpen && !$this->yestoday) break;
                $ballArray = explode(',', $resB['preDrawCode']);
                $ball = array();
                foreach ($ballArray as $key => $item) {
                    if ($key > 10) break;
                    $ball[] = $item;
                }
                $inserData = [
                    'id' => $dbID,
                    'date' => $date,
                    'time' => $time,
                    'period' => $period,
                    'no1' => $ball[0],
                    'no2' => $ball[1],
                    'no3' => $ball[2],
                    'no4' => $ball[3],
                    'no5' => $ball[4],
                    'no6' => $ball[5],
                    'no7' => $ball[6],
                    'no8' => $ball[7],
                    'no9' => $ball[8],
                    'no10' => $ball[9]
                ];
                $res = $this->_db->add($this->dbName, $inserData);
            }
        }
        
        if (!$this->yestoday) echo json_encode('success');
    }
}