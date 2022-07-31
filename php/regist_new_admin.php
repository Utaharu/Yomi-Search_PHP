<?php
if ($_POST['mode'] == 'new_dairi') {
    // パスワード認証(管理者認証)
    $cr_pass = crypt($_POST['pass'], $cfg['pass']);
    if ($cr_pass != $cfg['pass']) {
        if(!isset($_SERVER['REMOTE_HOST'])) {
            $_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        $msg = 'パスワードの認証に失敗しました。<br />'
             . '認証したコンピュータのIPアドレス：<b>' . $_SERVER['REMOTE_ADDR'] . '</b><br />'
             . '認証したコンピュータのホスト名：<b>' . $_SERVER['REMOTE_HOST'] . '</b>';
        $msgTitle = 'パスワード認証失敗';
        mes($msg, $msgTitle, 'java');
	}
	// 概入力値の設定
	if (!isset($_POST['Fname'])) {
		$_POST['Fname'] = '管理人代理登録';
	}
	if (!isset($_POST['Femail'])) {
		$_POST['Femail'] = $cfg['admin_email'];
	}

	if (!isset($_POST['Fpass'])) {
		$_POST['Fpass'] = '';
	}
	if (!isset($_POST['Fpass2'])) {
		$_POST['Fpass2'] = '';
	}

	if (!isset($_POST['Furl'])) {
		$_POST['Furl'] = 'http://';
	}
	if (!isset($_POST['Fbana_url'])) {
		$_POST['Fbana_url'] = 'http://';
	}
	if (!isset($_POST['Ftitle'])) {
		$_POST['Ftitle'] = '';
	}
	if (!isset($_POST['Fsyoukai'])) {
		$_POST['Fsyoukai'] = '';
	}
	if (!isset($_POST['Fkanricom'])) {
		$_POST['Fkanricom'] = '';
	}
	if (!isset($_POST['Fkey'])) {
		$_POST['Fkey'] = '';
	}
	header('Content-type: text/html; charset=UTF-8');
	require $cfg['temp_path'] . 'regist_new_admin.html';
}
?>