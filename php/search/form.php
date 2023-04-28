<?php
require_once "../config.php";
//DB - Connect
require $script['lib']['db']['main'];
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

require $script['lib']['function'];

require $path_set['template']['root'] . "/search_form.html";
exit;
?>