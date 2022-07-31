<?php
// 修正・削除のためのパスワード認証(enter)
if($_REQUEST['mode'] == 'enter') {
	// クッキーの読み込み
	$cookie_data = get_cookie();
	// cookieの有無を判定
	if (count($cookie_data) > 0) {
		$cookie_0 = '';
		$cookie_1 = ' checked';
	} else {
		$cookie_0 = ' checked';
		$cookie_1 = '';
	}
	// local($pre_pass); #パスワードの既入力値
	if($cookie_data[2] == 'admin') {
		$pre_pass = $cookie_data[3];
		$select_0 = '';
		$select_1 = ' selected';
	} else {
		$pre_pass = $cookie_data[0];
		$select_0 = ' selected';
		$select_1 = '';
	}
	if($cookie_data[4] && $cookie_data[3] && $_GET['id']){ // 直接認証
		$_REQUEST['pass'] = $cookie_data[3];
		$_REQUEST['changer'] = 'admin';
		$_REQUEST['mode'] = 'mente';
		$_REQUEST['id'] = $_GET['id'];
		require $cfg['sub_path'] . 'act_mente.php';
		exit;
	} else {
		if(!isset($_REQUEST['id'])) {
			$_REQUEST['id'] = $cookie_data[1];
		}
		// 概入力値の設定
		$_REQUEST['id'] = preg_replace('/\D/', '', $_REQUEST['id']);
		if($_REQUEST['id']) {
			$i = 0;
			$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE id=\''.$_REQUEST['id'].'\' LIMIT 1';
			$log = $db->single_num($query);
			if ($log[0] == $_REQUEST['id']) {
				$i = 1;
			}
                        $log[1] = str_replace('&amp;amp;', '&', $log[1]);
			$print_data = <<<EOM
[登録データ]<br>
<table width="200"><tr><td>
■タイトル：<br>{$log[1]}<br>
■URL：<br><a href="{$log[2]}">{$log[2]}</a>
</td></tr>
</table>
EOM;
			if(!$i) {
				if(!$cookie_data[1]) {
					mes('指定されたIDのデータは存在しません', 'エラー', 'java');
				} else { // Cookieのログが削除されていた場合
					$print_data = '';
				}
			}
		}
	}
	header('Content-type: text/html; charset=UTF-8');
	require $cfg['temp_path'] . 'enter.html';
}
?>