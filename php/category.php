<?php
//カテゴリ表示
if(!isset($_GET['path']) || !checkSQLWord($_GET['path'])) {
	$_GET['path'] = '';
}

// 表示ページ取得
if(!isset($_GET['page']) || !preg_match('/^\d+$/', $_GET['page'])) {
	$_GET['page'] = 1;
}

// ソート順を取得
// ソート順が指定されていなければデフォルトのソート順をセット
if(!isset($_GET['sort']) || !checkSQLWord($_GET['path']) ) {
	$_GET['sort'] = $cfg['defo_hyouji'];
}

if(! isset($cfg_reg['Mbana_w']) || ! isset($cfg_reg['Mbana_h']) ) {
    $query = 'SELECT name,value FROM '.$db->db_pre.'cfg_reg WHERE name IN (\'Mbana_w\',\'Mbana_h\')';
    $rowset = $db->rowset_num($query);
    foreach($rowset as $tmp) {
            $cfg_reg[$tmp[0]] = $tmp[1];
    }
}

$cookie_data = array();
if(! defined('MOBILE_TEMPLATE')) {
    $cookie_data = get_cookie();
}

// データの読み込み＆下層カテゴリ表示
// データの読み込み
$time = time();
$start = $time - $cfg['rank_kikan'] * 86400;
$end = $time;
$navi = '';

// カテゴリの登録可否フラグを初期化
$regist = 0;

