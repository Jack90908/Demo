<?php
class Model
{
    protected $item;
    protected $_isTransaction = false;
    private $_cols = '';
    private $_condition = '';
    private $_statement = array();
    private $_query = '';
    private $_order = '';
    private $_group = '';
    private $_lastInsertID = false;
    private $_lastData = array();
    private $_pdo = null;

    private $config = [
        'm' => [
            'host' => '192.168.0.100',
            'database' => 'roberdemo',
        ]
    ];

    private $sideDBInfo = [
        'c' => [
            'username' => 'rober',
            'password' => 'jack90908',
        ]
    ];

    private $db;
    private $showLastQuery;
    private $dbMapping;

    private $dbMappingString;

    /**
     * 取database名稱
     * @param string $db
     * @return string
     */
    static public function getDataBase($db = 'm')
    {
        $databaseName = getenv('APP_DB_' . strtoupper($db) . '_DATABASE');
        return $databaseName;
    }

    /**
     * 依照傳入字串, 建立相對應連線
     * e.g. cms => 控端::會員 -> slave
     *      ar  => 管端::報表 -> master
     * 實利化變數命名規則::
     * 管端會員類資料表 slave  $amsDB = SqlModel::newSqlModel('ams');
     * 控端比賽類資料表 Master $cgDB = SqlModel::newSqlModel('cg');
     * @param [string] $dbMapping
     */
    public function __construct($dbMapping = 'cm', $showLastQuery = false)
    {
        if (!$dbMapping) {
            return;
        }
        
        $dbMapping = (string) $dbMapping;
        $this->showLastQuery = $showLastQuery;
        $this->dbMappingString = $dbMapping;

        $dbMapping = (string) $dbMapping;
        $this->showLastQuery = $showLastQuery;
        $this->dbMappingString = $dbMapping;

        $sideInfo = $this->sideDBInfo[str_split($dbMapping, 1)[0]];
        $dbMapping = mb_substr($dbMapping, 1);
        $this->dbMapping = $dbMapping;
        $this->db = $this->combineDBInfo($dbMapping, $sideInfo);
    }

    public function __destruct()
    {
        $this->db = [];
        $this->_pdo = null;
    }

    public function getObject()
    {
        return $this->_lastData;
    }

    public function rowCount($sth)
    {
        if ($sth == null) {
            throw new Exception('please use function get() first');
        }

        return $sth->rowCount();
    }

    public function query($query, $value = array(), $getLastAddID = false)
    {
        if ($query == '' || substr_count($query, "?") != count($value)) {
            throw new Exception('query not match statement or empty');
        }
        $this->_lastInsertID = $getLastAddID;
        $this->_query = $query;
        $this->_statement = $value;
        if (in_array(strtoupper(substr($this->_query, 0, 1)), array('I', 'D', 'U'))) {
            $this->_lastInsertID = $getLastAddID;
            return $this->otherExec();
        }
        return $this->exec();
    }

    public function __call($method, $args)
    {
        if ($method == 'where') {
            return count($args) == 1 ?
                call_user_func_array(array($this, 'whereQuery'), $args) :
                call_user_func_array(array($this, 'whereKeyValue'), $args);
        }
        if ($method == 'set') {
            return call_user_func_array(array($this, 'set'), $args);
        }

        if ($method == "insert") {
            return call_user_func_array(array($this, 'add'), $args);
        }
        if ($method == 'delete') {
            return call_user_func_array(array($this, 'delete'), $args);
        }
        if ($method == 'order') {
            return call_user_func_array(array($this, 'order'), $args);
        }

        if ($method == 'group') {
            return call_user_func_array(array($this, 'groupby'), $args);
        }

        if ($method == 'whereIn') {
            return call_user_func_array(array($this, 'whereIn'), $args);
        }
    }

    /*
     *   $key    string
     *   $value  array() or string
     *   $option string  logic
     */
    protected function whereIn($key, $value, $option = "AND")
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        if ($this->_condition == '') {
            $this->_condition = "{$key} in ( " . $this->placeholders('?', sizeof($value)) . " )";
        } else {
            $this->_condition .= " {$option} {$key} in ( " . $this->placeholders('?', sizeof($value)) . " )";
        }

        $this->_statement = array_merge($this->_statement, array_values($value));

