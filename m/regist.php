<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP) modified データ登録用プログラム 						//
// mobile版
/*--------------------------------------------------------------------------*/
include 'mobile_initial.php';
// 登録内容変更
if(isset($_REQUEST['mode'])) {
	if($_REQUEST['mode'] == 'mente' || $_REQUEST['mode'] == 'act_mente') {
		require $cfg['sub_path'] . 'act_mente.php';
		exit;
	}
}
exit();
?>