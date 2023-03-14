<?php
/* ---------------------------------------------------------------- ##
## MyLink for Yomi-Search(PHP) ver0.3                               ##
##                                                                  ##
## Master : mitsuki                                                 ##
## URL    : http://yomiphp-mod.sweet82.com/                         ##
## update : NKBT                                                    ##
## URL    : http://www.nkbt.net/                                    ##
## ---------------------------------------------------------------- */

// 変数初期化
$cookie_new = '';
$id_array = array();

// 表示ページ取得
if (!isset($_GET['page'])) {
	$_GET['page'] = 1;
}

// ソート順を取得
// ソート順が指定されていなければデフォルトのソート順をセット
if (!isset($_GET['sort'])) {
	$_GET['sort'] = $cfg['defo_hyouji'];
}

// バナーサイズ取得
$query = 'SELECT name,value FROM '.$db->db_pre.'cfg_reg WHERE name IN (\'Mbana_w\',\'Mbana_h\')';
$rowset = $db->rowset_num($query);
foreach ($rowset as $tmp) {
	$cfg_reg[$tmp[0]] = $tmp[1];
}

// 追加処理
if (isset($_GET['act']) && $_GET['act'] == 'add') {

	// 配列にサイトIDを追加
	if (isset($_COOKIE['mylink4php'])) {
	
		$id_array = explode(',', $_COOKIE['mylink4php']);
		$FLG = 0;
		
		// 重複がなければIDを追加
		foreach ($id_array as $tmp) {
			if ($_GET['id'] == $tmp) {
				$FLG = 1;
			}
		}
		if (!$FLG) {
			array_push($id_array, $_GET['id']);
		}
		
	} else {
	
		array_push($id_array, $_GET['id']);
		
	}
	
	// 配列をカンマで連結
	$cookie_new = implode(',', $id_array);
	
	// COOKIEをセット
	setcookie('mylink4php', $cookie_new, time() + 31536000);
	JumpMylink();

// 削除処理	
} elseif (isset($_GET['act']) && $_GET['act'] == 'del') {

	// 指定IDを削除
	if ($_COOKIE['mylink4php']) {
	
		$id_array = explode(',', $_COOKIE['mylink4php']);
		
		// 指定ID(削除するID)以外を$cookie_newに追加
		foreach ($id_array as $tmp) {
			if ($_GET['id'] != $tmp) {
				$cookie_new .= $tmp . ',';
			}
		}
		$cookie_new = substr($cookie_new, 0, -1);
		
	}
	
	setcookie('mylink4php', $cookie_new, time() + 31536000);
	JumpMylink();
	
}

if (isset($_COOKIE['mylink4php'])) {

	switch ($_GET['sort']) {
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
	
	$st_no = $cfg['hyouji'] * ($_GET['page'] - 1);
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE '.$where.' ORDER BY '.$order;
	$rowset = array();
	$rowset = $db->rowset_assoc_limit($query, $st_no, $cfg['hyouji']);
	$log_lines = array(); // 表示データリスト

	foreach ($rowset as $log_data) {
		//Get IN,OUT Rank(Request By.Dan 2023/02/19)
		$query = 'SELECT rank,rev FROM '.$db->db_pre.'rank_counter WHERE id=\''.$log_data['id'].'\'';
		$r_count = $db->single_assoc($query);
		$log_data['in_count'] = $r_count['rev'];//IN
		$log_data['out_count'] = $r_count['rank'];//OUT
		array_push($log_lines, $log_data);
	}
	
	unset($rowset);
	
}

$query = 'SELECT name,value FROM '.$db->db_pre.'cfg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg[$tmp[0]] = $tmp[1];
}

$title = 'マイリンク';

// 表示するデータが存在する場合に、その件数を求める
if(isset($log_lines) && count($log_lines) > 0) {
	$query1 = 'SELECT count(id) FROM '.$db->db_pre.'log WHERE '.$where;
	$num = $db->single_num($query1);
	$log_count = $num[0];
} else {
	$log_count = 0;
}

$arg = array($_GET['page'], $log_count, $cfg['hyouji'], '&mode='.$_GET['mode'].'&sort='.$_GET['sort'], $cfg['script']);
$mokuji = mokuji($arg);
require $cfg['temp_path'] . 'mylink.html';

function JumpMylink() {
	global $cfg;
	header('Location: ' . $cfg['script'] . '?mode=mylink');
	exit;
}
?>