        return $this;
    }

    /*
     *   $key    string
     *   $value  string
     *   $option logic
     */
    private function whereKeyValue($key, $value, $option = "AND", $operand = "=")
    {
        $option = $option == '' ? 'AND' : $option;
        if (strtolower($operand) == "between") {
            if (is_array($value) && count($value) == 2) {
                if ($this->_condition == '') {
                    $this->_condition = "{$key} {$operand} ? and ?";
                } else {
                    $this->_condition .= " {$option} {$key} {$operand} ? and ?";
                }

                $this->_statement = array_merge($this->_statement, array_values($value));
            }
        } else {
            if ($this->_condition == '') {
                $this->_condition = "{$key} {$operand} ?";
            } else {
                $this->_condition .= " {$option} {$key} {$operand} ?";
            }

            $this->_statement = array_merge($this->_statement, array_values(array($value)));
        }

        return $this;
    }

    /*
     *   $condition  string
     */
    private function whereQuery($condition)
    {
        $this->_condition .= " {$condition}";
        return $this;
    }

    protected function order($col, $sort = "ASC")
    {
        if ($this->_order == '') {
            $this->_order = "ORDER BY {$col} {$sort}";
        } else {
            $this->_order = $this->_order . ", {$col} {$sort}";
        }

        return $this;
    }

    protected function groupby($col)
    {
        if ($this->_group === '') {
            $this->_group = "GROUP BY {$col}";
        } else if ($col != '') {
            $this->_group .= ", $col";
        }

        return $this;
    }

    /**
     * SELECT && pdo exec query.
     *
     * @param [sting] $table 欲操作資料表名稱
     * @param [type] $cols 欲操作欄位名稱
     * @param string $limit LIMIT 資料數
     * @return PDO prepare statement.
     */
    public function get($table, $cols = null, $limit = '')
    {
        $this->_cols = $cols ? (is_array($cols) ? implode(',', $cols) : ($cols == '' ? '*' : $cols)) : '*';
        $condition = $this->_condition == '' ? '' : "WHERE {$this->_condition}";
        $limit = is_array($limit) ? "LIMIT {$limit[0]}, {$limit[1]}" : $limit;
        $this->_query = "SELECT {$this->_cols} FROM {$table} {$condition} {$this->_group} {$this->_order} {$limit}";
        return $this->exec();
    }

    protected function getOnce($table, $cols = null)
    {
        $this->_cols = $cols ? (is_array($cols) ? implode(',', $cols) : $cols) : '*';
        $condition = $this->_condition == '' ? '' : "WHERE {$this->_condition}";
        $this->_query = "SELECT {$this->_cols} FROM {$table} {$condition} {$this->_group} {$this->_order} LIMIT 1";

        $this->exec();
    }

    /*
     *   $table  string
     *   $values array()
     */
    public function add($table, $values, $getLastAddID = false)
    {
        if (!$values || !is_array($values)) {
            return null;
        }

        if (is_array($values)) {
            $this->_lastInsertID = $getLastAddID;
            $first = current($values);
            $marks = array();
            $insertValues = array();
            $strlink = "";
            // 多筆
            if (is_array($first)) {
                $rows = $this->getColsName($first);
                $this->_query = "INSERT INTO {$table} ({$rows}) VALUES ";
                $sign = '(' . $this->placeholders('?', sizeof($values['0'])) . ')';
                foreach ($values as $key => $val) {
                    $strlink .= implode("#", array_values($val)) . "#"; //將所有要新增的報表值串成字串
                    $marks[] = $sign;
                }

                $insertValues = explode("#", substr($strlink, 0, -1));
                $this->_query .= implode(',', $marks);
                $this->_statement = $insertValues;
            } // 單筆
            else {
                $rows = $this->getColsName($values);
                $value = $this->placeholders('?', count($values));
                $this->_query = "INSERT INTO {$table} ({$rows}) VALUES ({$value})";
                $this->_statement = array_merge($insertValues, array_values($values));
            }
        }

        return $this->otherExec();
    }

    /*
     *
     */
    public function insertUpdate($table, $values, $update = "")
    {

        if (!$values || !is_array($values)) {
            return null;
        }

        if (is_array($values)) {
            $first = current($values);
            // 多筆
            if (is_array($first)) {
                $rows = $this->getColsName($first);
                foreach ($values as $key => $val) {
                    $strlink = "'" . implode("', '", array_values($val)) . "'"; //將所有要新增的報表值串成字串
                    if ($update == "") {
                        $update = " Game_result = '{$val['Game_result']}', Result_amount = '{$val['Result_amount']}'";
                    }
                    $this->_query .= " INSERT INTO {$table} ({$rows}) VALUES ({$strlink}) ON DUPLICATE KEY UPDATE{$update};";
                }
            } // 單筆
            else {
                $rows = $this->getColsName($values);
                if ($update == "") {
                    $update = " Game_result = '{$values['Game_result']}', Result_amount = '{$values['Result_amount']}'";
                }
                $strlink = "'" . implode("', '", array_values($values)) . "'"; //將所有要新增的報表值串成字串
                $this->_query = "INSERT INTO {$table} ({$rows}) VALUES ({$strlink}) ON DUPLICATE KEY UPDATE{$update};";
            }
        }

        return $this->otherExec();
    }

    /*
     *   $data   array
     */
    protected function set($table, $data)
    {
        // merge key value
        $keys = array();
        $values = array();
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $keys[] = "$key = ?";
                $values[] = $val;
            }
            $keys = implode(',', $keys);
        } else {
            // 用於原先set occupy1 = IF(occupy1 > '0.985', '0.985', occupy1)的寫法
            $keys = $data;
        }
        $condition = $this->_condition == '' ? '' : "WHERE {$this->_condition}";

        $this->_query = "UPDATE {$table} set {$keys} {$condition}";
        $tmp = $this->_statement;
        $this->_statement = array();
        $this->_statement = array_merge($this->_statement, array_values($values));
        $this->_statement = array_merge($this->_statement, array_values($tmp));

        unset($tmp);

        return $this->otherExec();
    }

    /*
     *   Delete rows From $table
     *   ***Need where condition***
     */
    protected function delete($table)
    {
        if ($this->_condition == '') {
            throw new Exception('Delete command without condition.');
        }

        $this->_query = "DELETE FROM {$table} WHERE {$this->_condition}";

        return $this->otherExec();
    }

    private function exec()
    {
        try{

            if ($this->_isTransaction == false && $this->_pdo == null) {
                $this->_pdo = $this->getPDOObject();
            }
            $sth = $this->_pdo->prepare($this->_query);
            $sth->execute($this->_statement);

            $err = $sth->errorCode();

            if ($this->_isTransaction == true) {
                if ($err != '00000') {
                    $this->_pdo->rollBack();
                    $this->_pdo = null;
                }
            } else {
                $this->_pdo = null;
            }

            $this->_lastData = array(
                'Query' => $this->_query,
                'Statement' => $this->_statement,
                'queryString' => $this->combine_query_statement($this->_query, $this->_statement)
            );

            if ($err != '00000') {
                $errorMsg = 'SQL Error:'.$this->dbMapping." ({$err})". ' SQL:'.$this->_query;
                $this->_errorLog($errorMsg);
            }

            if ($this->showLastQuery) {
                echo '<pre>';
                print_r($this->_lastData);
                echo '</pre>';
            }

            $this->reset();
            return $sth;
            // return $result;

        }catch(Exception $e){
            //紀錄錯誤到機器上
            $errorMsg = 'DB error:'.$this->dbMapping. ' SQL:'.
                $this->combine_query_statement($this->_query, $this->_statement);
            $this->_errorLog($errorMsg);
            exit(254);
        }
    }

    private function otherExec()
    {
        try {
            if ($this->_isTransaction == false && $this->_pdo == null) {
                $this->_pdo = $this->getPDOObject();
            }
            $sth = $this->_pdo->prepare($this->_query);
            $sth->execute($this->_statement);
            $err = $sth->errorCode();
            $errInfo = $sth->errorInfo();
            $lastInsertId = ($this->_lastInsertID) ? $this->_pdo->lastInsertId() : '';

            if ($this->_isTransaction == true) {
                if ($err != '00000') {
                    $this->_isTransaction = false;
                    $this->_pdo->rollBack();
                    //$this->_pdo = null;
                }
            } else {
                $lastInsertId = ($this->_lastInsertID) ? $this->_pdo->lastInsertId() : '';
                //$this->_pdo = null;
            }

            $this->_lastData = array(
                'Query' => $this->_query,
                'Statement' => $this->_statement,
            );

            if ($this->showLastQuery) {
                echo '<pre>';
                print_r($this->_lastData);
                echo '</pre>';
            }

            if ($err != '00000') {
                $errorMsg = 'SQL Error:'.$this->dbMapping." ({$err})". ' SQL:'.$this->_query;
                $this->_errorLog($errorMsg);
            }

            $resultArray = [
                'error'        => $err,
                'query'        => $this->_query,
                'statement'    => $this->_statement,
                'info'         => $errInfo,
                'queryString'  => $this->combine_query_statement($this->_query, $this->_statement),
                'lastInsertID' => $lastInsertId,
                'rowCount'     => $sth->rowCount()
            ];
            $this->reset();
            return json_encode($resultArray);
        }catch(Exception $e){
            //紀錄錯誤到機器上
            $errorMsg = 'DB error:'.$this->dbMapping. ' SQL:'.
                $this->combine_query_statement($this->_query, $this->_statement);
            $this->_errorLog($errorMsg);
            exit(254);
        }
    }

    public function beginTransaction()
    {
        $this->_pdo = $this->getPDOObject();
        $this->_isTransaction = true;
        $this->_pdo->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->_isTransaction = false;
        $this->_pdo->commit();
        $this->_pdo = null;
    }

    public function rollback()
    {
        if ($this->_isTransaction == true) {
            $this->_isTransaction = false;
            $this->_pdo->rollBack();
            $this->_pdo = null;
        }
    }

    public function fetch($sth, $fetchStyle = PDO::FETCH_ASSOC)
    {
        if ($sth == null) {
            throw new Exception('please use function get() first');
        }

        return $sth->fetch($fetchStyle);
    }

    public function fetchAll($sth, $fetchStyle = PDO::FETCH_ASSOC)
    {
        if ($sth == null) {
            throw new Exception('please use function get() first');
        }

        return $sth->fetchAll($fetchStyle);
    }

    public function fetchObject($sth)
    {
        if ($sth == null) {
            throw new Exception('please use function get() first');
        }

        return $sth->fetchObject();
    }

    public function fetchAllObject($sth, $fetchStyle = PDO::FETCH_OBJ)
    {
        if ($sth == null) {
            throw new Exception('please use function get() first');
        }

        return $sth->fetchAll($fetchStyle);
    }

    /*  -----   query function  -----   */

    public function now()
    {
        return date("Y-m-d H:i:s");
    }

    public function curtime()
    {
        return date("H:i:s");
    }

    private function getColsName($cols)
    {
        if (!is_array($cols)) {
            return null;
        }

        return implode(',', array_keys($cols));
    }

    private function placeholders($char, $count = 0, $separator = ',')
    {
        $result = array();
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $result[] = $char;
            }
        }

        return implode($separator, $result);
    }

    private function reset()
    {
        $this->_cols = '';
        $this->_condition = '';
        $this->_statement = [];
        $this->_query = '';
        $this->_order = '';
        $this->_group = '';
    }

    /*  -----   pdo object function -----   */

    private function getPDOObject()
    {
        try {
            return new PDO($this->getConnectStr(), $this->db['username'], $this->db['password']);
        } catch (PDOException $e) {
            var_dump($e);
            echo '連線錯誤，請稍後再試';
            $errorMsg = 'DB error:'.$this->dbMapping. ' 連線錯誤.';
            $this->_errorLog($errorMsg);
            exit;
        }
    }

    private function getConnectStr()
    {
        return "mysql:host={$this->db['host']};dbname={$this->db['database']};charset=utf8";
    }

    /*  -----   combine query and statement -----   */
    private function combine_query_statement($query, $statement)
    {
        $combine_string = "";
        if (substr_count($query, "?") == count($statement)) {
            foreach ($statement as $k => $v) {
                $query = preg_replace("/\?/", '"' . $v . '"', $query, 1);
            }
            $combine_string = $query;
        }
        return $combine_string;
    }

    /**
     * 依據端口 將帳密分配設定陣列
     */
    private function combineDBInfo($mappingConfig, $dbInfo)
    {
        $configAry = $this->config[$mappingConfig];
        $configAry += [
            'username' => $dbInfo['username'],
            'password' => $dbInfo['password'],
        ];
        return $configAry;
    }


    /**
     * 紀錄錯誤到機器上
     * @param $errorMsg
     */
    private function _errorLog($errorMsg)
    {
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, $errorMsg);
        fclose($stderr);
    }
}
