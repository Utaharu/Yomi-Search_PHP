<?php
// クラスファイルインクルード
require_once '../class/db.php';

// cfgファイル(設定情報)インクルード
require_once 'cfg.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

// [SQL-SET-NAMES]設定
$db->sql_setnames();

// データベース内のテーブルリスト取得
$table = $db->list_tables();
foreach ($table as $name) {
	$table_list[$name] = TRUE;
}

// 表示用メッセージ初期化
$mes = "";

// categoryテーブル作成
if(!isset($table_list["{$db->db_pre}category"])) {
	$sql = "CREATE TABLE {$db->db_pre}category (
		id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(255) BINARY,
		up_id INT UNSIGNED,
		path VARCHAR(100),
		top_list CHAR(1),
		etc_list CHAR(1),
		regist CHAR(1),
		reffer VARCHAR(100),
		sort_name VARCHAR(255) BINARY,
		comment VARCHAR(255) BINARY
		)";
	$db->query($sql);
	foreach($ganes as $key => $title) {
		if(stristr($key, '_')) {
			continue;
		}
		$category = str_replace('_', '/', $key);
		if(isset($gane_UR[$key])) {
			$regist = $gane_UR[$key];
		} else {
			$regist = '';
		}
		$sort_name = $EST_furi[$key];
		if(isset($KTEX[$key])) {
			$comment = $KTEX[$key];
		} else {
			$comment = '';
		}
		$etc_list = '';
		foreach ($gane_other as $tmp) {
			if ($tmp == $key) {
				$etc_list = 1;
				break;
			}
		}
		$title = $db->escape_string($title);
		$sort_name = $db->escape_string($sort_name);
		$comment = $db->escape_string($comment);
		$sql = "INSERT INTO {$db->db_pre}category VALUES(NULL, '$title', '0', '{$category}/', '1', '$etc_list', '$regist', '', '$sort_name', '$comment');";
		$db->query($sql);
		$id[$key] = $db->last_id();
	}
	foreach ($ganes as $key => $title) {
		if (!stristr($key, '_')) {
			continue;
		}
		$category = str_replace('_', '/', $key);
		$up = substr($key, 0, strrpos($key, '_'));
		$up_id = $id[$up];
		if(isset($gane_top[$key])) {
			$top_list = $gane_top[$key];
		} else {
			$top_list = '';
		}
		if(isset($gane_UR[$key])) {
			$regist = $gane_UR[$key];
		} else {
			$regist = '';
		}
		$sort_name = $EST_furi[$key];
		if(isset($KTEX[$key])) {
			$comment = $KTEX[$key];
		} else {
			$comment = '';
		}
		$etc_list = '';
		foreach ($gane_other as $tmp) {
			if ($tmp == $key) {
				$etc_list = 1;
				break;
			}
		}
		$title = $db->escape_string($title);
		$sort_name = $db->escape_string($sort_name);
		$comment = $db->escape_string($comment);
		$sql = "INSERT INTO {$db->db_pre}category VALUES(NULL, '{$title}', '{$up_id}', '{$category}/', '{$top_list}', '{$etc_list}', '{$regist}', '', '{$sort_name}', '{$comment}');";
		$db->query($sql);
		$id[$key] = $db->last_id();
	}
	foreach ($gane_ref as $key=>$val) {
		if ($val) {
			$ref_id = explode('&', $val);
			foreach ($ref_id as $tmp) {
				$sql = "UPDATE {$db->db_pre}category SET reffer='&{$id[$key]}&' WHERE id='{$id[$tmp]}'";
				$db->query($sql);
			}
		}
	}
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}category を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}category は作成済みです！<br>\n<br>\n";
}

// cfgテーブル作成
if(!isset($table_list["{$db->db_pre}cfg"])) {
	$sql = "CREATE TABLE {$db->db_pre}cfg (
		name VARCHAR(20),
		value VARCHAR(255)
		)";
	$db->query($sql);
	// cfgテーブルに初期データをインサート
	foreach($EST as $key=>$val) {
		$key = $db->escape_string($key);
		$val = $db->escape_string($val);
		$sql = "INSERT INTO {$db->db_pre}cfg VALUES('" . $key . "', '" . $val . "')";
		$db->query($sql);
	}
	foreach($KTEX as $key=>$val) {
		if(preg_match("/^\d{2}/", $key)) continue;
		if($key == 'rank') $key = 'rank_ys';
		$key = $db->escape_string($key);
		$val = $db->escape_string($val);
		$sql = "INSERT INTO {$db->db_pre}cfg VALUES('$key', '$val')";
		$db->query($sql);
	}
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}cfg を作成しました。<br>\n<br>";
} else {
	$mes .= "テーブル {$db->db_pre}cfg は作成済みです！<br>\n<br>\n";
}

// cfg_regテーブル作成
if(!isset($table_list["{$db->db_pre}cfg_reg"])) {
	$sql = "CREATE TABLE {$db->db_pre}cfg_reg (
		name VARCHAR(20),
		value VARCHAR(255)
		)";
	$db->query($sql);
	foreach ($EST_reg as $key=>$val) {
		$key = $db->escape_string($key);
		$val = $db->escape_string($val);
		$sql = "INSERT INTO {$db->db_pre}cfg_reg VALUES('$key', '$val')";
		$db->query($sql);
	}
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}cfg_reg を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}cfg_reg は作成済みです！<br>\n<br>\n";
}

