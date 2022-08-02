<?php
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

// インクルード
require 'class/db.php';
require 'functions.php';
require 'ads.php';

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



// categoryテーブルからカテゴリ情報を配列($ganes)へ読込
$ganes=array();
$query = 'SELECT path,title FROM '.$db_pre.'category ORDER BY path';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$ganes[$tmp[0]] = $tmp[1];
}

if(isset($_GET['word'])) {
	$_GET['word'] = mb_convert_encoding($_GET['word'], 'UTF-8', 'auto');
	$_GET['word'] = htmlspecialchars($_GET['word']);
	if(isset($_GET['words'])) {
		if($_GET['words']) {
			$_GET['words'] = array_map('htmlspecialchars', $_GET['words']);
		}
	}
}

// -- 目次 -- //
// (1)検索結果表示画面(search)
// (2)詳細検索画面(search_ex)
// (3)外部リンク画面(meta)
// (4)検索処理用データをハッシュに入れる(open_for_search)
// (5)外部検索エンジンへのリンク一覧を表示(PR_mata_page)
// (6)キーワードを一時ランキングファイル(keyrank_temp.cgi)に記録(set_word)

// テンプレートファイル
// 検索結果画面=>temp/search.html
// 詳細検索画面=>temp/search_ex.html
// 外部リンク画面=>temp/search_meta.html

// 【カテゴリ検索】
// [オプション]
// カテゴリ指定([kt]&[option(b_all=以下)])
// 日付指定=>( today-x | year/mon/day | [str_day]-[end_day] )
// 新規ウィンドウ=>window=new
if(!isset($_GET['page'])) {
	$_GET['page'] = 1;
}

// mode値で処理分岐
if(isset($_GET['mode'])) {
	// (1)検索結果表示画面(search)
	if($_GET['mode'] == 'search') { // 検索結果表示画面
		require $cfg['sub_path'] . 'search.php';
		exit;
	// 外部リンク画面
	} elseif($_GET['mode'] == 'meta') {
		require $cfg['sub_path'] . 'search_meta.php';
		exit;
	// 詳細検索画面
	} else {
		require $cfg['sub_path'] . 'search_ex.php';
		exit;
	}
} else {
	// 詳細検索画面
	require $cfg['sub_path'] . 'search_ex.php';
	exit;
}


// (5)外部検索エンジンへのリンク一覧を表示(&PR_mata_page)
function PR_meta_page($location_list) {
	$T_flag = 1;
        $w = '';
	$w = '<table width="90%" cellpadding="8">'."\n";
	foreach($location_list as $list) {
		list($Dengine, $Durl) = explode('<>', $list);
		if($T_flag == 5) {
			$w.= '</tr>'."\n";
			$T_flag=1;
		}
		if($T_flag == 1) {
			$w.= '<tr>'."\n";
		}
		$w.= '<th><a href="'.$Durl.'"';
		if(isset($_POST['target'])) {
			$w.= ' target="'.$_POST['target'].'"';
		}
		$w.= '><font size="+1">'.$Dengine.'</font></a></th>';
		$T_flag++;
	}
	if($T_flag != 2) {
		$w.= '</tr>'."\n";
	}
	$w.= '</table>'."\n";
        echo $w;
        return true;
}

// (6)キーワードをデータベースに記録(&set_word)
function set_word() {
	global $db;
	$time = time();
	$keyword = $_GET['word'];
	if(strlen($keyword) < 50) {
		$keyword = str_replace('　', ' ', $keyword);
		$keyword = mb_strtolower($keyword, 'UTF-8');
		$keyword = $db->escape_string($keyword);
		$keywords = explode(' ', $keyword);
		if(count($keywords) > 0) {
			foreach($keywords as $i) {
				if($i && $i != 'and' && $i != 'or' && $i != 'not') {
					$i = str_replace("\n", '', $i);
					$query = 'SELECT word FROM '.$db->db_pre.'key WHERE word=\''.$i.'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - 24 * 3600);
					$tmp = $db->single_num($query);
					if(!$tmp) {
						$query = 'INSERT INTO '.$db->db_pre.'key (word, time, ip) VALUES (\''.$i.'\', \''.$time.'\', \''.$_SERVER['REMOTE_ADDR'].'\')';
						$db->query($query);
					}
				}
			}
		}
	}
}

// (t1)メッセージ画面出力(mes)
// 書式:&mes($arg1,$arg2,$arg3);
// 機能:メッセージ画面を出力する
// 引数:$arg1=>表示するメッセージ
//      $arg2=>ページのタイトル(省略時は「メッセージ画面」)
//      $arg3=>・JavaScriptによる「戻る」ボタン表示=java
//             ・HTTP_REFERERを使う場合=env
//             ・管理室へのボタン=kanri
//             ・通常のURL又はパスを指定する場合にはそのURL又はパスを記入
//             ・省略時は非表示
// 戻り値:なし
function mes($mes, $title, $arg3 = '') {
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
//	header ("Content-type: text/html; charset=UTF-8");
	require $cfg['temp_path'] . 'mes.html';
	exit;
}
?>