<?php
// エラーレポート設定
require_once '../php/config4debug.php';
require_once './cfg.php';

if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

if(isset($_POST['mode'])) {

	// テーブル作成
	if($_POST['mode'] == 'install') {
		require_once 'setup01.php';
		require_once 'setup01.html';
		exit;
	}
	
	// 初期設定
	if($_POST['mode'] == 'initialsetting') {
		require_once 'setup02.php';
		require_once 'setup02.html';
		exit;
	}
	
	// 初期設定反映して完了
	if($_POST['mode'] == 'finish') {
		require_once 'setup03.php';
		require_once 'setup03.html';
		exit;
	}

        echo 'error!';
        exit;

// トップページ表示
} else {
	require_once('top.html');
	exit;
}
?>