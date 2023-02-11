<?php
// エラーレポート設定
require 'mobile_initial.php';
if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

// インクルード
//require 'class/db.php';
//require 'functions.php';
//require 'ads.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

//local変数に
$db_pre = $db->db_pre;

// cfgテーブルから設定情報を配列($cfg)へ読込
$cfg = array();

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

// #-- 目次 --#
// (1)リンクジャンプ処理(link)
// (1.1)アクセスジャンプ処理(r_link)
// (2)キーワードランキング表示画面(PR_keyrank)
// (3)アクセス(IN)ランキング表示画面(PR_rev)
// (4)人気(OUT)ランキング表示画面(PR_rank)

// ページ設定
if(isset($_GET['page'])) {
        if(preg_match('/^\d+$/', $_GET['page'])==false) {
            $_GET['page'] = 1;
        }
	if($_GET['page'] < 1 or $_GET['page'] > 1000) {
		$_GET['page'] = 1;
	}
} else {
	$_GET['page']=1;
}


if(isset($_GET['mode'])) {
	// (1)リンクジャンプ処理(link)
	if($_GET['mode'] == 'link') {
		$_GET['id'] = preg_replace('/\D/', '', $_GET['id']);
		if($_GET['id']) {
			// refererチェック
			// refererが無いときにカウントしない場合にはこのif文を削除
			if(!$_SERVER['HTTP_REFERER']) {
				$fl = 1;
			}
			$ref_list = explode(',', $cfg['rank_ref']);
			if(!$cfg['rank_ref']) {
				$fl = 1;
			} else {
				foreach($ref_list as $tmp){
					if(strstr($_SERVER['HTTP_REFERER'], $tmp)) {
						$fl = 1;
					}
				}
			}


			if($fl) {
				$time = time();
				$query = 'SELECT id FROM '.$db->db_pre.'rank WHERE id=\''.$_GET['id'].'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - $cfg['rank_time'] * 3600);
				$tmp = $db->single_num($query);
				if(!$tmp) {
					$query = 'INSERT INTO '.$db->db_pre.'rank (id,time,ip) VALUES (\''.$_GET['id'].'\', \''.$time.'\' ,\''.$_SERVER['REMOTE_ADDR'].'\')';
					$result = $db->query($query);
					$query = "UPDATE {$db->db_pre}rank_counter SET rank=rank+1 WHERE id='{$_GET["id"]}'";
					$db->query($query);
				}
			}
			//リンク先URLの引き当て
			$getid = (int)$_GET['id'];
			$query = 'SELECT url FROM '.$db->db_pre.'log WHERE id=\''.$getid.'\'';
			$url = $db->single_num($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
			//リンク先へ転送
			if(isset($url[0])){
				location($url[0]);
				exit();
			}
			unset($url);
			unset($getid);
		}
		mes("該当するリンクが有りません","エラー","java");
	// (1.1)アクセスジャンプ処理(r_link)
	} elseif($_GET["mode"] == "r_link"){
		if($cfg['rev_fl']) {
			$_GET['id'] = preg_replace("/\D/", "", $_GET['id']);
			if($_GET['id']){
				$query = "SELECT COUNT(id) FROM {$db->db_pre}log WHERE id='{$_GET["id"]}'";
				$result = $db->query($query);
				if(mysql_result($result, 0) > 0) {
					$time = time();
					$_GET['id'] = str_replace("\n", "", $_GET['id']);
					$query = "SELECT id FROM {$db->db_pre}rev WHERE id='{$_GET["id"]}' AND ip='{$_SERVER["REMOTE_ADDR"]}' AND time > ".($time - $cfg['rank_time'] * 3600);
					$tmp = $db->single_num($query);
					if(!$tmp) {
						$query = "INSERT INTO {$db->db_pre}rev (id,time,ip) VALUES ('{$_GET["id"]}', '{$time}' ,'{$_SERVER["REMOTE_ADDR"]}')";
						$result = $db->query($query);
						$query = "UPDATE {$db->db_pre}rank_counter SET rev=rev+1 WHERE id='{$_GET["id"]}'";
						$db->query($query);
					}
				}
			}
		}
		$cfg['location'] = 0; // refreshジャンプにする
		location($cfg['rev_url']);
	
	// (2)キーワードランキング表示画面(keyrank)
	} elseif($_GET['mode'] == "keyrank"){
		require $cfg['sub_path'] . "keyrank.php";
		exit;
	// (3)アクセス(IN)ランキング表示画面(rev)
	} elseif($_GET["mode"] == "rev" or $_GET["mode"] == "rev_bf" or $_GET["mode"] == "rev_rui"){
		if(!$cfg["rev_fl"]) {
			mes("アクセスランキングは実施しない設定になっています","エラー","java");
		}
		require $cfg["sub_path"] . "rank.php";
		exit;
	}
}
// (4)人気ランキング表示画面
if(!$cfg["rank_fl"]) {
	mes("人気ランキングは実施しない設定になっています","エラー","java");
}
if(!isset($_GET["mode"])) {
	$_GET["mode"] = "rank_ys";
}
require $cfg["sub_path"] . "rank.php";
exit;


?>