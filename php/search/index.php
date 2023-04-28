<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 Search Page
--------------------------------------------------------------------------*/

//DB - Connect
require_once $script['lib']['db']['main'];
if(!isset($db)){
	$db = new DB_Connecter($db_set['host'],$db_set['user'],$db_set['password'],$db_set['name'],$db_set['prefix'],$db_set['charset']);

	//Load Script Settings
	require_once $script['lib']['db']['get_setting'];
	$Settings = new Get_Settings($db);
	$cfg = array();
	$cfg = $Settings->Common;
	$cfg_reg = array();
	$cfg_reg = $Settings->Register;
	$text = array();
	$text = $Settings->Description;

	// categoryテーブルからカテゴリ情報を配列($ganes)へ読込
	$ganes=array();
	$query = 'SELECT path,title FROM {prefix}category ORDER BY path';
	$rowset = $db->rowset_num($query);
	foreach($rowset as $tmp) {
		$ganes[$tmp[0]] = $tmp[1];
	}
}
require_once $script['lib']['function'];
require_once $script['lib']['plugin']['ad'];
require_once $script['lib']['plugin']['navigation'];

if(isset($Form['word'])) {
	$Form['word'] = mb_convert_encoding($Form['word'], 'UTF-8', 'auto');
	$Form['word'] = htmlspecialchars($Form['word']);
	if(isset($Form['words']) and is_array($Form['words'])) {
		foreach($Form['words'] as $words_index=>$word_value){
			if($word_value){
				$Form['words'][$words_index] = htmlspecialchars($word_value);
			}
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
if(!isset($Form['page'])) {
	$Form['page'] = 1;
}

// mode値で処理分岐
if(isset($Form['mode'])) {
	switch($Form['mode']){
		case 'search':
			// 検索結果表示画面
			require $script['lib']['search']['result'];
		break;
		case 'meta':
			// 外部リンク画面
			require $script['lib']['metasearch']['index'];
		break;
		default:
			// 詳細検索画面
			require $script['lib']['search']['advanced'];
		break;
	}
} else {
	// 詳細検索画面
	require $script['lib']['search']['advanced'];
}
?>