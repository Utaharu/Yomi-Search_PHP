<?php
/**
 * ランキング表示画面
 */
$query = 'SELECT name, value FROM '.$db->db_pre . 'cfg_reg WHERE name = \'Mbana_w\' OR name = \'Mbana_h\'';
$rowset = $db->rowset_num($query);
foreach ($rowset as $tmp) {
    $cfg_reg[$tmp[0]] = $tmp[1];
}
$cookie_data = get_cookie();
$Eref = urlencode($_SERVER['HTTP_REFERER']);
$log_lines = array();
$end_no = $_GET['page'] * $cfg['hyouji'];
$str_no = $end_no - $cfg['hyouji'] + 1;
$time = time();

// path取得
if (!isset($_GET['path']) || !checkSQLWord($_GET['path'])) {
    $_GET['path'] = '';
}
$rank_ys  = '<a href="'.$cfg['rank'].'?mode=rank_ys&path='.$_GET['path'].'">現在の人気ランキング</a>';
$rank_bf  = '<a href="'.$cfg['rank'].'?mode=rank_bf&path='.$_GET['path'].'">前回の人気ランキング</a>';
$rank_rui = '<a href="'.$cfg['rank'].'?mode=rank_rui&path='.$_GET['path'].'">人気ランキング(累計)</a>';
$rev      = '<a href="'.$cfg['rank'].'?mode=rev&path='.$_GET['path'].'">現在のアクセスランキング</a>';
$rev_bf   = '<a href="'.$cfg['rank'].'?mode=rev_bf&path='.$_GET['path'].'">前回のアクセスランキング</a>';
$rev_rui  = '<a href="'.$cfg['rank'].'?mode=rev_rui&path='.$_GET['path'].'">アクセスランキング(累計)</a>';


if($_GET['mode'] == 'rank_bf') {
	$Stitle = '前回の人気ランキング';
	$rank_menu = ' - ' . $rank_ys . ' - ' . $rank_rui;
	$start = $time - $cfg['rank_kikan'] * 172800;
	$end = $time - $cfg['rank_kikan'] * 86400 + 1;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rank';
} elseif($_GET['mode'] == 'rank_rui') {
	$Stitle = '人気ランキング(累計)';
	$rank_menu = ' - ' . $rank_ys . ' - ' . $rank_bf;
	$last_mod = '　-　' . date('Y/m/d H:i', $time);
	$table = 'rank';
} elseif($_GET['mode'] == 'rev') {
	$Stitle = 'アクセスランキング';
	$rank_menu = ' - ' . $rev_bf . ' - ' . $rev_rui;
	$start = $time - $cfg['rev_kikan'] * 86400;
	$end = $time;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rev';
} elseif($_GET['mode'] == 'rev_bf') {
	$Stitle = '前回のアクセスランキング';
	$rank_menu = ' - ' . $rev . ' - ' . $rev_rui;
	$start = $time - $cfg['rev_kikan'] * 172800;
	$end = $time - $cfg['rev_kikan'] * 86400 + 1;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rev';
} elseif($_GET['mode'] == 'rev_rui') {
	$Stitle = 'アクセスランキング(累計)';
	$rank_menu = ' - ' . $rev . ' - ' . $rev_bf;
	$last_mod = '　-　' . date('Y/m/d H:i', $time);
	$table = 'rev';
} else {
	$Stitle = '人気ランキング';
	$rank_menu = ' - ' . $rank_bf . ' - ' . $rank_rui;
	$start = $time - $cfg['rank_kikan'] * 86400;
	$end = $time;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rank';
}
$min = $cfg[$table.'_min'];
$best = $cfg[$table.'_best'];
if ($_GET['path']) { //各カテゴリの場合
    $query = 'SELECT title, comment FROM '.$db->db_pre.'category WHERE path = \'' . $_GET['path'] . '\' LIMIT 1';
    $row = $db->single_assoc($query);
    $title = $row['title'];
    $guide = $row['comment'];
    $Stitle .= ' - ' . $title;
}
if($_GET['mode'] != 'rev_rui' and $_GET['mode'] != 'rank_rui') {
	if(!$_GET['path']) {
		$query = 'SELECT count(id) AS pt,id FROM '.$db->db_pre.$table.' WHERE time BETWEEN '.$start.' AND '.$end.' GROUP BY id HAVING pt >= '.$min.' ORDER BY pt DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,count(*) AS pt FROM '.$db->db_pre.'log AS l,'.$db->db_pre.$table.' AS t WHERE l.id=t.id AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	} else {
		$query = 'SELECT count(l.id) AS pt,t.id FROM '.$db->db_pre.'log AS l,'.$db->db_pre.$table.' AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$_GET['path'].'&%\' AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,count(*) AS pt FROM '.$db->db_pre.'log AS l,'.$db->db_pre.$table.' AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$_GET['path'].'&%\' AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	}
} else {
	if(!$_GET['path']) {
		$query = 'SELECT id,'.$table.' AS pt FROM '.$db->db_pre.'rank_counter WHERE '.$table.' >= '.$min.' ORDER BY '.$table.' DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,t.'.$table.' AS pt FROM '.$db->db_pre.'log AS l,'.$db->db_pre.'rank_counter AS t WHERE l.id=t.id AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	} else {
		$query = 'SELECT t.id,t.'.$table.' AS pt FROM '.$db->db_pre.'log AS l,'.$db->db_pre.'rank_counter AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$_GET['path'].'&%\' AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,t.'.$table.' AS pt FROM '.$db->db_pre.'log AS l,'.$db->db_pre.'rank_counter AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$_GET['path'].'&%\' AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	}
}

$cut = $time - $cfg['rank_kikan'] * 172800;
$query = 'DELETE FROM '.$db->db_pre.'rank WHERE time<'.$cut;
$db->query($query);

$cut = $time - $cfg['rev_kikan'] * 172800;
$query = 'DELETE FROM '.$db->db_pre.'rev WHERE time<'.$cut;
$db->query($query);

$pre_rank = 0;
$pre_point = 0;
for ($i = 0; $i < $log_count; $i++) {
	if ($count[$i]['pt'] != $pre_point) {
		$pre_rank = $i + 1;
		$pre_point = $count[$i]['pt'];
	}
	$rank[$count[$i]['id']] = $pre_rank;
}
$tmp = array($_GET['page'], $log_count, $cfg['hyouji'], '&mode='.$_GET['mode'].'&path='.$_GET['path'], $cfg['rank']);
$mokuji = mokuji($tmp);
$navi = '';
if($_GET['path']) {
    $navi = navi_bar($_GET['path']) . '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$_GET['path'].'">'.$title.'</a>&nbsp;&gt;&nbsp;';
}
header('Content-type: text/html; charset=UTF-8');
require $cfg['temp_path'] . 'rank.html';
?>