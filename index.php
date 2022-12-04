<?php
$start_time1 = microtime(true);

/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP)modified ver1.5.8.n1 (Since2010/10/12)
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP)modified ver1.5 (Since2007/02/28)			   //
//   Yomi-Search				: http://yomi.pekori.to/   //
//   Yomi-Search(PHP)			: http://sql.s28.xrea.com:8080/	   //
//   Yomi-Search(PHP)modified	: http://yomiphp-mod.sweet82.com/	   //
/*--------------------------------------------------------------------------*/

// ---[利用規約]-------------------------------------------------------------+
// 1. このスクリプトはフリーソフトです。このスクリプトを使用した
//    いかなる損害に対して作者は一切の責任を負いません。
// 2. このスクリプトを使用した時点で利用規約(http://yomi.pekori.to/kiyaku.html)
//    に同意したものとみなさせていただきます。
//    ご使用になる前に必ずお読みください。
// --------------------------------------------------------------------------+


$mobile_flg = 0;
$strHostName = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
if ( strstr($strHostName, 'docomo.ne.jp') !== false) {
    $mobile_flg=1;
} elseif (preg_match('/\.[dhtckrnsq]\.vodafone\.ne\.jp/i', $strHostName) || preg_match('/\.softbank\.ne\.jp/i', $strHostName)) {
    $mobile_flg=3;
} elseif ( strstr($strHostName, 'ezweb.ne.jp') !== false ) {
    $mobile_flg=2;
}

if($mobile_flg > 0) {
    header('Location: ./m/index.php');
    exit();
}

// エラーレポート設定
require 'config4debug.php';
if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

require 'class/db.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

// [SQL-SET-NAMES]設定
$db->sql_setnames();

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

require $cfg['sub_path'] . "functions.php";
require $cfg['sub_path'] . 'ads.php';

// ページ設定
if(isset($_GET['page']) && is_numeric($_GET['page'])) {
 	if($_GET['page'] < 1 or $_GET['page'] > 1000) {
		$_GET['page'] = 1;
	}
} else if(isset($_GET['page'])){
	$_GET['page'] = preg_replace('/\D/', '', $_GET['page']);
	if($_GET['page'] < 1 or $_GET['page'] > 1000) {
		$_GET['page'] = 1;
	}
} else {
    $_GET['page'] = 1;
}


//-----------------//
//各モードへ分岐
//-----------------//
if(isset($_GET['mode'])) {
	if($_GET['mode'] == 'help') {
		require $cfg['temp_path'] . 'help.html';
	} else if($_GET['mode'] == 'random') {
		require $cfg['sub_path'] . 'random.php';
	} elseif($_GET['mode'] == 'mylink') {
		require $cfg['sub_path'] . 'mylink.php';
	} elseif ($_GET['mode']) {
		require $cfg['sub_path'] . 'category.php';
	}
} else {
    require $cfg['sub_path'] . 'top.php';
}


if($debugmode) {
    $end_time1 = microtime(true);
    echo '処理時間：';
    echo $end_time1 - $start_time1;
    echo "<BR />MEMORY:".memory_get_usage();
}

exit;


//(1)メッセージ画面出力(mes)
//書式:mes($arg1,$arg2,$arg3);
//機能:メッセージ画面を出力する
//引数:$arg1=>表示するメッセージ
//     $arg2=>ページのタイトル(省略時は「メッセージ画面」)
//     $arg3=>・JavaScriptによる「戻る」ボタン表示=java
//            ・HTTP_REFERERを使う場合=env
//            ・管理室へのボタン=kanri
//            ・通常のURL又はパスを指定する場合にはそのURL又はパスを記入
//            ・省略時は非表示
//戻り値:なし
function mes($mes, $title, $arg3) {
	global $cfg;
	if(!$title) {
		$title = 'メッセージ画面';
	}
	if($arg3 == 'java') {
		$back_url = '<form><input type="button" value="&nbsp;&nbsp;&nbsp;&nbsp;戻る&nbsp;&nbsp;&nbsp;&nbsp;" onClick="history.back()"></form>';
	} elseif($arg3 == 'env') {
		$back_url = '【<a href="'.$_SERVER['HTTP_REFERER'].'">戻る</a>】';
	} elseif(!$arg3) {
		$back_url = '';
	} else {
		$back_url = '【<a href="'.$arg3.'">戻る</a>】';
	}
	require $cfg['temp_path'] . 'mes.html';
	exit;
}


?>