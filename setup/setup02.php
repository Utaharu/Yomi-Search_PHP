<?php
// クラスファイルインクルード
require_once '../class/db.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

// [SQL-SET-NAMES]設定
$db->sql_setnames();

// cfgテーブルから設定情報を配列($cfg)へ読込
$query = 'SELECT name, value FROM '.$db->db_pre.'cfg';
$rowset = $db->rowset_num($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
foreach($rowset as $tmp) {
	$cfg[$tmp[0]] = $tmp[1];
}

// 検索エンジンのURL取得(例：http://localhost/yomi/yomi.php)
$home = 'http://' . htmlspecialchars($HTTP_SERVER_VARS['HTTP_HOST'], ENT_QUOTES) . htmlspecialchars($HTTP_SERVER_VARS['REQUEST_URI'], ENT_QUOTES);
$home = substr($home, 0, -15);
$home .= 'yomi.php';

// yomi.phpが設置されているURLを取得
$cgi_path_url = 'http://' . htmlspecialchars($HTTP_SERVER_VARS['HTTP_HOST'], ENT_QUOTES) . htmlspecialchars($HTTP_SERVER_VARS['REQUEST_URI'], ENT_QUOTES);
$cgi_path_url = substr($cgi_path_url, 0, -15);
?>