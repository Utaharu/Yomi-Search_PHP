<?php
class db {

    // <データベース設定> /utf8_general_ciで作成してみた
    var $db_host = '';  // MySQLのホスト名
    var $db_user = '';       // MySQLのログインに使用するユーザ名
    var $db_pass = '';           // MySQLのログインに使用するパスワード
    var $db_name = '';           // MySQLのデータベース名
    var $db_pre  = '';       // テーブルの先頭に付加するプレフィックス
    // </データベース設定>

    var $db_link;
    var $result;
    var $row = array();

    /**
     * Constructer
     * Opens a connection to a MySQL Server.
     * 
     * @return
     */
    function db() {
        $this->db_link = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
        mysql_select_db($this->db_name);
        return TRUE;
    }

    // クエリ発行
    function query($query) {
        $this->result = mysql_query($query) or $this->error('Query failed '.$query);
        return $this->result;
    }

    // MySQL用に文字列をエスケープ
    function escape_string($str) {
        return @mysql_escape_string($str);
    }
	
    // $table内の総ログ数を求める
    function log_count($table) {
        $query = 'SELECT count(id) FROM ' . $table;
        $result = mysql_query($query)  or $this->error('Query failed '.$query);
        $tmp = mysql_fetch_row($result);
        mysql_free_result($result);
        return $tmp[0];
    }

	// 最後にinsertしたデータのidを求める
	function last_id() {
		return @mysql_insert_id($this->db_link);
	}

	function fetch_num($result_id = 0) {
		if (!$result_id) $result_id = $this->result;
		$this->row[$result_id] = @mysql_fetch_row($result_id);
		return $this->row[$result_id];
	}

	function fetch_assoc($result_id = 0) {
		if (!$result_id) $result_id = $this->result;
		$this->row[$result_id] = @mysql_fetch_assoc($result_id);
		return $this->row[$result_id];
	}

	function single_num($query) {
		$result = mysql_query($query)  or $this->error('Query failed '.$query);
		$row = @mysql_fetch_row($result);
		mysql_free_result($result);
		return $row;
	}

	function single_assoc($query) {
		$result = mysql_query($query) or $this->error('Query failed '.$query);
		$row = @mysql_fetch_assoc($result);
		mysql_free_result($result);
		return $row;
	}

	function rowset_num($query) {
		$rowset = array();
		$result = mysql_query($query) or $this->error('Query failed '.$query);
		while ($row = @mysql_fetch_row($result)) {
			$rowset[] = $row;
		}
		mysql_free_result($result);
		return $rowset;
	}

	function rowset_assoc($query) {
		$rowset = array();
		$result = mysql_query($query) or $this->error('Query failed '.$query);
		while ($row = @mysql_fetch_assoc($result)) {
			$rowset[] = $row;
		}
		mysql_free_result($result);
		return $rowset;
	}

	function rowset_num_limit($query,$offset,$rows) {
		$rowset = array();
		$result = mysql_query($query.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$query);
		while ($row = @mysql_fetch_row($result)) {
			$rowset[] = $row;
		}
		mysql_free_result($result);
		return $rowset;
	}

	function rowset_assoc_limit($query,$offset,$rows) {
		$rowset = array();
		$result = mysql_query($query.' LIMIT '.$offset.','.$rows) or $this->error('Query failed '.$query);
		while ($row = @mysql_fetch_assoc($result)) {
			$rowset[] = $row;
		}
		mysql_free_result($result);
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
		if(mysql_errno()) echo "Error No.".mysql_errno()."：".mysql_error()."<br>\n<font color=red>".$msg.'<br><hr>'.$ew.'</font>'."\n";
	}
	
	// logテーブルを再構築する(バックアップの復元に使用)
	function remake($table) {
		$query = "DROP TABLE $table";
		$result = @mysql_query($query);
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
			$result = @mysql_query($query);
		}
		return $result;
	}
	
	// データベース内のテーブルリスト取得
	function list_tables() {
		
//                $result = mysql_list_tables($this->db_name);
                $result = @mysql_query('SHOW TABLES FROM '.$this->db_name);

		$i = 0;
        $tb_names = array();
		while ($i < mysql_num_rows($result)) {
			$tb_names[$i] = mysql_tablename($result, $i);
			$i++;
		}
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
        if (mysql_get_server_info() >= '4.1') {
            if(!$this->query('SET NAMES \'utf8\'')) die('error around sql-set-names.');
        }
        return TRUE;
    }

    function close()
    {
        mysql_close($this->db_link);
    }
}
?>