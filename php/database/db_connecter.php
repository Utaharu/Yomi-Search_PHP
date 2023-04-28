<?php
/*
	Class - DB_Connecter
		# Yomi-Search PHP - Mysqli Call
*/

class DB_Connecter {
	private $host = '';
	private $user = '';
	private $password = '';
	private $name = '';
	private $prefix = '';
	private $charset = "";
  
	var $db_pre;
    protected $mysqli;
    protected $result;
    public $row = array();
    
    function __construct($Host,$User,$Password,$Database_Name,$Table_Prefix,$Charset){
		$this->charset = $Charset;
		$this->host = $Host;
		$this->user = $User;
		$this->password = $Password;
		$this->name = $Database_Name;
		$this->prefix = $Table_Prefix;
		$this->db_pre = $this->prefix;
		
        $this->Connect();
    }

    /**
     * Constructer
     * Opens a connection to a MySQL Server.
     * 
     * @return
     */
    function Connect() {
		$this->mysqli = mysqli_init();
        $this->mysqli->real_connect($this->host, $this->user, $this->password,$this->name);
		$this->mysqli->set_charset($this->charset);//Charset
        return TRUE;
    }

    // クエリ発行
    function query($sql) {
		$sql = preg_replace("/\{prefix\}/",$this->prefix,$sql);
        $this->result = $this->mysqli->query($sql) or $this->error('Query failed '.$sql);
        return $this->result;
    }

    // MySQL用に文字列をエスケープ
    function escape_string($str) {
		if(is_array($str)){
			foreach($str as $key=>$val){
				$str[$key] = $this->mysqli->real_escape_string($val);
			}
		}elseif($str){
			$str = $this->mysqli->real_escape_string($str);
		}
        return $str;
    }
	
    // $table内の総ログ数を求める
    function log_count($table) {
        $sql = "SELECT count(id) FROM {prefix}{$table}";
        $this->query($sql)  or $this->error('Query failed '.$sql);
        $tmp = $this->result->fetch_row();
        $this->result->free();
        return $tmp[0];
    }

	// 最後にinsertしたデータのidを求める
	function last_id() {
		return $this->mysqli->insert_id;
	}
	
	//num =//数字添字配列
	//assoc=//連想配列
	
	function fetch_num() {
		$this->row = $this->result->fetch_row();
		$this->result->free();
		return $this->row;
	}

	function fetch_assoc() {
		$this->row = $this->result->fetch_assoc();
		$this->result->free();
		return $this->row;
	}

	function single_num($sql) {
		$this->query($sql)  or $this->error('Query failed '.$sql);
		$this->row = $this->result->fetch_row();
		$this->result->free();
		return $this->row;
	}
	
	function single_assoc($sql) {
		$this->query($sql) or $this->error('Query failed '.$sql);
		$this->row = $this->result->fetch_assoc();
		$this->result->free();
		return $this->row;
	}
	
	//数字添字配列
	function rowset_num($sql) {
		$this->row = array();
		$this->query($sql) or $this->error('Query failed '.$sql);
		while ($line = $this->result->fetch_row()) {
			$this->row[] = $line;
		}
		$this->result->free();
		return $this->row;
	}
	
	//連想配列
	function rowset_assoc($sql) {
		$this->row = array();
		$this->query($sql) or $this->error('Query failed '.$sql);
		while ($line = $this->result->fetch_assoc()) {
			$this->row[] = $line;
		}
		$this->result->free();
		return $this->row;
	}

	function rowset_num_limit($sql,$offset,$rows) {
		$this->row = array();
		$this->query($sql.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$sql);
		while ($line = $this->result->fetch_row()) {
			$this->row[] = $line;
		}
		$this->result->free();
		return $this->row;
	}

	function rowset_assoc_limit($sql,$offset,$rows) {
		$this->row = array();
		$this->query($sql.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$sql);
		while ($line = $this->result->fetch_assoc()){
			$this->row[] = $line;
		}
		$this->result->free();
		return $this->row;
	}
	
	// エラーを表示
	function error($msg) {
		$ew = '';
		if( PHP_VERSION >= 4.3) {
			$e = debug_backtrace();
			foreach($e as $k=>$v) {
				foreach($v as $key=>$val) {
					if(is_string($val) || is_int($val)) {
						$ew .= '<strong>['.$key.']:</strong>'.$val."<hr />\n";
					} else if(is_array($val)) {
						$ew .= '['.$key.']:'.$val[0]."<hr />\n";
					}
				}
				$ew .= "<br />";
			}
		}
		if($this->mysqli->errno) echo "Error No.".$this->mysqli->errno."：".$this->mysqli->error."<br>\n<font color=red>".$msg.'<br><hr>'.$ew.'</font>'."\n";
	}
	
	/* 
	 ## データベース内のテーブルリスト取得 ##
	  * key(table_name) = value(table_name)
	   $table_list[$table_name] = $table_name;
	*/
	function list_tables() {
		$this->query('SHOW TABLES FROM '.$this->name);
		$i = 0;
        $table_list = array();
        while ($table_name = $this->result->fetch_row()) {
          $table_list[$table_name[0]] = $table_name[0];
        }
		return $table_list;
	}

    function close() {
        $this->mysqli->close();
    }	
}


?>