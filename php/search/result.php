<?php
require_once $script['lib']['plugin']['cookie'];
// (1)検索結果表示画面(search)
if($Form['mode'] =='search') { // 検索結果表示画面
	$navi = "";
	$search_day = "";
	$log_lines = array();
	$words_a = array();
	$words_o = array();
	$words_n = array();
	$kt_search_list=array();
	
	if(!isset($Form['search_kt'])){$Form['search_kt'] = "";}
	if(!isset($Form['search_kt_ex'])){$Form['search_kt_ex'] = "";}
	if(!isset($Form['search_day'])){$Form['search_day'] = "";}
	
	// 検索オプションをクッキーに記録
	// オプション
	// [0]=>検索条件(a|o)/[1]=>検索式の使用有無(0|1)/[2]=>検索エンジン名(ID)/
	// [3]=>検索エンジン名(表示名)/[4]=>www.(0|1)/[5]=>カテゴリ指定(ID)
	// [6]=>カテゴリ指定(表示名)/[7]=>指定カテゴリ(0|1)/[8]=>日付指定(data)
	// [9]=>日付指定(表示名)/[10]=>日付指定コマンド(data)/[11]=>カテゴリ名検索(0|1)
	$cookie_data = Cookie::get_cookie();
	if(isset($Form['set_option'])) {
		if($Form['set_option'] == 'on') {
			if($Form['method'] == 'and') {
				$cookie_lines[0] = 'a'; // [0]
			} else {
				$cookie_lines[0] = 'o';
			}
			if($Form['use_str'] == 'on') {
				$cookie_lines[1] = '1'; // [1]
			} else {
				$cookie_lines[1] = '0';
			}
			$cookie_lines[2] = $Form['engine']; // [2]
			if($Form['engine'] == 'pre') {
				$cookie_lines[3] = $cfg['search_name'] . 'で'; // [3]
			} else {
				if(preg_match('/<option value=\"{$Form["engine"]}\">(.+)で/', $text['search_form'], $match)) {
					$cookie_lines[3] = $match[1];
				}
			}
			$cookie_lines[3] = str_replace(',','，', $cookie_lines[3]);
			if($Form['www'] == 'on') {
				$cookie_lines[4]='0'; // [4]
			} else {
				$cookie_lines[4] = '1';
			}
			$cookie_lines[5] = $Form['search_kt']; // [5]
			if($Form['search_kt']) {
				$cookie_lines[6] = full_category($Form['search_kt']); // [6]
			} else {
				$cookie_lines[6] = '指定しない';
			}
			if($Form['search_kt_ex'] != '-b_all') {
				$cookie_lines[7]=0; // [7]
			} else {
				$cookie_lines[7] = 1;
			}
			$cookie_lines[8] = $Form['search_day']; // [8]
			if($Form['search_day'] == 'today') {
				$cookie_lines[9] = '本日'; // [9]
			} elseif(preg_match('/^(\d+)-/', $Form['search_day'], $match)) {
				$cookie_lines[9] = $match[1] . '日以内';
			} else {
				$cookie_lines[9] = '指定しない';
			}
			$cookie_lines[10] = $Form['search_day_ex']; // [10]
			$cookie_lines[10] = str_replace(',','，', $cookie_lines[10]);
			if($Form['kt_search'] == 'on') {
				$cookie_lines[11]=0; // [11]
			} else {
				$cookie_lines[11] = 1;
			}
			$cookie_data[5] = implode(',', $cookie_lines);
			$cookie_data[5] = str_replace(';', '', $cookie_data[5]);
			Cookie::set_cookie($cookie_data);
		}
	}

	// 入力値の整形
	if(!isset($Form['engine'])) {
		$Form['engine'] = 'pre';
	}
	if(preg_match('/\D/', $Form['page'])) {
		mes('ページ指定値が不正です', 'ページ指定エラー', 'java');
	}
	if(!isset($Form['sort'])) {
		$Form['sort'] = $cfg['defo_hyouji'];
	}
	if(isset($Form['search_day_ex'])) {
		$Form['search_day'] = $Form['search_day_ex'];
	}
	if(isset($Form['words'])) { // キーワードの結合
		foreach($Form['words'] as $tmp) {
			$Form['word'] .= ' ' . $tmp;
		}
	}
	if(!isset($Form['hyouji'])) {
		$Form['hyouji'] = $cfg['hyouji'];
	}
	
	// キーワードランキング用のデータを取得
	if($cfg['keyrank'] && $Form['page'] == 1){
		set_word();
	}
	// 検索構文の解析
	$Form['word'] = str_replace('　', ' ', $Form['word']);
	$Form['word'] = trim($Form['word'], ' ');
	$search_word = $db->escape_string($Form['word']);

	// 検索式を使う
	if(@$Form['use_str'] == 'on') {
		$words = explode(' ', $search_word);
		if(isset($Form['words'])) {
			$words = array_merge($words, $Form['words']);
		}
		$w_fl = 'and';
		foreach($words as $word) {
			if($word == 'and') {
				$w_fl = 'and';
			} elseif($word == 'or') {
				$w_fl = 'or';
			} elseif($word == 'not') {
				$w_fl = 'not';
			} elseif($w_fl == 'and') {
				array_push($words_a, $word);
				$w_fl = 'and';
			} elseif($w_fl == 'or') {
				array_push($words_o,$word);
				$w_fl = 'and';
			} elseif($w_fl == 'not') {
				array_push($words_n, $word);
				$w_fl = 'and';
			} else {
				array_push($words_a, $word);
			}
		}
	// 検索式を使わない
	} else {
		if($Form['method'] != 'or') {
			$words_a = explode(' ', $search_word);
		} else {
			$words_o = explode(' ', $search_word);
		}
	}

	// 外部検索へ分岐
	if($Form['engine'] != 'pre') {
		if(isset($Form['window'])) {
			$Form['target'] = $Form['window'];
		} else {
			$Form['target'] = '';
		}
		require $script['lib']['metasearch']['result'];
		meta('select');
	}
	if(empty($Form['word']) && empty($Form['search_day'])) { // キーワード・日付指定の両方が未指定のとき
		mes('<b>キーワード</b>か<b>日付指定</b>のいずれかは必ず指定してください', '記入ミス', 'java');
	}
	if(empty($Form['word'])) {
		$Form['kt_search']='off';
	}
 	// 検索処理
	// カテゴリ検索
    if (!isset($Form['kt_search'])) {
        $Form['kt_search'] = '';
    }
	if($Form['kt_search'] != 'off') {
		foreach($ganes as $key=>$val) {
			$kt_fl = 1;
			foreach($words_a as $word) { // and検索
				if(!empty($word)) {
					if(!stristr($val, $word)) {
						$kt_fl = 0;
						break;
					}
				}
			}
			foreach($words_o as $word) { // or検索
				if(!empty($word)) {
					$kt_fl = 0;
					if(stristr($val, $word)) {
						$kt_fl = 1;
						break;
					}
				}
			}
			foreach($words_n as $word) { // not検索
				if(!empty($word)) {
					if(stristr($val, $word)) {
						$kt_fl = 0;
						break;
					}
				}
			}
			if($kt_fl) {
				array_push($kt_search_list, $key);
			}
		}
	}
	if(isset($Form['search_day'])) {
		if(preg_match('/^today-?(\d*)$/', $Form['search_day'], $match)) { // today-x
			if($match[1] > 10000) {
				$match[1] = 0;
			}
			if($match[1]>0) {
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			} else {
				$search_day = '★本日に更新されたサイト<br>';
			}
		} elseif(preg_match('/^(\d+)\-(\d+)$/', $Form['search_day'], $match)) { // [str_day]-[end_day]
			if($match[2] > 0) {
				$search_day = '★' . $match[1] . '日前～' . $match[2] . '日前に更新されたサイト<br>';
			} else {
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			}
		} elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $Form['search_day'], $match)) { // year/mon/day
			$search_day = '★' . $Form['search_day'] . ' に更新されたサイト<br>';
		}
	}
	if (!isset($Form['use_str'])) {
        $Form['use_str'] = '';
    }
	if ($Form['use_str'] == 'on') {
		$Stitle = '検索結果(検索式：'.$Form['word'].')';
	} else {
		if (!isset($Form['method'])) {
			$Form['method'] = 'and';
		}
		$Stitle = '検索結果(検索ワード：'.$Form['word'].' / 検索条件：'.$Form['method'].')';
	}
	// 対象全配列を@writeに入れる
	$i = 0;
	// カテゴリ指定部分
	if (isset($Form['search_kt']) && !empty($Form['search_kt'])) {
		if(strstr($Form['search_kt'], '-b_all')) {
			$Form['search_kt'] = str_replace('-b_all', '', $Form['search_kt']);
			$Form['search_kt_ex'] = '-b_all';
		}
		list($oya_kt,) = explode('/', $Form['search_kt']);
		$oya_kt .= '/';
		if(!$ganes[$oya_kt]) {
			mes('カテゴリ指定が不正です', 'カテゴリ指定エラー', 'java');
		}
		if($Form['search_kt_ex']) {
			$category = 'category LIKE \'%&'.$Form['search_kt_ex'].'%\'';
		} else {
			$category = 'category LIKE \'%&'.$Form['search_kt'].'%\'';
		}
	}
	$where = "";
	if(count($words_a) >= 0) { // and検索
		foreach($words_a as $word) {
			$where .= " AND (id LIKE '%{$word}%' OR title LIKE '%{$word}%' OR message LIKE '%{$word}%' OR comment LIKE '%{$word}%' OR keywd LIKE '%{$word}%' OR url LIKE '%{$word}%')";
		}
	}
	if(count($words_o) >= 0) { // or検索
		foreach($words_o as $word) {
			$where .= " OR (id LIKE '%{$word}%' OR title LIKE '%{$word}%' OR message LIKE '%{$word}%' OR comment LIKE '%{$word}%' OR keywd LIKE '%{$word}%' OR url LIKE '%{$word}%')";
		}
	}
	if(count($words_n) >= 0) { // not検索
		foreach($words_n as $word) {
			$where .= " AND (id collate NOT LIKE '%{$word}%' AND title collate NOT LIKE '%{$word}%' AND message collate NOT LIKE '%{$word}%' AND comment NOT LIKE '%{$word}%' AND keywd collate NOT LIKE '%{$word}%' AND url NOT LIKE '%{$word}%')";
		}
	}
	if($where) {
		$where = substr($where, 4);
	}
	// 日付検索部分
	if (isset($Form['search_day']) && !empty($Form['search_day'])) {
		if(preg_match('/^today-?(\d*)$/', $Form['search_day'], $match)) { // today-x
			if($match[1] > 10000) {
				$match[1] = 0;
			}
			$bf_day = time() - 86400 * $match[1];
			$ltime = 'stamp > \''.$bf_day.'\'';
		} elseif(preg_match('/^(\d+)\-(\d+)$/', $Form['search_day'], $match)) { // [str_day]-[end_day]
			$str_times = time() - 86400 * $match[1];
			$end_times = time() - 86400 * $match[2];
			$ltime = 'stamp BETWEEN \''.$str_times.'\' AND \''.$end_times.'\'';
		} elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $Form['search_day'], $match)) { // year/mon/day
			$month = array('01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December');
			$mon = $month[$match[2]];
			$str_times = strtotime ("{$match[3]} {$mon} {$match[1]}");
			$end_times = $str_times + 86400;
			$ltime = 'stamp BETWEEN \''.$str_times.'\' AND \''.$end_times.'\'';
		} else {
			mes('日付指定のコマンドが正しくありません', 'エラー', 'java');
		}
	}
	// データ格納部分
	if($Form['sort'] == 'id_new') {
		$order = 'ORDER BY id DESC';
	} elseif($Form['sort'] == 'id_old') {
		$order = 'ORDER BY id';
	} elseif($Form['sort'] == 'time_new') {
		$order = ' ORDER BY stamp DESC';
	} elseif($Form['sort'] == 'time_old') {
		$order = ' ORDER BY stamp';
	} elseif($Form['sort'] == 'ac_new') {
		$order = ' ORDER BY title';
	} elseif($Form['sort'] == 'ac_old') {
		$order = ' ORDER BY title DESC';
	} else {
		$order = " ORDER BY char_length(replace(replace(mark,'_',''),'0','')) DESC, id DESC"; // mark
	}
	$query = '';
	if(isset($category)) {
		$query = ' ('.$category.')';
	}
	if($query && $where) {
		$query .= ' AND ';
	}
	if($where) {
		$query .= '('.$where.')';
	}
	if($query && isset($ltime)) {
		$query .= ' AND ';
	}
	if(isset($ltime)) {
		$query .= '('.$ltime.')';
	}
	$row = $db->single_num('SELECT count(id) FROM {prefix}log WHERE '.$query);
	$Clog = $row[0];
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM {prefix}log WHERE '.$query.$order;
	$st_no = $cfg['hyouji'] * ($Form['page'] - 1);
	// 検索処理実行
	$rowset = $db->rowset_assoc_limit($query, $st_no, $cfg['hyouji']);
	$time = time();
	$start = $time - $cfg['rank_kikan'] * 86400;
	$end = $time;
	foreach($rowset as $log_data) {
		
		//Get IN,OUT Rank(Request By.Dan 2023/02/19)
		$query = 'SELECT rank,rev FROM {prefix}rank_counter WHERE id=\''.$log_data['id'].'\'';
		$r_count = $db->single_assoc($query);
		$log_data['in_count'] = $r_count['rev'];//IN
		$log_data['out_count'] = $r_count['rank'];//OUT		
		
		if($cookie_data[3]) { // adminモード
			$query = 'SELECT COUNT(id) FROM {prefix}rank WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] = $count[0];
			$query = 'SELECT rank,rev FROM {prefix}rank_counter WHERE id=\''.$log_data['id'].'\'';
			$r_count = $db->single_assoc($query);
			$log_data['count'] .= '_' . $r_count['rank'];
			$query = 'SELECT COUNT(id) FROM {prefix}rev WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] .= '_' . $count[0];
			$log_data['count'] .= '_' . $r_count['rev'];
		}
		array_push($log_lines, $log_data);
	}
	unset($rowset);
	// 結果表示
	header ('Content-type: text/html; charset=UTF-8');
	require $path_set['template']['root'] . '/search.html';
}

