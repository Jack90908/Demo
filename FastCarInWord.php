<?php
require_once "Model.php";
#資料庫語法
if ($_POST) {
    new FastCarInWord('', $_POST);
}
// CREATE TABLE `fast_car_word` LIKE `fast_car`
class FastCarInWord {
    private $url = 'http://52.193.14.86/Api/pks/getPksHistoryList?lotCode=10037';
    private $dbName = 'fast_car_word';
    public $tableKey = '{"message":"';

    public $dataKeyLen = '';
    private $fixOpen = false; #不修正資料則判斷最大期數
    private $_db = '';//資料庫連線
    public $dataKey = '<tdstyle="width:164px">';
    private $dataNum = [4, 2];
    private $dateSe = [
        'all' => [0, 17],
        'date' => [0, 8],
        'time' => [-8, 8],
        'period' => [9, 3]
    ];

    public function __construct($getData = '', $post = array())
    {
        $this->_db = new Model('cm');
        #抓最大ID
        $getMaxID = $this->_db->get($this->dbName, 'max(id)');
        list($this->maxID) = $this->_db->fetch($getMaxID, PDO::FETCH_NUM);
        #判斷抓的站台
        // $this->getUrl($getData);
        #手動更新如果資料已有則不抓資料
        if ($post) $this->getNowId(key($post));
        $this->data = $this->curl_get($this->url);
        $this->fastCarSOP();
    }

