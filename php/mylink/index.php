<?php
/* ---------------------------------------------------------------- ##
## MyLink for Yomi-Search(PHP) ver0.3                               ##
##                                                                  ##
## Master : mitsuki                                                 ##
## URL    : http://yomiphp-mod.sweet82.com/                         ##
## update : NKBT                                                    ##
## URL    : http://www.nkbt.net/                                    ##
## ---------------------------------------------------------------- */
require_once $script['lib']['plugin']['navigation'];

// 変数初期化
$cookie_new = '';
$id_array = array();

// 表示ページ取得
if (!isset($Form['page'])) {
	$Form['page'] = 1;
}

// ソート順を取得
// ソート順が指定されていなければデフォルトのソート順をセット
if (!isset($Form['sort'])) {
	$Form['sort'] = $cfg['defo_hyouji'];
}

// バナーサイズ取得
$query = 'SELECT name,value FROM {prefix}cfg_reg WHERE name IN (\'Mbana_w\',\'Mbana_h\')';
$rowset = $db->rowset_num($query);
foreach ($rowset as $tmp) {
	$cfg_reg[$tmp[0]] = $tmp[1];
}

// 追加処理
if (isset($Form['act']) && $Form['act'] == 'add') {

	// 配列にサイトIDを追加
	if (isset($_COOKIE['mylink4php'])) {
	
		$id_array = explode(',', $_COOKIE['mylink4php']);
		$FLG = 0;
		
		// 重複がなければIDを追加
		foreach ($id_array as $tmp) {
			if ($Form['id'] == $tmp) {
				$FLG = 1;
			}
		}
		if (!$FLG) {
			array_push($id_array, $Form['id']);
		}
		
	} else {
	
		array_push($id_array, $Form['id']);
		
	}
	
	// 配列をカンマで連結
	$cookie_new = implode(',', $id_array);
	
	// COOKIEをセット
	setcookie('mylink4php', $cookie_new, time() + 31536000);
	JumpMylink();

// 削除処理	
} elseif (isset($Form['act']) && $Form['act'] == 'del') {

	// 指定IDを削除
	if ($_COOKIE['mylink4php']) {
	
		$id_array = explode(',', $_COOKIE['mylink4php']);
		
		// 指定ID(削除するID)以外を$cookie_newに追加
		foreach ($id_array as $tmp) {
			if ($Form['id'] != $tmp) {
				$cookie_new .= $tmp . ',';
			}
		}
		$cookie_new = substr($cookie_new, 0, -1);
		
	}
	
	setcookie('mylink4php', $cookie_new, time() + 31536000);
	JumpMylink();
	
}

if (isset($_COOKIE['mylink4php'])) {

	switch ($Form['sort']) {
		case 'id_new'  : $order = 'id DESC'; break;
		case 'id_old'  : $order = 'id'; break;
		case 'time_new': $order = 'stamp DESC'; break;
		case 'time_old': $order = 'stamp'; break;
		case 'ac_new'  : $order = 'title'; break;
		case 'ac_old'  : $order = 'title DESC'; break;
		default: $order = 'mark DESC, id DESC';
	}
	
	$mylink_id_array = explode(',', $_COOKIE['mylink4php']);
	
	$where = '';
	foreach ($mylink_id_array as $tmp) {
		if ($where) {
			$where .= ' or id=\'' . $tmp . '\'';
		} else {
			$where .= 'id=\'' . $tmp . '\'';
		}
	}
	
	$st_no = $cfg['hyouji'] * ($Form['page'] - 1);
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM {prefix}log WHERE '.$where.' ORDER BY '.$order;
	$rowset = array();
	$rowset = $db->rowset_assoc_limit($query, $st_no, $cfg['hyouji']);
	$log_lines = array(); // 表示データリスト

	foreach ($rowset as $log_data) {
		//Get IN,OUT Rank(Request By.Dan 2023/02/19)
		$query = 'SELECT rank,rev FROM {prefix}rank_counter WHERE id=\''.$log_data['id'].'\'';
		$r_count = $db->single_assoc($query);
		$log_data['in_count'] = $r_count['rev'];//IN
		$log_data['out_count'] = $r_count['rank'];//OUT
		array_push($log_lines, $log_data);
	}
	
	unset($rowset);
	
}

//Load Config
require_once $script['lib']['db']['get_setting'];
$Settings = new Get_Settings($db);
$cfg = array();
$cfg = $Settings->Common;

$title = 'マイリンク';

// 表示するデータが存在する場合に、その件数を求める
if(isset($log_lines) && count($log_lines) > 0) {
	$query1 = 'SELECT count(id) FROM {prefix}log WHERE '.$where;
	$num = $db->single_num($query1);
	$log_count = $num[0];
} else {
	$log_count = 0;
}

$arg = array($Form['page'], $log_count, $cfg['hyouji'], '&mode='.$Form['mode'].'&sort='.$Form['sort'], $cfg['script']);
$mokuji = Navigation::Page($arg);
require $path_set['template']['root'] . '/mylink.html';

function JumpMylink() {
	global $cfg;
	header('Location: ' . $cfg['script'] . '?mode=mylink');
	exit;
}
?>