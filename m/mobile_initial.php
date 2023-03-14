<?php
/***********************************************/
/* yomi-search PHP modified1.8.5n  ガラケー版 */
/* 2010-10-24 nkbt make */
/***********************************************/
$start_time1 = microtime(true); //処理開始タイムGET

$strHostName = gethostbyaddr($_SERVER['REMOTE_ADDR']);
if( strpos($strHostName, 'docomo.ne.jp') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'DoCoMo') !== false) {
    header('Content-type: application/xhtml+xml;charset=utf-8');
} else {
    header('Content-Type: text/html; charset=UTF-8');
}
// エラーレポート設定
require '../php/config4debug.php';

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

// インクルード
require '../class/db.php';
require '../php/meta_ys.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

//アクセス数
require '../php/count_ys.php';

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

require 'cfg_mobile.php';


//強引に読み込ませるtemplateディレクトリを変える
$cfg['temp_path'] = MOBILE_TEMPLATE;

//強引に。。
$cfg['search_name'] = MOBILE_SITE_NAME;
$cfg['sub_path'] = MOBILE_PHP_DIR;
$cfg['script'] =  MOBILE_HOME;
$cfg['home'] = MOBILE_HOME;
$cfg['home'] = MOBILE_HOME;
$cfg['img_path_url'] = PC_IMG_PATH;
require './functions_mobile.php';


// ページ設定
if(isset($_GET['page']) && is_numeric($_GET['page'])) {
 	if($_GET['page'] < 1 or $_GET['page'] > 1000) {
		$_GET['page'] = 1;
	}
} else if(isset($_GET['page'])){
	$_GET['page'] = preg_replace('/\D/', '', $_GET['page']);
	if($_GET['page'] < 1 or $_GET['page'] > 1000) {
		$_GET['page'] = 1;
	}
} else {
    $_GET['page'] = 1;
}

//-----------------//
//各モードへ分岐
//-----------------//
if(isset($_REQUEST['mode'])) {

    if($_REQUEST['mode'] == 'random') {
            require $cfg['sub_path'] . 'random.php';
            exit();
    } elseif($_REQUEST['mode'] == 'mylink') {
            require $cfg['sub_path'] . 'mylink.php';
            exit();
    } 
//    elseif ($_GET['mode'] && strpos($_SERVER['PHP_SELF'], 'index') !== false) {
//            require $cfg['sub_path'] . 'category.php';
//            exit();
//    }
    
    //検索
    if($_REQUEST['mode'] == 'search') {
            require 'search_initial.php';
            require $cfg['sub_path'] . 'search.php';
            exit;
    }

    //サイト登録情報の変更
    if($_REQUEST['mode'] == 'enter') {
            require $cfg['sub_path'] . 'enter.php';
            exit;
    }
    if($_REQUEST['mode'] == 'no_link') {
            require $cfg['sub_path'] . 'no_link.php';
            exit;
    }

    // 新規登録実行(act_regist)
    if($_REQUEST['mode'] == 'act_regist') {
            
            require $cfg['sub_path'] . 'act_regist.php';
            exit;
    }
    // 新規登録実行(代理登録)
    if($_REQUEST['mode'] == 'new_dairi') {
        require $cfg['sub_path'] . 'regist_new_admin.php';
        exit;
    }
    // 削除実行
    if($_REQUEST['mode'] == 'act_del'){
        require $cfg['sub_path'] . 'act_del.php';
        exit;
    }
    // パスワード再発行
    if($_REQUEST['mode'] == 'act_repass') {
        require $cfg['sub_path'] . 'act_repass.php';
        exit;
    }
}

//新規サイト登録
if(((!isset($_REQUEST['mode']) or $_REQUEST['mode'] == '') && strpos($_SERVER['PHP_SELF'], 'regist'))||(isset($_REQUEST['mode']) and $_REQUEST['mode']=='form')){
    require $cfg['sub_path'].'regist_new.php';
    exit();
}


if($debugmode) {
    $end_time1 = microtime(true);
    echo '処理時間：';
    echo $end_time1 - $start_time1;
    echo "<BR />MEMORY:".memory_get_usage();
}
