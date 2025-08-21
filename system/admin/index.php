<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Management;
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 AdministratorRoom Page
--------------------------------------------------------------------------*/
//DB - Connect
require $script['lib']['db']['main'];
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
}

require $script['lib']['function'];
require $script['lib']['plugin']['cookie'];
require_once $script['lib']['plugin']['navigation'];

$PR_menu_bar = "";
if(isset($text['menu_bar'])){$PR_menu_bar = $text['menu_bar'];}

// 登録申請中(仮登録状態)のログ件数を取得して
// 管理室用のリストボックスを生成
$count_temp = $db->log_count('log_temp');

// (1)ログイン画面(&login)
// header("Content-type: text/html; charset=UTF-8");
if(isset($Form["mode"])) {
	require $script['lib']['admin']['listbox'];

if($Form["mode"] == "kanri") {

} elseif($Form["mode"] == "cfg_kanri") {
	// (2.1)認証設定画面表示(cfg_kanri)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_kanri.html";
	exit;
} elseif($Form["mode"] == "temp_to_regist") {
	/* temp_to_regist - File */
} elseif($Form["mode"] == "temp_to_regist_act") {
	/* temp_to_regist_act - File */
} elseif($Form["mode"] == "key_cfg") {
	/* key_cfg - File */
} elseif($Form["mode"] == "key_cfg_act") {

} elseif($Form["mode"] == "key_del_act") {

} elseif($Form["mode"] == "key_cfg_del_word_act") {

} elseif($Form["mode"] == "log_conv") {
	// (7)各種ログ変換(&log_conv)
	pass_check();
	require $cfg["temp_path"] . "admin/log_conv.html";
	exit;
} elseif($Form["mode"] == "log_conv_act") {

} elseif($Form['mode'] == 'log_conv_kt_sort') {

}
elseif($Form["mode"] == "log_kt_change") {
	
} elseif($Form["mode"] == "log_kt_change_act") {

} elseif($Form["mode"] == "log_repair") {
	// (9)ログ(登録データ)の修復(&log_repair)
	pass_check();
	require $cfg["temp_path"] . "admin/log_repair.html";
	exit;
} elseif($Form["mode"] == "log_repair_act") {
	// (9.1)ログ(登録データ)の修復実行(&log_repair_act)

} elseif($Form["mode"] == "config") {
	// (11)環境設定 (&config)
	pass_check();
	require $cfg["temp_path"] . "admin/config.html";
	exit;
} elseif($Form["mode"] == "config_kt") {

} elseif($Form["mode"] == "rank_cfg") {
	// (13)人気ランキングの設定(&rank_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/rank_cfg.html";
	exit;
} elseif($Form["mode"] == "dl_check") {
	
} elseif($Form["mode"] == "dl_check_dl") {

} elseif($Form["mode"] == "dl_check_act") {
	
} elseif($Form["mode"] == "mylink_cfg") {
	// (15)マイリンクの設定(&mylink_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/mylink_cfg.html";
	exit;
} elseif($Form["mode"] == "ver_info") {
	// (18)バージョン情報(&ver_info)
	pass_check();
	require $cfg["temp_path"] . "admin/ver_info.html";
	exit;
} elseif($Form["mode"] == "cfg_admin_pass") {
	// 管理者パスワード変更(cfg_admin_pass)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_admin_pass.html";
	exit;
} elseif($Form["mode"] == "look_mes") {

} elseif($Form["mode"] == "new_pass") {

} elseif($Form["mode"] == "cfg_make") {

} elseif($Form["mode"] == "cfg_make_PR_menu") {
	
} elseif($Form["mode"] == "cfg_make_reg") {

} elseif($Form["mode"] == "cfg_make_kt") {
	
} elseif($Form["mode"] == "cfg_reg") {
	// 環境設定(登録処理関係)
	require $cfg["temp_path"] . "admin/cfg_reg.html";
	exit;
} elseif($Form["mode"] == "cfg_html") {

} elseif($Form["mode"] == "cfg_marks") {
	require $cfg["temp_path"] . "admin/cfg_marks.html";
	exit;
}elseif($Form['mode'] == "db_ipv6"){
/*
 V @ old -> V.8.0 - db @ ipv6
	require_once $script['lib']['db']['expand']['ipv4_v6'];
	$db_ipv_conv = new DB_Expand_IPv4Tov6();
	$db_ipv_conv->Change_Ipv6_Column();
*/	
	exit;
}	

} else {
	// (1)ログイン画面(&login)
	require $cfg["temp_path"] . "admin/login.html";
	exit;
}