<?php

/**
 * RSS for Yomi-Search(PHP)modified
 *
 * @category   
 * @package    Yomi-Search(PHP)modified
 * @copyright  Copyright (c) 2008 mitsuki
 * @license    
 * @link       http://yomiphp-mod.sweet82.com/
 * @version    1.0.0
 */

// Load Components
//=================
require_once '../class/db.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
//====================================
$db = new db();

// [SQL-SET-NAMES]設定
//=====================
$db->sql_setnames();

// cfgテーブルから設定情報を配列($cfg)へ読込
//===========================================
//local変数に
$db_pre = $db->db_pre;

// cfgテーブルから設定情報を配列($cfg)へ読込
$cfg = array();

// cfgテーブルから設定情報を配列($cfg)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'cfg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg[$tmp[0]] = $tmp[1];
}


// 新着・更新期間の日数からタイムスタンプを算出
//==============================================
$ntime = time() - $cfg['new_time'] * 24 * 3600;

// 新着サイト&更新サイト共通クエリ(SQL文)
//========================================
$sql_base = 'SELECT id, title, url, message, stamp FROM ' . $db_pre . 'log';


// 各モードへ分岐
//================
if (@$_GET['mode'] == 'new') {

	// 新着サイト
	//============
	$title = $cfg['search_name'] . ' (新着サイト一覧)';
	$sql = ' WHERE stamp > ' . $ntime . ' AND renew != 1 ORDER BY id DESC';
	$description = '新しく登録されたサイトの一覧です。';

} elseif(@$_GET['mode'] == 'update') {

	// 更新サイト
	//============
	$title = $cfg['search_name'] . ' (更新サイト一覧)';
	$sql = ' WHERE stamp > ' . $ntime . ' AND renew = 1 ORDER BY stamp DESC';
	$description = '登録内容が更新されたサイトの一覧です。';

} else {

	// 引数が不正な場合は処理を中断
	//==============================
	echo '引数が不正です。処理を中止します。';
	exit;

}

// SQLを実行してログデータを取得
//===============================
$log_lines = array();
$tmp       = array();
$rowset = $db->rowset_assoc($sql_base . $sql);
foreach ($rowset as $tmp) {
   	array_push($log_lines, $tmp);
}

// リンク要素の生成
//==================
$link = $cfg['cgi_path_url'] . 'rss/rss.php' . '?mode=' . htmlspecialchars($_GET['mode']);

// RSSフィード生成
//=================
$RssFeed = MakeRssFeed($log_lines, $title, $link, $description);

// RSSフィード出力
//=================
header('Content-type: text/xml;charset=utf-8');
print $RssFeed;

// 処理終了
//==========
exit;


// RSSフィード生成関数
//=====================
function MakeRssFeed($log_lines, $title, $link, $description) {

    global $cfg;

    $log_data = array();
	$RssFeed  = '';
	$xml      = '';
	
	$xml = implode('', file('header.tpl')) . '\n';
	
    foreach ($log_lines as $log_data) {
		
		$jump_url = str_replace('&', '&amp;', $log_data['url']);
		
		$xml .= '<item>\n';
		$xml .= '<title>' . htmlspecialchars($log_data['title']) . '</title>\n';
		$xml .= '<description>' . htmlspecialchars($log_data['message']) . '</description>\n';
		$xml .= '<link>' . $jump_url . '</link>\n';
		$xml .= '<pubDate>' . date('r', $log_data['stamp']) . '</pubDate>\n';
		$xml .= '</item>\n';
    }
	
	$xml .= implode('', file('footer.tpl'));
	
	// eval(PHPコードとして評価)するための準備("をエスケープ)
	//========================================================
	$xml = str_replace('"', '\"', $xml);
	
	// 文字列($xml)をPHPコードとして評価して変数($RssFeed)に代入
	//===========================================================
	eval("\$RssFeed = \"$xml\";");
	
	return $RssFeed;
}
?>