// counterテーブル作成
if(!isset($table_list["{$db->db_pre}counter"])) {
	$sql = "CREATE TABLE {$db->db_pre}counter (
		counter INT UNSIGNED
		)";
	$db->query($sql);
	// counterテーブルに初期データをインサート
	$sql = "INSERT INTO {$db->db_pre}counter VALUES('0')";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}counter を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}counter は作成済みです！<br>\n<br>\n";
}

// counter_logテーブル作成
if(!isset($table_list["{$db->db_pre}counter_log"])) {
	$sql = "CREATE TABLE {$db->db_pre}counter_log (
		ip VARCHAR(40),
		time BIGINT UNSIGNED
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}counter_log を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}counter_log は作成済みです！<br>\n<br>\n";
}

// keyテーブル作成
if(!isset($table_list["{$db->db_pre}key"])) {
	$sql = "CREATE TABLE {$db->db_pre}key (
		word VARCHAR(50),
		time INT UNSIGNED,
		ip VARCHAR(40)
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}key を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}key は作成済みです！<br>\n<br>\n";
}

// key_rankテーブル作成
if(!isset($table_list["{$db->db_pre}key_rank"])) {
	$sql = "CREATE TABLE {$db->db_pre}key_rank (
		word VARCHAR(255) BINARY,
		open_key CHAR(1),
		bad_key CHAR(1),
		view_word VARCHAR(255) BINARY
	)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}key_rank を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}key_rank は作成済みです！<br>\n<br>\n";
}

// logテーブル作成
if(!isset($table_list["{$db->db_pre}log"])) {
	$sql = "CREATE TABLE {$db->db_pre}log (
		id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(255) BINARY,
		url VARCHAR(100),
		mark VARCHAR(19),
		last_time VARCHAR(21),
		passwd VARCHAR(13),
		message BLOB,
		comment BLOB,
		name VARCHAR(255) BINARY,
		mail VARCHAR(100),
		category VARCHAR(100),
		stamp INT UNSIGNED,
		banner VARCHAR(100),
		renew TINYINT UNSIGNED,
		ip VARCHAR(40),
		keywd VARCHAR(255) BINARY
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}log を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}log は作成済みです！<br>\n<br>\n";
}

// log_tempテーブル作成
if(!isset($table_list["{$db->db_pre}log_temp"])) {
	$sql = "CREATE TABLE {$db->db_pre}log_temp (
		id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		title VARCHAR(255) BINARY,
		url VARCHAR(100),
		mark VARCHAR(19),
		last_time VARCHAR(21),
		passwd VARCHAR(13),
		message BLOB,
		comment BLOB,
		name VARCHAR(255) BINARY,
		mail VARCHAR(100),
		category VARCHAR(100),
		stamp BIGINT UNSIGNED,
		banner VARCHAR(100),
		renew TINYINT UNSIGNED,
		ip VARCHAR(40),
		keywd VARCHAR(255) BINARY
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}log_temp を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}log_temp は作成済みです！<br>\n<br>\n";
}

// rankテーブル作成
if(!isset($table_list["{$db->db_pre}rank"])) {
	$sql = "CREATE TABLE {$db->db_pre}rank (
		id INT UNSIGNED,
		time INT UNSIGNED,
		ip VARCHAR(40)
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}rank を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}rank は作成済みです！<br>\n<br>\n";
}

// rank_counterテーブル作成
if(!isset($table_list["{$db->db_pre}rank"])) {
	$sql = "CREATE TABLE {$db->db_pre}rank_counter (
		id INT UNSIGNED,
		rank INT UNSIGNED,
		rev INT UNSIGNED
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}rank_counter を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}rank_counter は作成済みです！<br>\n<br>\n";
}

// reportテーブル作成
if(!isset($table_list["{$db->db_pre}report"])) {
	$sql = "CREATE TABLE {$db->db_pre}report (
		id INT UNSIGNED,
		ip VARCHAR(40),
		no_url CHAR (1),
		move CHAR (1),
		no_banner CHAR (1),
		violation CHAR (1),
		other CHAR (1),
		comment VARCHAR(255) BINARY,
		name VARCHAR(100) BINARY,
		email VARCHAR(100) BINARY
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}report を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}report は作成済みです！<br>\n<br>\n";
}

// revテーブル作成
if(!isset($table_list["{$db->db_pre}rev"])) {
	$sql = "CREATE TABLE {$db->db_pre}rev (
		id INT UNSIGNED,
		time INT UNSIGNED,
		ip VARCHAR(40)
		)";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}rev を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}rev は作成済みです！<br>\n<br>\n";
}

// textテーブル作成
if(!isset($table_list["{$db->db_pre}text"])) {
	$sql = "CREATE TABLE {$db->db_pre}text (
		name VARCHAR(20),
		value BLOB
		)";
	$db->query($sql);
	$search_form = $db->escape_string($search_form);
	$sql = "INSERT INTO {$db->db_pre}text VALUES('search_form', '{$search_form}')";
	$db->query($sql);
	$menu_bar = $db->escape_string($menu_bar);
	$sql = "INSERT INTO {$db->db_pre}text VALUES('menu_bar', '{$menu_bar}')";
	$db->query($sql);
	$sql = "INSERT INTO {$db->db_pre}text VALUES('head_sp', '')";
	$db->query($sql);
	$sql = "INSERT INTO {$db->db_pre}text VALUES('foot_sp', '')";
	$db->query($sql);
	$mes .= "データベース {$db->db_name} にテーブル {$db->db_pre}text を作成しました。<br>\n<br>\n";
} else {
	$mes .= "テーブル {$db->db_pre}text は作成済みです！<br>\n<br>\n";
}
?>