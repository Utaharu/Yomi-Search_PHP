<?php
require_once $script['lib']['plugin']['cookie'];
require_once $script['lib']['plugin']['navigation'];
/**
 * ランキング表示画面
 */
$query = 'SELECT name, value FROM {prefix}cfg_reg WHERE name = \'Mbana_w\' OR name = \'Mbana_h\'';
$rowset = $db->rowset_num($query);
foreach ($rowset as $tmp) {
    $cfg_reg[$tmp[0]] = $tmp[1];
}
$cookie_data = Cookie::get_cookie();
$Eref = "";
if(isset($_SERVER['HTTP_REFERER']) and $_SERVER['HTTP_REFERER']){$Eref = urlencode($_SERVER['HTTP_REFERER']);}
$log_lines = array();
$end_no = $Form['page'] * $cfg['hyouji'];
$str_no = $end_no - $cfg['hyouji'] + 1;
$time = time();

// path取得
if (!isset($Form['path']) || !checkSQLWord($Form['path'])) {
    $Form['path'] = '';
}
$rank_ys  = '<a href="'.$cfg['rank'].'?mode=rank_ys&path='.$Form['path'].'">現在の人気ランキング</a>';
$rank_bf  = '<a href="'.$cfg['rank'].'?mode=rank_bf&path='.$Form['path'].'">前回の人気ランキング</a>';
$rank_rui = '<a href="'.$cfg['rank'].'?mode=rank_rui&path='.$Form['path'].'">人気ランキング(累計)</a>';
$rev      = '<a href="'.$cfg['rank'].'?mode=rev&path='.$Form['path'].'">現在のアクセスランキング</a>';
$rev_bf   = '<a href="'.$cfg['rank'].'?mode=rev_bf&path='.$Form['path'].'">前回のアクセスランキング</a>';
$rev_rui  = '<a href="'.$cfg['rank'].'?mode=rev_rui&path='.$Form['path'].'">アクセスランキング(累計)</a>';


if($Form['mode'] == 'rank_bf') {
	$Stitle = '前回の人気ランキング';
	$rank_menu = ' - ' . $rank_ys . ' - ' . $rank_rui;
	$start = $time - $cfg['rank_kikan'] * 172800;
	$end = $time - $cfg['rank_kikan'] * 86400 + 1;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rank';
} elseif($Form['mode'] == 'rank_rui') {
	$Stitle = '人気ランキング(累計)';
	$rank_menu = ' - ' . $rank_ys . ' - ' . $rank_bf;
	$last_mod = '　-　' . date('Y/m/d H:i', $time);
	$table = 'rank';
} elseif($Form['mode'] == 'rev') {
	$Stitle = 'アクセスランキング';
	$rank_menu = ' - ' . $rev_bf . ' - ' . $rev_rui;
	$start = $time - $cfg['rev_kikan'] * 86400;
	$end = $time;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rev';
} elseif($Form['mode'] == 'rev_bf') {
	$Stitle = '前回のアクセスランキング';
	$rank_menu = ' - ' . $rev . ' - ' . $rev_rui;
	$start = $time - $cfg['rev_kikan'] * 172800;
	$end = $time - $cfg['rev_kikan'] * 86400 + 1;
	$last_mod = date('Y/m/d H:i', $start) . '　-　' . date('Y/m/d H:i', $end);
	$table = 'rev';
} elseif($Form['mode'] == 'rev_rui') {
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
if ($Form['path']) { //各カテゴリの場合
    $query = 'SELECT title, comment FROM {prefix}category WHERE path = \'' . $Form['path'] . '\' LIMIT 1';
    $row = $db->single_assoc($query);
	if($row){
		$title = $row['title'];
		$guide = $row['comment'];
		$Stitle .= ' - ' . $title;
	}
}
if($Form['mode'] != 'rev_rui' and $Form['mode'] != 'rank_rui') {
	if(!$Form['path']) {
		$query = 'SELECT count(id) AS pt,id FROM {prefix}'.$table.' WHERE time BETWEEN '.$start.' AND '.$end.' GROUP BY id HAVING pt >= '.$min.' ORDER BY pt DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,count(*) AS pt FROM {prefix}log AS l,{prefix}'.$table.' AS t WHERE l.id=t.id AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	} else {
		$query = 'SELECT count(l.id) AS pt,t.id FROM {prefix}log AS l,{prefix}'.$table.' AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$Form['path'].'&%\' AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,count(*) AS pt FROM {prefix}log AS l,{prefix}'.$table.' AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$Form['path'].'&%\' AND t.time BETWEEN '.$start.' AND '.$end.' GROUP BY t.id HAVING pt >= '.$min.' ORDER BY pt DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	}
} else {
	if(!$Form['path']) {
		$query = 'SELECT id,'.$table.' AS pt FROM {prefix}rank_counter WHERE '.$table.' >= '.$min.' ORDER BY '.$table.' DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,t.'.$table.' AS pt FROM {prefix}log AS l,{prefix}rank_counter AS t WHERE l.id=t.id AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	} else {
		$query = 'SELECT t.id,t.'.$table.' AS pt FROM {prefix}log AS l,{prefix}rank_counter AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$Form['path'].'&%\' AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC LIMIT '.$best;
		$count = $db->rowset_assoc($query);
		$log_count = count($count);
		$query = 'SELECT l.*,t.'.$table.' AS pt FROM {prefix}log AS l,{prefix}rank_counter AS t WHERE l.id=t.id AND l.category LIKE \'%&'.$Form['path'].'&%\' AND t.'.$table.' >= '.$min.' ORDER BY t.'.$table.' DESC';
		$log_lines = $db->rowset_assoc_limit($query, $str_no - 1, $cfg['hyouji']);
	}
}

//Get IN,OUT Rank(Request By.Dan 2023/02/19)
if(is_array($log_lines) and $log_count > 0){
	foreach($log_lines as $log_key=>$log_val){
		if($log_val){
			$query = 'SELECT rank,rev FROM {prefix}rank_counter WHERE id=\''.$log_val['id'].'\'';
			$r_count = $db->single_assoc($query);
			$log_lines[$log_key]['in_count'] = $r_count['rev'];//IN
			$log_lines[$log_key]['out_count'] = $r_count['rank'];//OUT
		}
	}
}

$cut = $time - $cfg['rank_kikan'] * 172800;
$query = 'DELETE FROM {prefix}rank WHERE time<'.$cut;
$db->query($query);

$cut = $time - $cfg['rev_kikan'] * 172800;
$query = 'DELETE FROM {prefix}rev WHERE time<'.$cut;
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
$tmp = array($Form['page'], $log_count, $cfg['hyouji'], '&mode='.$Form['mode'].'&path='.$Form['path'], $cfg['rank']);
$mokuji = Navigation::Page($tmp);
$navi = '';
if($Form['path']) {
    $navi = Navigation::Bredcrumbs($Form['path']) . '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$Form['path'].'">'.$title.'</a>&nbsp;&gt;&nbsp;';
}

require $path_set['template']['root'] . '/rank.html';
?>