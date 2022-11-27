<?php
if(@$cfg_reg['no_regist']) {
	$title = '登録処理エラー';
	$mes  = '管理人のみが登録できるモードで運用中。<br>'."\n";
	$mes .= '訪問者の登録は受け付けることができません。'."\n";
	$back_url = '<a href="' . $_SERVER['HTTP_REFERER'] . '">戻る</a>';
	require $cfg['temp_path'] . 'mes.html';
	exit;
}
if(isset($_GET['path'])) {
	$category = '&' . $_GET['path'];
} else {
	$category = '&';
}

if(isset($_POST['mode']) && $_POST['mode'] == 'form') { // 外部入力(form)
	$category = '&';
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
		if(isset($_POST['Fkt{$i}'])){
			$category .= $_POST['Fkt{$i}'] . '&';
		}
	}
}

if(!isset($_POST['Furl'])) {
	$_POST['Furl'] = 'http://';
}

if(!isset($_POST['Fbana_url'])) {
	$_POST['Fbana_url'] = 'http://';
}

if(isset($_POST['Fto_admin'])) {
	$_POST['Fto_admin'] = str_replace('&lt;br&gt;', "\n", $_POST['Fto_admin']);
	$_POST['Fto_admin'] = str_replace('<br>', "\n", $_POST['Fto_admin']);
} else {
	$_POST['Fto_admin'] = '';
}

require $cfg['temp_path'] . 'regist_new.html';
?>