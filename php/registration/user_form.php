<?php
if(@$cfg_reg['no_regist']) {
	$title = '登録処理エラー';
	$mes  = '管理人のみが登録できるモードで運用中。<br>'."\n";
	$mes .= '訪問者の登録は受け付けることができません。'."\n";
	$back_url = '<a href="' . $_SERVER['HTTP_REFERER'] . '">戻る</a>';
	require $path_set['template']['root'] . '/mes.html';
	exit;
}
if(isset($Form['path'])) {
	$category = '&' . $Form['path'];
} else {
	$category = '&';
}

if(isset($Form['mode']) && $Form['mode'] == 'form') { // 外部入力(form)
	$category = '&';
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
		if(isset($Form['Fkt{$i}'])){
			$category .= $Form['Fkt{$i}'] . '&';
		}
	}
}

if(!isset($Form['Furl'])) {
	$Form['Furl'] = 'http://';
}

if(!isset($Form['Fbana_url'])) {
	$Form['Fbana_url'] = 'http://';
}

if(isset($Form['Fto_admin'])) {
	$Form['Fto_admin'] = str_replace('&lt;br&gt;', "\n", $Form['Fto_admin']);
	$Form['Fto_admin'] = str_replace('<br>', "\n", $Form['Fto_admin']);
} else {
	$Form['Fto_admin'] = '';
}

require $path_set['template']['root'] . '/regist_new.html';
?>