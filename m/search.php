<?php
// エラーレポート設定
require '../php/config4debug.php';

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

foreach($_GET as $key => $val) {
    if(is_array($_GET[$key])) {
        $_GET[$key] = array_map('checkSQLWord', $_GET[$key]);
    } else {
        if(checkSQLWord($val)==false) {
            $_GET[$key] = '';
        }
    }
}

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
}
?>
<?php $_GET = array_map("htmlspecialchars", $_GET); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1994 16:00:00 GMT">
<title><?php echo $Stitle; ?>| <?php echo $cfg['search_name']; ?></title>
<meta name="keywords" content="<?php echo $cfg['ver']; ?>,検索エンジン,検索画面" />
<meta name="description" content="<?php echo $cfg['ver']; ?>の検索エンジンの検索画面です" />
<link media="screen" rel="stylesheet" href="css/main.css" type="text/css">
</head>
<body>
<a name="top"></a>
<!-- Menu Bar Output -->
<table width="100%" class="table_menu_bar">
    <tr>
        <td style="text-align:center;">
        <?php printHeaderAd(); ?>
        </td>
    </tr>
  <tr>
    <td><?php echo $text['menu_bar']; ?></td>
  </tr>
</table>
<!-- /Menu Bar Output -->
<!-- Header Space Output -->
<?php echo $text['head_sp']; ?>
<!-- /Header Space Output -->
<!-- Navigation Bar Output -->
<table width="100%" class="table_navigation_bar">
  <tr>
    <td><a href="<?php echo $cfg['home']; ?>">ホーム</a>&nbsp;&gt;&nbsp;<?php echo $navi; ?><strong><?php echo $Stitle; ?></strong></td>
  </tr>
</table>
<!-- /Navigation Bar Output -->
<?php echo $search_day; ?>
<!-- ページ中段の検索フォーム -->
<table width="100%" class="table_searchform">
  <form action="<?php echo $cfg['search']; ?>" method="get">
  <tr>
    <td align="center">
	<input type="hidden" name="mode" value="search">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
	<input type="hidden" name="open_type" value="0">
	<input type="hidden" name="hyouji" value="30">
	<img src="<?php echo $cfg['img_path_url']; ?>search.gif" alt="検索" align="absbottom" title="検索">&nbsp;<input name="word" type="text" class="form" value="<?php echo htmlspecialchars($_GET['word']); ?>" size="20">&nbsp;<input type="submit" class="form" value="検索">&nbsp;
	<select name="method" class="form">
		<option value="and" selected>AND</option>
		<option value="or">OR</option>
	</select>
    </td>
  </tr>
  </form>
