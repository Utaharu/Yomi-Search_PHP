<?php
// クラスファイルインクルード
require_once '../class/db.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

// [SQL-SET-NAMES]設定
$db->sql_setnames();

// <入力チェック>
$mes = '';

// パスワード未入力チェック
if(empty($_POST['pass1']) || empty($_POST['pass2'])) {
	$mes .= 'パスワードは必ず設定してください。<br />'."\n";
}

// パスワード確認チェック
if($_POST['pass1'] != $_POST['pass2']) {
	$mes .= 'パスワードが確認用と一致していません。<br />'."\n";
}

// 管理者名未入力チェック
if(empty($_POST['admin_name'])) {
	$mes .= '管理者名が未入力です。<br />'."\n";
}

// 検索エンジン名称未入力チェック
if(empty($_POST['search_name'])) {
	$mes .= '検索エンジンの名称が未入力です。<br />'."\n";
}

// メールアドレス称未入力チェック
if(empty($_POST['admin_email'])) {
	$mes .= 'メールアドレスが未入力です。<br />'."\n";
}

// メールアドレス形式チェック
if(!CheckMail($_POST['admin_email'])) {
	$mes .= 'メールアドレスの形式が不正です。<br />'."\n";
}

// 検索エンジンのURL未入力チェック
if(empty($_POST['home'])) {
	$mes .= '検索エンジンのURLが未入力です。<br />'."\n";
}

// ホームページのURL未入力チェック
if(empty($_POST['admin_hp'])) {
	$mes .= 'ホームページのURLが未入力です。<br />'."\n";
}

// トップディレクトリのURL未入力チェック
if(empty($_POST['cgi_path_url'])) {
	$mes .= 'トップディレクトリのURLが未入力です。<br />'."\n";
}

// トアクセス(IN)ランキング用URLの転送先未入力チェック
if(empty($_POST['rev_url'])) {
	$mes .= 'アクセス(IN)ランキング用URLの転送先が未入力です。<br />'."\n";
}
// </入力チェック>

// データベースを更新(初期設定反映)
if(empty($mes)) {
	// パスワードを暗号化して更新
	$_POST['pass1'] = crypt($_POST['pass1'],"ys");
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["pass1"]}' WHERE name='pass'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// 管理者名を更新
	$_POST["admin_name"] = $db->escape_string(trim($_POST["admin_name"]));
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["admin_name"]}' WHERE name='admin_name'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// 検索エンジンの名称を更新
	$_POST["search_name"] = $db->escape_string(trim($_POST["search_name"]));
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["search_name"]}' WHERE name='search_name'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	//　- スマホ
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["search_name"]}' WHERE name='sp_search_name'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// メールアドレスを更新
	$_POST["admin_email"] = $db->escape_string(trim($_POST["admin_email"]));
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["admin_email"]}' WHERE name='admin_email'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// 検索エンジンのURLを更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["home"]}' WHERE name='home'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// ホームページのURLを更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["admin_hp"]}' WHERE name='admin_hp'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// トップディレクトリのURLを更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["cgi_path_url"]}' WHERE name='cgi_path_url'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// - スマホ
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["cgi_path_url"]}/s/' WHERE name='sp_path_url'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	
	// 人気(OUT)ランキングの実施を更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["rank_fl"]}' WHERE name='rank_fl'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// アクセス(IN)ランキングの実施を更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["rev_fl"]}' WHERE name='rev_fl'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// アクセス(IN)ランキング用URLの転送先を更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["rev_url"]}' WHERE name='rev_url'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	// マイリンク機能の使用を更新
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["mylink_fl"]}' WHERE name='mylink_fl'";
	$result = $db->query($query) or $db->error('Query failed '.$query.__FILE__.__LINE__); 
	
	// cfgテーブルから設定情報を配列($cfg)へ読込
	$query = 'SELECT name, value FROM '.$db->db_pre.'cfg';
	$rowset = $db->rowset_num($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
	foreach($rowset as $tmp) {
		$cfg[$tmp[0]] = $tmp[1];
	}
	
	// セットアップ完了ページを表示
	require_once 'setup03.html';
	exit;
} else {
	require_once 'err.html';
	exit;
}

// ---------------------------------------------------------------- //
// 関数名：CheckMail                                                //
// 引　数：文字列                                                   //
// 戻　値：形式が正しい場合      ：TRUE                             //
// 　　　　形式が間違っている場合：FALSE                            //
// 解　説：メールアドレスの書式が正しいかどうかを判断する関数       //
// ---------------------------------------------------------------- //
function CheckMail( $str ) {
	if(preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+$/', $str)) {
		return TRUE;
	} else {
		return FALSE;
	}
}
?>