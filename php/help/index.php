<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 Help Page
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
require $path_set['template']['root'] . '/help.html';
?>