if($_GET['mode'] == 'dir') { //各カテゴリの場合
	$query = 'SELECT title, regist, comment FROM '.$db->db_pre.'category WHERE path=\''.$_GET['path'].'\' LIMIT 1';
	$row = $db->single_assoc($query);
	$title = $row['title'];
	$guide = $row['comment'];
	if(isset($row['regist'])) { // カテゴリの登録可否フラグを更新
		$regist = $row['regist'];
	}
	switch($_GET['sort']) {
		case 'id_new'  : $order = 'id DESC'; break;
		case 'id_old'  : $order = 'id'; break;
		case 'time_new': $order = 'stamp DESC'; break;
		case 'time_old': $order = 'stamp'; break;
		case 'ac_new'  : $order = 'title'; break;
		case 'ac_old'  : $order = 'title DESC'; break;
		default: $order = "char_length(replace(replace(mark,'_',''),'0','')) DESC, id DESC";
	}
	$search_id = $_GET['path']; //検索対象のカテゴリ番号
	$log_lines = array(); //表示データリスト
	$log_count = array(); //各カテゴリの登録数

	$st_no = $cfg['hyouji'] * ($_GET['page'] - 1);

	$query = 'SELECT id, title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd  FROM '.$db->db_pre.'log WHERE category LIKE \'%&'.$_GET['path'].'%&%\' ORDER BY '.$order;
	$rowset = $db->rowset_assoc_limit($query, $st_no, $cfg['hyouji']);
	foreach ($rowset as $log_data) {
		if ($cookie_data[3]) { //adminモード
			$query = 'SELECT count(id) FROM '.$db->db_pre.'rank WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] = $count[0];
			$query = 'SELECT rank,rev FROM '.$db->db_pre.'rank_counter WHERE id=\''.$log_data['id'].'\'';
			$r_count = $db->single_assoc($query);
			$log_data['count'] .= '_'.$r_count['rank'];
			$query = 'SELECT count(id) FROM '.$db->db_pre.'rev WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] .= '_' . $count[0];
			$log_data['count'] .= '_' . $r_count['rev'];
		}
//		array_push($log_lines, $log_data);
//              $log_lines += $log_data;
                $log_data['title'] = str_replace('&amp;amp;', '&', $log_data['title']);
                $log_lines[] = $log_data;
	}
	unset($rowset);
        unset($log_data);

	$query = 'SELECT count(id) FROM '.$db->db_pre.'log WHERE category LIKE \'%&'.$_GET['path'].'%&%\'';
	$num = $db->single_num($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	$log_count[$search_id] = $num[0];
	// ナビゲーションバーを表示
	$navi = navi_bar($_GET['path']);
} else { //その他の特殊カテゴリ(新着/更新/おすすめ/相互)
	$title = $cfg['name_'.$_GET['mode']];
	$guide = $cfg[$_GET['mode'].'_ys'];
	$log_lines = array(); //表示データリスト
	$search_id = $_GET['mode'];
	$st_no = $cfg['hyouji'] * ($_GET['page'] -1);
	if ($_GET['mode'] == 'new') {
		$ntime = time() - $cfg['new_time'] * 24 * 3600;
		$query = ' stamp > '.$ntime." AND renew = 0 ORDER BY char_length(replace(replace(mark,'_',''),'0','')) DESC, id DESC";
	} elseif($_GET['mode'] == 'renew') {
		$ntime = time() - $cfg['new_time'] * 24 * 3600;
		$query = ' stamp > '.$ntime.' AND renew = 1 ORDER BY stamp DESC';
	} elseif($_GET['mode'] == 'm1') {
		$query = ' mark LIKE \'1%\'';
	} elseif($_GET['mode'] == 'm2') {
		$query = ' substring(mark,3,1)=\'1\'';
	} elseif($_GET['mode'] == 'm3') {
		$query = ' substring(mark,5,1)=\'1\'';
	} elseif($_GET['mode'] == 'm4') {
		$query = ' substring(mark,7,1)=\'1\'';
	} elseif($_GET['mode'] == 'm5') {
		$query = ' substring(mark,9,1)=\'1\'';
	} elseif($_GET['mode'] == 'm6') {
		$query = ' substring(mark,11,1)=\'1\'';
	} elseif($_GET['mode'] == 'm7') {
		$query = ' substring(mark,13,1)=\'1\'';
	} elseif($_GET['mode'] == 'm8') {
		$query = ' substring(mark,15,1)=\'1\'';
	} elseif($_GET['mode'] == 'm9') {
		$query = ' substring(mark,17,1)=\'1\'';
	} elseif($_GET['mode'] == 'm10') {
		$query = ' substring(mark,19,1)=\'1\'';
	} else {
		echo 'STOP in '.__FILE__.' line '.__LINE__;
		exit;
	}
	$query1 = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE ' . $query;
	$rowset = $db->rowset_assoc_limit($query1, $st_no, $cfg['hyouji']);
	foreach ($rowset as $log_data){
		if ($cookie_data[3]) { //adminモード
			$query2 = 'SELECT count(id) FROM '.$db->db_pre.'rank WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query2);
			$log_data['count'] = $count[0];
			$query2 = 'SELECT rank,rev FROM '.$db->db_pre.'rank_counter WHERE id=\''.$log_data['id'].'\'';
			$r_count = $db->single_assoc($query2);
			$log_data['count'] .= '_' . $r_count['rank'];
			$query2 = 'SELECT count(id) FROM '.$db->db_pre.'rev WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query2);
			$log_data['count'] .= '_' . $count[0];
			$log_data['count'] .= '_' . $r_count['rev'];
		}
                $log_data['title'] = str_replace('&amp;amp;', '&', $log_data['title']);

		array_push($log_lines, $log_data);
	}
	$query3 = 'SELECT count(id) FROM '.$db->db_pre.'log WHERE' . $query;
	$num = $db->single_num($query3);
	$log_count[$search_id] = $num[0];
}

if ($_GET['mode'] == 'new') {
    $total_url = $db->log_count($db->db_pre.'log');
    $navi .= '&nbsp;-&nbsp;現在の総登録数:<b>'.$total_url.'</b>サイト';
}

$arg = array($_GET['page'], $log_count[$search_id], $cfg['hyouji'], '&mode='.$_GET['mode'].'&path='.$_GET['path'].'&sort='.$_GET['sort'], $cfg['script']);
$mokuji = mokuji($arg);
require $cfg['temp_path'].'category.html';
?>