<?php
// (1)検索結果表示画面(search)
if($_GET['mode'] =='search') { // 検索結果表示画面
	$log_lines = array();
	$words_a = array();
	$words_o = array();
	$words_n = array();
	$kt_search_list=array();
	// 検索オプションをクッキーに記録
	// オプション
	// [0]=>検索条件(a|o)/[1]=>検索式の使用有無(0|1)/[2]=>検索エンジン名(ID)/
	// [3]=>検索エンジン名(表示名)/[4]=>www.(0|1)/[5]=>カテゴリ指定(ID)
	// [6]=>カテゴリ指定(表示名)/[7]=>指定カテゴリ(0|1)/[8]=>日付指定(data)
	// [9]=>日付指定(表示名)/[10]=>日付指定コマンド(data)/[11]=>カテゴリ名検索(0|1)
	$cookie_data = get_cookie();
	if(isset($_GET['set_option'])) {
		if($_GET['set_option'] == 'on') {
			if($_GET['method'] == 'and') {
				$cookie_lines[0] = 'a'; // [0]
			} else {
				$cookie_lines[0] = 'o';
			}
			if($_GET['use_str'] == 'on') {
				$cookie_lines[1] = '1'; // [1]
			} else {
				$cookie_lines[1] = '0';
			}
			$cookie_lines[2] = $_GET['engine']; // [2]
			if($_GET['engine'] == 'pre') {
				$cookie_lines[3] = $cfg['search_name'] . 'で'; // [3]
			} else {
				if(preg_match('/<option value=\"{$_GET["engine"]}\">(.+)で/', $text['search_form'], $match)) {
					$cookie_lines[3] = $match[1];
				}
			}
			$cookie_lines[3] = str_replace(',','，', $cookie_lines[3]);
			if($_GET['www'] == 'on') {
				$cookie_lines[4]='0'; // [4]
			} else {
				$cookie_lines[4] = '1';
			}
			$cookie_lines[5] = $_GET['search_kt']; // [5]
			if($_GET['search_kt']) {
				$cookie_lines[6] = full_category($_GET['search_kt']); // [6]
			} else {
				$cookie_lines[6] = '指定しない';
			}
			if($_GET['search_kt_ex'] != '-b_all') {
				$cookie_lines[7]=0; // [7]
			} else {
				$cookie_lines[7] = 1;
			}
			$cookie_lines[8] = $_GET['search_day']; // [8]
			if($_GET['search_day'] == 'today') {
				$cookie_lines[9] = '本日'; // [9]
			} elseif(preg_match('/^(\d+)-/', $_GET['search_day'], $match)) {
				$cookie_lines[9] = $match[1] . '日以内';
			} else {
				$cookie_lines[9] = '指定しない';
			}
			$cookie_lines[10] = $_GET['search_day_ex']; // [10]
			$cookie_lines[10] = str_replace(',','，', $cookie_lines[10]);
			if($_GET['kt_search'] == 'on') {
				$cookie_lines[11]=0; // [11]
			} else {
				$cookie_lines[11] = 1;
			}
			$cookie_data[5] = implode(',', $cookie_lines);
			$cookie_data[5] = str_replace(';', '', $cookie_data[5]);
			set_cookie($cookie_data);
		}
	}

	// 入力値の整形
	if(!isset($_GET['engine'])) {
		$_GET['engine'] = 'pre';
	}
	if(preg_match('/\D/', $_GET['page'])) {
		mes('ページ指定値が不正です', 'ページ指定エラー', 'java');
	}
	if(!isset($_GET['sort'])) {
		$_GET['sort'] = $cfg['defo_hyouji'];
	}
	if(isset($_GET['search_day_ex'])) {
		$_GET['search_day'] = $_GET['search_day_ex'];
	}
	if(isset($_GET['words'])) { // キーワードの結合
		foreach($_GET['words'] as $tmp) {
			$_GET['word'] .= ' ' . $tmp;
		}
	}
	if(!isset($_GET['hyouji'])) {
		$_GET['hyouji'] = $cfg['hyouji'];
	}
	
	// キーワードランキング用のデータを取得
	if($cfg['keyrank'] && $_GET['page'] == 1){
		set_word();
	}
	// 検索構文の解析
	$_GET['word'] = str_replace('　', ' ', $_GET['word']);
	$_GET['word'] = trim($_GET['word'], ' ');
	$search_word = $db->escape_string($_GET['word']);

	// 検索式を使う
	if(@$_GET['use_str'] == 'on') {
		$words = explode(' ', $search_word);
		if(isset($_GET['words'])) {
			$words = array_merge($words, $_GET['words']);
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
		if($_GET['method'] != 'or') {
			$words_a = explode(' ', $search_word);
		} else {
			$words_o = explode(' ', $search_word);
		}
	}

	// 外部検索へ分岐
	if($_GET['engine'] != 'pre') {
		if(isset($_GET['window'])) {
			$_GET['target'] = $_GET['window'];
		} else {
			$_GET['target'] = '';
		}
		require $cfg['sub_path'] . 'meta_ys.php';
		meta('select');
	}
	if(empty($_GET['word']) && empty($_GET['search_day'])) { // キーワード・日付指定の両方が未指定のとき
		mes('<b>キーワード</b>か<b>日付指定</b>のいずれかは必ず指定してください', '記入ミス', 'java');
	}
	if(empty($_GET['word'])) {
		$_GET['kt_search']='off';
	}
 	// 検索処理
	// カテゴリ検索
    if (!isset($_GET['kt_search'])) {
        $_GET['kt_search'] = '';
    }
	if($_GET['kt_search'] != 'off') {
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
	if(isset($_GET['search_day'])) {
		if(preg_match('/^today-?(\d*)$/', $_GET['search_day'], $match)) { // today-x
			if($match[1] > 10000) {
				$match[1] = 0;
			}
			if($match[1]>0) {
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			} else {
				$search_day = '★本日に更新されたサイト<br>';
			}
		} elseif(preg_match('/^(\d+)\-(\d+)$/', $_GET['search_day'], $match)) { // [str_day]-[end_day]
			if($match[2] > 0) {
				$search_day = '★' . $match[1] . '日前～' . $match[2] . '日前に更新されたサイト<br>';
			} else {
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			}
		} elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $_GET['search_day'], $match)) { // year/mon/day
			$search_day = '★' . $_GET['search_day'] . ' に更新されたサイト<br>';
		}
	}
	if (!isset($_GET['use_str'])) {
        $_GET['use_str'] = '';
    }
	if ($_GET['use_str'] == 'on') {
		$Stitle = '検索結果(検索式：'.$_GET['word'].')';
	} else {
		if (!isset($_GET['method'])) {
			$_GET['method'] = 'and';
		}
		$Stitle = '検索結果(検索ワード：'.$_GET['word'].' / 検索条件：'.$_GET['method'].')';
	}
	// 対象全配列を@writeに入れる
	$i = 0;
	// カテゴリ指定部分
	if (isset($_GET['search_kt']) && !empty($_GET['search_kt'])) {
		if(strstr($_GET['search_kt'], '-b_all')) {
			$_GET['search_kt'] = str_replace('-b_all', '', $_GET['search_kt']);
			$_GET['search_kt_ex'] = '-b_all';
		}
		list($oya_kt,) = explode('/', $_GET['search_kt']);
		$oya_kt .= '/';
		if(!$ganes[$oya_kt]) {
			mes('カテゴリ指定が不正です', 'カテゴリ指定エラー', 'java');
		}
		if($_GET['search_kt_ex']) {
			$category = 'category LIKE \'%&'.$_GET['search_kt_ex'].'%\'';
		} else {
			$category = 'category LIKE \'%&'.$_GET['search_kt'].'%\'';
		}
	}
	// ワード検索部分
	if(count($words_a) >= 0) { // and検索
		foreach($words_a as $word) {
			$where .= ' AND (title LIKE \'%'.$word.'%\' OR message LIKE \'%'.$word.'%\' OR comment LIKE \'%'.$word.'%\' OR keywd LIKE \'%'.$word.'%\' OR url LIKE \'%'.$word.'%\')';
		}
	}
	if(count($words_o) >= 0) { // or検索
		foreach($words_o as $word) {
			$where .= ' OR (title LIKE  \'%'.$word.'%\' OR message LIKE  \'%'.$word.'%\' OR comment LIKE  \'%'.$word.'%\' OR keywd LIKE  \'%'.$word.'%\' OR url LIKE  \'%'.$word.'%\')';
		}
	}
	if(count($words_n) >= 0) { // not検索
		foreach($words_n as $word) {
			$where .= ' AND (title NOT LIKE  \'%'.$word.'%\' AND message NOT LIKE  \'%'.$word.'%\' AND comment NOT LIKE  \'%'.$word.'%\' AND keywd NOT LIKE  \'%'.$word.'%\' AND url NOT LIKE  \'%'.$word.'%\')';
		}
	}
	if($where) {
		$where = substr($where, 4);
	}
	// 日付検索部分
	if (isset($_GET['search_day']) && !empty($_GET['search_day'])) {
		if(preg_match('/^today-?(\d*)$/', $_GET['search_day'], $match)) { // today-x
			if($match[1] > 10000) {
				$match[1] = 0;
			}
			$bf_day = time() - 86400 * $match[1];
			$ltime = 'stamp > \''.$bf_day.'\'';
		} elseif(preg_match('/^(\d+)\-(\d+)$/', $_GET['search_day'], $match)) { // [str_day]-[end_day]
			$str_times = time() - 86400 * $match[1];
			$end_times = time() - 86400 * $match[2];
			$ltime = 'stamp BETWEEN \''.$str_times.'\' AND \''.$end_times.'\'';
		} elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $_GET['search_day'], $match)) { // year/mon/day
			$month = array('01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December');
			$mon = $month[$match[2]];
			$str_times = strtotime ("{$match[3]} {$mon} {$match[1]}");
			$end_times = $str_times + 86400;
			$ltime = "stamp BETWEEN '{$str_times}' AND '{$end_times}'";
		} else {
			mes('日付指定のコマンドが正しくありません', 'エラー', 'java');
		}
	}
	// データ格納部分
	if($_GET['sort'] == 'id_new') {
		$order = 'ORDER BY id DESC';
	} elseif($_GET['sort'] == 'id_old') {
		$order = 'ORDER BY id';
	} elseif($_GET['sort'] == 'time_new') {
		$order = ' ORDER BY stamp DESC';
	} elseif($_GET['sort'] == 'time_old') {
		$order = ' ORDER BY stamp';
	} elseif($_GET['sort'] == 'ac_new') {
		$order = ' ORDER BY title';
	} elseif($_GET['sort'] == 'ac_old') {
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
	$row = $db->single_num('SELECT count(id) FROM '.$db->db_pre.'log WHERE '.$query);
	$Clog = $row[0];
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE '.$query.$order;
	$st_no = $cfg['hyouji'] * ($_GET['page'] - 1);
	// 検索処理実行
	$rowset = $db->rowset_assoc_limit($query, $st_no, $cfg['hyouji']);
	$time = time();
	$start = $time - $cfg['rank_kikan'] * 86400;
	$end = $time;
	foreach($rowset as $log_data) {
		if($cookie_data[3]) { // adminモード
			$query = 'SELECT COUNT(id) FROM '.$db->db_pre.'rank WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] = $count[0];
			$query = 'SELECT rank,rev FROM '.$db->db_pre.'rank_counter WHERE id=\''.$log_data['id'].'\'';
			$r_count = $db->single_assoc($query);
			$log_data['count'] .= '_' . $r_count['rank'];
			$query = 'SELECT COUNT(id) FROM '.$db->db_pre.'rev WHERE time BETWEEN '.$start.' AND '.$end.' AND id=\''.$log_data['id'].'\'';
			$count = $db->single_num($query);
			$log_data['count'] .= '_' . $count[0];
			$log_data['count'] .= '_' . $r_count['rev'];
		}
		array_push($log_lines, $log_data);
	}
	unset($rowset);
	// 結果表示
	header ('Content-type: text/html; charset=UTF-8');
	require $cfg['temp_path'] . 'search.html';
}
?>