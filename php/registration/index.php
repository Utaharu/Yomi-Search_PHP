<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 Registration Page
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
}
require_once $script['lib']['function'];
require_once $script['lib']['registration']['common'];
require_once $script['lib']['plugin']['navigation'];

if(isset($Form['mode'])) {
	switch($Form['mode']){
		case 'enter' :
			require $script['lib']['registration']['confirm_form'];
		break;
		case 'no_link' :
			require $script['lib']['report']['index'];
		break;
		case 'act_regist' :
			// 新規登録実行(act_regist)
			require $script['lib']['registration']['register'];
		break;
		case 'new_dairi' :
			// 新規登録実行(代理登録)
			require $script['lib']['registration']['admin_form'];
        break;
		case 'act_del' :
			// 削除実行
			require $script['lib']['registration']['delete'];
		break;
		case 'act_repass' :
			// パスワード再発行
			require $script['lib']['registration']['reissue'];
		break;
		case 'mente' :
		case 'act_mente':
			require $script['lib']['registration']['edit'];
		break;
		case 'new' :
		default :
			require $script['lib']['registration']['user_form'];
		break;
	}
}else{
	require $script['lib']['registration']['user_form'];
}
?>