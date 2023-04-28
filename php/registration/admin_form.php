<?php
if ($Form['mode'] == 'new_dairi') {
    // パスワード認証(管理者認証)
    $cr_pass = crypt($Form['pass'], $cfg['pass']);
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
	if (!isset($Form['Fname'])) {
		$Form['Fname'] = '管理人代理登録';
	}
	if (!isset($Form['Femail'])) {
		$Form['Femail'] = $cfg['admin_email'];
	}

	if (!isset($Form['Fpass'])) {
		$Form['Fpass'] = '';
	}
	if (!isset($Form['Fpass2'])) {
		$Form['Fpass2'] = '';
	}

	if (!isset($Form['Furl'])) {
		$Form['Furl'] = 'http://';
	}
	if (!isset($Form['Fbana_url'])) {
		$Form['Fbana_url'] = 'http://';
	}
	if (!isset($Form['Ftitle'])) {
		$Form['Ftitle'] = '';
	}
	if (!isset($Form['Fsyoukai'])) {
		$Form['Fsyoukai'] = '';
	}
	if (!isset($Form['Fkanricom'])) {
		$Form['Fkanricom'] = '';
	}
	if (!isset($Form['Fkey'])) {
		$Form['Fkey'] = '';
	}
	require $path_set['template']['root'] . '/regist_new_admin.html';
}
?>