</table>
<?php
// カテゴリ検索結果を表示
if(@$kt_search_list && $_GET['page'] == 1) {
	?>
    <hr><ul>▼以下のカテゴリと一致しました(<?php echo count($kt_search_list); ?>件)<br><br>
    <?php
    foreach ($kt_search_list as $kt) {
		?><a href="<?php echo $cfg['script']; ?>?mode=dir&amp;path=<?php echo $kt; ?>"><?php echo full_category($kt); ?></a><br><br><?php
	}
	?></ul><?php
}
// ワード検索結果を表示
if($Clog) {
	?>
<!-- データがある場合 -->
<!-- 表示方法選択フォーム -->
<form action="<?php echo $cfg['search']; ?>" method="get">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="word" value="<?php echo htmlspecialchars($_GET['word']); ?>">
	<input type="hidden" name="engine" value="<?php echo htmlspecialchars($_GET['engine']); ?>">
	<input type="hidden" name="mode" value="<?php echo htmlspecialchars($_GET['mode']); ?>">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="search_kt" value="<?php echo htmlspecialchars($_GET['search_kt']); ?>">
	<input type="hidden" name="search_kt_ex" value="<?php echo htmlspecialchars($_GET['search_kt_ex']); ?>">
	<input type="hidden" name="search_day" value="<?php echo htmlspecialchars($_GET['search_day']); ?>">
	<input type="hidden" name="use_str" value="<?php echo htmlspecialchars($_GET['use_str']); ?>">
	<input type="hidden" name="method" value="<?php echo htmlspecialchars($_GET['method']); ?>">
[現在の表示：<b>
<?php
if(!@$_GET['sort']) {
	switch(@$cfg['defo_hyouji']) {
		case "time_new": echo "更新日の新しい順"; break;
		case "id_new": echo "登録日の新しい順"; break;
		case "ac_new": echo "アイウエオ順(昇順)"; break;
		case "ac_old": echo "アイウエオ順(降順)"; break;
		case "id_old": echo "登録日の古い順"; break;
		case "time_old": echo "更新日の古い順"; break;
		default: echo "マーク順";
	}
} else {
	switch(@$_GET['sort']) {
		case "time_new": echo "更新日の新しい順"; break;
		case "id_new": echo "登録日の新しい順"; break;
		case "ac_new": echo "アイウエオ順(昇順)"; break;
		case "ac_old": echo "アイウエオ順(降順)"; break;
		case "id_old": echo "登録日の古い順"; break;
		case "time_old": echo "更新日の古い順"; break;
		default: echo "マーク順";
	}
}
?>
</b>]
	<select name="sort" class="form">
		<option value="" selected>---------------</option>
		<option value="time_new">更新日の新しい順で</option>
		<option value="id_new">登録日の新しい順で</option>
		<option value="mark">マーク順で</option>
		<option value="ac_new">アイウエオ順(昇順)で</option>
		<option value="">---------------</option>
		<option value="ac_old">アイウエオ順(降順)で</option>
		<option value="id_old">登録日の古い順で</option>
		<option value="time_old">更新日の古い順で</option>
	</select>
	<input type="submit" class="form" value="表示切替">
</form>
<br>
<?php
	$word_en   = htmlspecialchars($_GET['word']);
	$word_en   = urlencode($word_en);
    $arg_path  = '&mode='       . $_GET['mode']
               . '&sort='       . $_GET['sort']
               . '&word='       . $word_en
               . '&engine='     . $_GET['engine']
               . '&use_str='    . $_GET['use_str']
               . '&method='     . $_GET['method'];
    if(isset($_GET['search_kt']) && !empty($_GET['search_kt'])) {
        $arg_path .= '&search_kt='  . $_GET['search_kt'];
    }
    if(isset($_GET['search_kt_ex']) && !empty($_GET['search_kt_ex'])) {
        $arg_path .= '&search_kt_ex=' . $_GET['search_kt_ex'];
    }
    if(isset($_GET['search_day']) && !empty($_GET['search_day'])) {
        $arg_path .= '&search_day=' . $_GET['search_day'];
    }
	$arg = array($_GET['page'],
                 $Clog,
                 $cfg['hyouji'],
                 $arg_path,
                 $cfg['search']);
	$PRmokuji = mokuji($arg);
	?>
<div align="center"><?php echo $PRmokuji; ?></div>
<br>
<?php
	// &open_for_searchで得たハッシュ/@writeを元にデータを表示@log_linesに入れる
	// $arg1=ページ番号(1～)
	foreach($log_lines as $log_data) {
		$jump_url = $log_data['url'];
		if(@$cfg['rank_fl']) {
			$jump_url = urlencode(unhtmlentities($log_data['url']));
			$jump_url = "{$cfg['rank']}?mode=link&id={$log_data['id']}&url={$jump_url}";
		} else {
			$jump_url = unhtmlentities($log_data['url']);
		}
		?>
<!-- ログ表示 -->
<table border="3" cellpadding="7" id="log">
  <tr id="log-1">
    <td>
	  <a href="<?php echo $jump_url; ?>" target="_blank"><?php echo $log_data['title'];?></a>&nbsp;<?php echo put_icon(); ?>
 <br><br><font size="-1">更新日：<?php echo $log_data['last_time']; ?> [<a href="regist_ys.php?mode=enter&id=<?php echo $log_data['id']; ?>">修正・削除</a>]
 [<a href="regist_ys.php?mode=no_link&id=<?php echo $log_data['id']; ?>&pre=on&title=<?php echo urlencode($log_data['title']); ?>">管理者に通知</a>]
 </font>
<?php
		if($log_data['banner']) {
			?>
<br><a href="<?php echo $jump_url; ?>" target="_blank"><img src="<?php echo $log_data['banner']; ?>" width="<?php echo $cfg_reg['Mbana_w']; ?>" height="<?php echo $cfg_reg['Mbana_h']; ?>" alt="<?php echo $log_data['title'];?>" title="<?php echo $log_data['title'];?>"></a>
<?php
		}
		?>
<tr id="log-2"><td><font id="small">
<?php
		$kt = explode("&", $log_data["category"]);
		foreach($kt as $tmp) {
			$query = "SELECT title FROM {$db->db_pre}category WHERE path='{$tmp}' LIMIT 1";
			$row = $db->single_assoc($query) or $db->error("Query failed $query".__FILE__.__LINE__);
			if($row["title"]) {
				?>[<a href="<?php echo $cfg['script']; ?>?mode=dir&amp;path=<?php echo $tmp; ?>"><?php
				// echo $row[title]; // カテゴリ名を短縮カテゴリ名で表示
				echo full_category($tmp); // カテゴリ名をフルカテゴリ名で表示
				?></a>] <?php
			}
		}
		?>
</font></td></tr>
</td>
</tr>
<tr id="log-3">
	<td><?php echo $log_data['message']; ?></td>
</tr>
<?php
		if($log_data['comment']) {
			?>
<tr id="log-4">
	<td><font id="kanri"><?php echo $log_data['comment']; ?></font></td>
</tr>
<?php
		}
		?>
</table><br>
<!-- /ログ表示 -->
<?php
	}
	?>
<!-- Page Mokuji Output -->
<div align="center"><?php echo $PRmokuji; ?></div>
<!-- /Page Mokuji Output -->
<!--/データがある場合-->
<?php
} else { // 外部検索エンジンへのリンクを表示
    ?>▼該当するデータは見つかりませんでした。下記の検索エンジンで再検索できます。<br />
    <?php
	require_once $cfg['sub_path'] . 'meta_ys.php';
	$location_list = meta('meta_page', 'on');
	PR_meta_page($location_list);
}
?>
<br>

<!-- Other Category Output -->
<?php other_category(); ?>
<!-- /Other Category Output -->

<!-- Footer Space Output -->
<?php if(isset($text['foot_sp'])) { echo $text['foot_sp']; } ?>
<!-- /Footer Space Output -->

<!-- Copy Right Output -->
<div align="center">
<?php cr(); ?><br />
<?php printFooterAd(); ?>
</div>
<!-- /Copy Right Output -->

</body>
</html>