    private function getUrl($getData)
    {
        $getUrl = $this->_db->where('status', 'Y')
                            ->get('getUrl', ['url_id']);
        $fetchUrl = $this->_db->fetch($getUrl);
        if ($fetchUrl) {
            switch ($fetchUrl['url_id']) {
                case 1 :
                    $this->tableKey = 'kj52_lotteryTable';
                    $this->dataKey = '<tdstyle="width:164px">';
                    $this->url = 'https://www.9696ty.com/96/xyft/xyft_get/numberdistribution.php';
                    $this->fixDataUrl = 'https://www.9696ty.com/96/xyft/xyft_get/number.php?date=';
                    $this->dataNum = [4, 2];
                    $this->dateSe = [
                        'all' => [0, 17],
                        'date' => [0, 8],
                        'time' => [-5, 5],
                        'period' => [9, 3]
                    ];
                break;
                case 2 :
                    $this->url = 'https://www.9111kjw.com/draw-xyft-today.html';
                    $this->tableKey = '<div class="table h45 history-table" data-type="xyft">';
                    $this->dataKey = '<tdclass="time"><span>';
                    $this->dataNum = [3, 2];
                    $this->dateSe = [
                        'all' => [0, 23],
                        'date' => [0, 8],
                        'time' => [18, 5],
                        'period' => [8, 3]
                    ];
                break;
            }
        }
        // if ($getData) {
        //     $this->url = $this->fixDataUrl . $getData;
        //     $this->fixOpen = true;
        // }        
    }
    #關於整體SOP流程放置這
    private function dataSOP()
    {
        $this->getTable();
        $this->getData();
        $this->insertDB();
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
    #用每個表格的key來判斷抓取範圍
    private function getData()
    {
        $replace = [
            "&nbsp", ";", " ","\r", "\n", "\r\n", "\n\r"
        ];
        $this->data = str_replace($replace, "", $this->data);
        $this->data = explode($this->dataKey ,$this->data);
        unset($this->data[0]);
    }
    #存入資料庫
    private function insertDB()
    {
        foreach ($this->data as $oneGame) {

            $datePeriodTime = substr($oneGame, $this->dateSe['all'][0], $this->dateSe['all'][1]);    
            #日期、時間、期數
            $date = substr($datePeriodTime, $this->dateSe['date'][0], $this->dateSe['date'][1]);
            $time = substr($datePeriodTime, $this->dateSe['time'][0], $this->dateSe['time'][1]);
            $period = substr($datePeriodTime, $this->dateSe['period'][0], $this->dateSe['period'][1]);    
            $dbID = $date . $period;
            #重複的不新增，用資料庫最大值去判斷
            if ($dbID < $this->maxID && !$this->fixOpen) continue;
            $ball = array();
            $gameData = explode('spanclass' ,$oneGame);
            unset($gameData[0]);
            foreach ($gameData as $key => $item) {
                if ($key > 10) break;
                $ballStr = substr($item, $this->dataNum[0], $this->dataNum[1]);
                $ball[] = str_replace(["'", '"'], '', $ballStr);
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
            $res = $this->_db->add('game', $inserData);

        }
        if (date('Hs') > 2200 && date('Hs') < 2205) {
            $deleteDate = date('Ymd') -2;
            $this->_db->where("id LIKE $deleteDate%")
                    ->delete($this->dbName);
        }
        echo json_encode('success');
    }
    #極速快車api
    private function fastCarSOP()
    {
        $this->getTable();
        $this->data = json_decode($this->data, true);
        if (isset($this->data['result'])) {
            foreach ($this->data['result']['data'] as $resB) {
                $timeR = str_replace('-', "", $resB['preDrawTime']);
                $date = substr($timeR, $this->dateSe['date'][0], $this->dateSe['date'][1]);
                $time = substr($timeR, $this->dateSe['time'][0], $this->dateSe['time'][1]);
                $period = $resB['preDrawIssue'];    
                $dbID = $date . $period;
                #重複的不新增，用資料庫最大值去判斷
                if ($dbID < $this->maxID && !$this->fixOpen) break;
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
        
        echo json_encode('success');
    }
    private function faker()
    {
        $this->data = "HTTP/1.1 200 OK
                Date: Sun, 19 Apr 2020 18:05:12 GMT
                Content-Type: text/html; charset=UTF-8
                Transfer-Encoding: chunked
                Connection: keep-alive
                Set-Cookie: __cfduid=d8df2870a83bb6f1ce9a4b2499349c7901587319512; expires=Tue, 19-May-20 18:05:12 GMT; path=/; domain=.9696ty.com; HttpOnly; SameSite=Lax
                Vary: Accept-Encoding
                X-Powered-By: PHP/5.6.40
                CF-Cache-Status: DYNAMIC
                Expect-CT: max-age=604800, report-uri='https://report-uri.cloudflare.com/cdn-cgi/beacon/expect-ct'
                Server: cloudflare
                CF-RAY: 58688d669ee3fbd8-KIX
                cf-request-id: 023536b41a0000fbd8a02d1200000001


            <div  class='kj52_lotteryTable'>
                <div id='history-table' class='kj52_lotteryTable'>
                    <table id='history'>
                        <tr class='LT-tr'>
                            <th style='width: 164px;'>期號及時間</th>
                            <th style='width: 373px;'>開獎號碼</th>
                            <th colspan='3'>冠亞軍和</th>
                            <th colspan='5'>1~5龍虎</th>
                        </tr>

                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-156  03:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    7                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-155  02:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-154  02:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-153  02:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-152  02:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-151  02:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    4                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-150  02:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-149  02:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-148  02:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-147  02:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    14                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-146  02:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-145  02:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    14                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-144  02:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-143  01:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-142  01:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-141  01:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-140  01:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-139  01:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-138  01:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-137  01:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-136  01:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-135  01:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    7                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-134  01:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-133  01:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-132  01:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no9'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    14                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-131  00:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    7                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-130  00:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-129  00:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    4                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-128  00:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    5                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-127  00:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-126  00:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    4                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-125  00:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-124  00:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no3'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-123  00:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-122  00:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-121  00:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    7                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-120  00:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-119  23:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    18                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-118  23:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-117  23:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-116  23:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-115  23:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-114  23:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-113  23:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-112  23:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no4'></span>                
                                            <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-111  23:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-110  23:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-109  23:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-108  23:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-107  22:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-106  22:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-105  22:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-104  22:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-103  22:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-102  22:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-101  22:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-100  22:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    17                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-099  22:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-098  22:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-097  22:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-096  22:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-095  21:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-094  21:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-093  21:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    19                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-092  21:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    5                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-091  21:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-090  21:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    10                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-089  21:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-088  21:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-087  21:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-086  21:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    15                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-085  21:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-084  21:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    19                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-083  20:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-082  20:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    7                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-081  20:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    3                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-080  20:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-079  20:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no3'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-078  20:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-077  20:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-076  20:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-075  20:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no6'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-074  20:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-073  20:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no9'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    19                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-072  20:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no2'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    5                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-071  19:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no7'></span>                
                                            <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    17                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-070  19:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-069  19:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no4'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-068  19:44                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no5'></span>                
                                            <span class='no9'></span>                
                                            <span class='no3'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-067  19:39                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no7'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no6'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    6                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-066  19:34                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no5'></span>                
                                            <span class='no1'></span>                
                                            <span class='no8'></span>                
                                            <span class='no2'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no7'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-065  19:29                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no5'></span>                
                                            <span class='no7'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>                
                                            <span class='no8'></span>                
                                            <span class='no6'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-064  19:24                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-063  19:19                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no3'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no9'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    8                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-062  19:14                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>                
                                            <span class='no3'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no4'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    13                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-061  19:09                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no8'></span>                
                                            <span class='no9'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no3'></span>                
                                            <span class='no7'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>                
                                            <span class='no6'></span>                
                                            <span class='no2'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    17                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-060  19:04                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no1'></span>                
                                            <span class='no4'></span>                
                                            <span class='no10'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    16                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        虎                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-059  18:59                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no2'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>                
                                            <span class='no9'></span>                
                                            <span class='no6'></span>                
                                            <span class='no8'></span>                
                                            <span class='no4'></span>                
                                            <span class='no1'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    12                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>大</font>                    </td>
                                <td style='width: 60px'>
                                    <font color='#0000FF'>雙</font>                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>

                            </tr>
                                                            <tr class='even'>

                                                <td style='width: 164px'>
                                    20200419-058  18:54                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no6'></span>                
                                            <span class='no3'></span>                
                                            <span class='no1'></span>                
                                            <span class='no2'></span>                
                                            <span class='no8'></span>                
                                            <span class='no10'></span>                
                                            <span class='no4'></span>                
                                            <span class='no7'></span>                
                                            <span class='no9'></span>                
                                            <span class='no5'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    9                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                                            <tr class='odd'>

                                                <td style='width: 164px'>
                                    20200419-057  18:49                    </td>
                                <td style='width: 373px'>
                                    <div class='num_pk10'>
                                                                    <span class='no10'></span>                
                                            <span class='no1'></span>                
                                            <span class='no9'></span>                
                                            <span class='no4'></span>                
                                            <span class='no2'></span>                
                                            <span class='no6'></span>                
                                            <span class='no5'></span>                
                                            <span class='no8'></span>                
                                            <span class='no7'></span>                
                                            <span class='no3'></span>   
                                    </div>
                                </td>
                                <td style='width: 60px'>
                                    11                    </td>
                                <td style='width: 60px'>
                                    小                    </td>
                                <td style='width: 60px'>
                                    單                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>                        </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        <font color='red'>龍</font>
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>
                                    <td style='width: 60px' class=''>
                                        虎
                                    </td>

                            </tr>
                                    </table>
                </div>

            </div>";
    }
}