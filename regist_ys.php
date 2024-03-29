<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP) modified データ登録用プログラム 						//
/*--------------------------------------------------------------------------*/

// エラーレポート設定
require 'php/config4debug.php';

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

require './class/db.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

//local変数に
$db_pre = $db->db_pre;

// cfgテーブルから設定情報を配列($cfg)へ読込
$cfg = array();

// cfg_regテーブルから設定情報を配列($cfg_reg)へ読込
$cfg_reg = array();

// textテーブルから設定情報を配列($text)へ読込
$text = array();

// cfgテーブルから設定情報を配列($cfg)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'cfg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg[$tmp[0]] = $tmp[1];
}

// textテーブルから設定情報を配列($text)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'text';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$text[$tmp[0]] = $tmp[1];
}

// cfg_regテーブルから設定情報を配列($cfg_reg)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'cfg_reg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg_reg[$tmp[0]] = $tmp[1];
}

require $cfg['sub_path'] . 'functions.php';
require $cfg['sub_path'] .'functions_reg.php';

//[html環境設定->menu_bar]と、[環境設定->スクリプト名]でのpath。置換処理。
Menu_Bar();

// 修正・削除のためのパスワード認証(enter)
if(isset($_REQUEST['mode'])) {
	if($_REQUEST['mode'] == 'enter') {
		require $cfg['sub_path'] . 'enter.php';
		exit;
	}
	if($_REQUEST['mode'] == 'no_link') {
		require $cfg['sub_path'] . 'no_link.php';
	}
}

if(isset($_POST['mode'])) {
	// 新規登録実行(act_regist)
	if($_POST['mode'] == 'act_regist') {
		require $cfg['sub_path'] . 'act_regist.php';
		exit;
	}
	// 新規登録実行(代理登録)
	if($_POST['mode'] == 'new_dairi') {
		require $cfg['sub_path'] . 'regist_new_admin.php';
        exit;
	}
	// 削除実行
	if($_POST['mode'] == 'act_del'){
		require $cfg['sub_path'] . 'act_del.php';
		exit;
	}
	// パスワード再発行
	if($_POST['mode'] == 'act_repass') {
		require $cfg['sub_path'] . 'act_repass.php';
	}
}

// 登録内容変更
if(isset($_REQUEST['mode'])) {
	if($_REQUEST['mode'] == 'mente' || $_REQUEST['mode'] == 'act_mente') {
		require $cfg['sub_path'] . 'act_mente.php';
		exit;
	}
}
require $cfg['sub_path'] . 'regist_new.php';

// 終了
exit;
?>