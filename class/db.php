<?php
/**
 * 20160902 mysql to mysqli use !
**/
class db {

    // <データベース設定> utf8_general_ciで作成
    var $db_host = 'localhost';  // MySQLのホスト名
    var $db_user = 'root';       // MySQLのログインに使用するユーザ名
    var $db_pass = '';           // MySQLのログインに使用するパスワード
    var $db_name = 'yomiyomi';   // MySQLのデータベース名
    var $db_pre  = 'zzz_';       // テーブルの先頭に付加するプレフィックス
    // </データベース設定>

    var $db_link;
    var $result;
    var $row = array();
    
    function __construct() {
        $this->db();
    }

    /**
     * Constructer
     * Opens a connection to a MySQL Server.
     * 
     * @return
     */
    function db() {
        $this->db_link = mysqli_connect($this->db_host, $this->db_user, $this->db_pass);
        mysqli_select_db($this->db_link, $this->db_name);
        return TRUE;
    }

    // クエリ発行
    function query($query) {
        $this->result = mysqli_query($this->db_link, $query) or $this->error('Query failed '.$query);
        return $this->result;
    }

    // MySQL用に文字列をエスケープ
    function escape_string($str) {
        return @mysqli_real_escape_string($this->db_link, $str);
    }
	
    // $table内の総ログ数を求める
    function log_count($table) {
        $query = 'SELECT count(id) FROM ' . $table;
        $result = self::query($query)  or $this->error('Query failed '.$query);
        $tmp = mysqli_fetch_row($result);
        mysqli_free_result($result);
        return $tmp[0];
    }

	// 最後にinsertしたデータのidを求める
	function last_id() {
		return @mysqli_insert_id($this->db_link);
	}

	function fetch_num($result_id = 0) {
		if (!$result_id) $result_id = $this->result;
		$this->row[$result_id] = @mysqli_fetch_row($result_id);
		return $this->row[$result_id];
	}

	function fetch_assoc($result_id = 0) {
		if (!$result_id) $result_id = $this->result;
		$this->row[$result_id] = @mysqli_fetch_assoc($result_id);
		return $this->row[$result_id];
	}

	function single_num($query) {
		$result = self::query($query)  or $this->error('Query failed '.$query);
		$row = @mysqli_fetch_row($result);
		mysqli_free_result($result);
		return $row;
	}

	function single_assoc($query) {
		$result = self::query($query) or $this->error('Query failed '.$query);
		$row = @mysqli_fetch_assoc($result);
		mysqli_free_result($result);
		return $row;
	}

	function rowset_num($query) {
		$rowset = array();
		$result = self::query($query) or $this->error('Query failed '.$query);
		while ($row = @mysqli_fetch_row($result)) {
			$rowset[] = $row;
		}
		mysqli_free_result($result);
		return $rowset;
	}

	function rowset_assoc($query) {
		$rowset = array();
		$result = self::query($query) or $this->error('Query failed '.$query);
		while ($row = @mysqli_fetch_assoc($result)) {
			$rowset[] = $row;
		}
		mysqli_free_result($result);
		return $rowset;
	}

	function rowset_num_limit($query,$offset,$rows) {
		$rowset = array();
		$result = self::query($query.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$query);
		while ($row = @mysqli_fetch_row($result)) {
			$rowset[] = $row;
		}
		mysqli_free_result($result);
		return $rowset;
	}

	function rowset_assoc_limit($query,$offset,$rows) {
		$rowset = array();
		$result = self::query($query.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$query);
		while ($row = @mysqli_fetch_assoc($result)) {
			$rowset[] = $row;
		}
		mysqli_free_result($result);
		return $rowset;
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
		if(mysqli_errno($this->db_link)) echo "Error No.".mysqli_errno($this->db_link)."：".mysqli_error($this->db_link)."<br>\n<font color=red>".$msg.'<br><hr>'.$ew.'</font>'."\n";
	}
	
	// logテーブルを再構築する(バックアップの復元に使用)
	function remake($table) {
		$query = "DROP TABLE $table";
		$result = self::query($query);
		if ($result) {
			$query = "CREATE TABLE $table (
			id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			title VARCHAR(255) BINARY,
			url VARCHAR(100),
			mark VARCHAR(19),
			last_time VARCHAR(21),
			passwd VARCHAR(13),
			message VARCHAR(255) BINARY,
			comment VARCHAR(255) BINARY,
			name VARCHAR(255) BINARY,
			mail VARCHAR(100),
			category VARCHAR(100),
			stamp INT UNSIGNED,
			banner VARCHAR(100),
			renew TINYINT UNSIGNED,
			ip VARCHAR(15),
			keywd VARCHAR(255) BINARY
			)";
			$result = self::query($query);
		}
		return $result;
	}
	
	// データベース内のテーブルリスト取得
	function list_tables() {
		
        $result = self::query('SHOW TABLES FROM '.$this->db_name);

		$i = 0;
        $tb_names = array();
//	    $tb_names = $result->fetch_all(MYSQLI_ASSOC);
        while ($row = $result->fetch_row()) {
          $tb_names[] = $row[0];
        }
	    
//		while ($i < mysqli_num_rows($result)) {
//			$tb_names[$i] = mysqli_tablename($result, $i);
//			$i++;
//		}
		return $tb_names;
	}


    /**
	 *
	 * sql_setnames
	 * MySQLサーバのバージョンが4.1以上の場合に[SET-NAMES]句を発行する
	 *
     * 引数:なし
     * 戻値:なし
     *
     */
    function sql_setnames()
    {
        if (mysqli_get_server_info($this->db_link) >= '4.1') {
            if(!$this->query('SET NAMES \'utf8\'')) die('error around sql-set-names.');
        }
        return TRUE;
    }

    function close()
    {
        mysqli_close($this->db_link);
    }
}
?>