// (5)外部検索エンジンへのリンク一覧を表示(&PR_mata_page)
function PR_meta_page($location_list,$Caria) {
	global $Form;
	$T_flag = 1;
        $w = '';
	if($Caria == "pc"){$w = '<table width="90%" cellpadding="8">'."\n";}
	foreach($location_list as $list) {
		list($Dengine, $Durl) = explode('<>', $list);
		switch($Caria){
			case "pc":
				if($T_flag == 5) {
					$w.= '</tr>'."\n";
					$T_flag=1;
				}
				if($T_flag == 1) {
					$w.= '<tr>'."\n";
				}
				$w.= '<th><a href="'.$Durl.'"';
				if(isset($Form['target'])) {
					$w.= ' target="'.$Form['target'].'"';
				}
				$w.= '><font size="+1">'.$Dengine.'</font></a></th>';
			break;
			case "phone":
				$w.= '<a href="'.$Durl.'"';
				if(isset($Form['target'])) {
					$w.= ' target="'.$Form['target'].'"';
				}
				$w.= '><font size="+1">'.$Dengine.'</font></a><br />';
			break;
		}
		$T_flag++;
	}
	if($Caria == "pc"){
		if($T_flag != 2) {
			$w.= '</tr>'."\n";
		}
		$w.= '</table>'."\n";
	}
	echo $w;
	return true;
}

// (6)キーワードをデータベースに記録(&set_word)
function set_word() {
	global $db,$Form;
	$time = time();
	$keyword = $Form['word'];
	if(strlen($keyword) < 50) {
		$keyword = str_replace('　', ' ', $keyword);
		$keyword = mb_strtolower($keyword, 'UTF-8');
		$keyword = $db->escape_string($keyword);
		$keywords = explode(' ', $keyword);
		if(count($keywords) > 0) {
			foreach($keywords as $i) {
				if($i && $i != 'and' && $i != 'or' && $i != 'not') {
					$i = str_replace("\n", '', $i);
					$query = 'SELECT word FROM {prefix}key WHERE word=\''.$i.'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - 24 * 3600);
					$tmp = $db->single_num($query);
					if(!$tmp) {
						$query = 'INSERT INTO {prefix}key (word, time, ip) VALUES (\''.$i.'\', \''.$time.'\', \''.$_SERVER['REMOTE_ADDR'].'\')';
						$db->query($query);
					}
				}
			}
		}
	}
}

?>