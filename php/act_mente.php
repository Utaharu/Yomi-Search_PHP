<?php
// クッキーを記録
$d = $db->db_pre;
if($_REQUEST['mode'] == 'mente'){ // 登録内容変更時

    if(! defined('MOBILE_PHP_DIR')) $CK_data = get_cookie();
        if(preg_match('/\D/', $_REQUEST['id'])) {
		mes('指定されたIDのデータは存在しません', 'エラー', 'java');
        }
	$query = 'SELECT id, title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$d.'log WHERE id=\''.$_REQUEST['id'].'\' LIMIT 1';
	$log_data = $db->single_num($query);
	if(!$log_data) {
		mes('指定されたIDのデータは存在しません', 'エラー', 'java');
	}
	if($_REQUEST['changer'] == 'admin') {
		$CK_data[2] = 'admin'; // 変更者
		if($_REQUEST['pass']) { // 管理者パスワード
			$CK_data[3] = $_REQUEST['pass'];
		}
		if($_REQUEST['cookie'] == 'off') {
			if(function_exists("set_for_cookie")){set_fo_cookie();}
		} else {
			set_cookie($CK_data);
		}
		// パスワード認証(管理者認証)
		$cr_pass = crypt($_REQUEST['pass'], $cfg['pass']);
		if($cr_pass != $cfg['pass']) {
			if(!$_SERVER['REMOTE_HOST']) {
				$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			}
			mes('パスワードの認証に失敗しました<br>認証したコンピュータのIPアドレス：<b>'.$_SERVER['REMOTE_ADDR'].'</b><br>認証したコンピュータのホスト名：<b>'.$_SERVER['REMOTE_HOST'].'</b>', 'パスワード認証失敗', 'java');
		}
	} else {
		if($cfg_reg['no_mente']) {
			mes('現在、登録者による修正・削除は停止されています', 'エラー', 'java');
		}
		$cr_pass = crypt($_POST['pass'], $log_data[5]);
		if($log_data[5] != $cr_pass) {
			mes('パスワードが違います', 'パスワード認証エラー', 'java');
		}
		$CK_data[0] = $_POST['pass']; // 登録者パスワード
		$CK_data[1] = $_POST['id']; // ID
		if($_POST['cookie'] == 'off') {
			if(! defined('MOBILE_PHP_DIR')) set_fo_cookie();
		} else {
			if(! defined('MOBILE_PHP_DIR')) set_cookie($CK_data);
		}
	}
	if(!$log_data[12]) {
		$log_data[12] = 'http://';
	}
	$time = time();
	$start = $time - $cfg['rank_kikan'] * 86400;
	$end = $time;
	$query = 'SELECT COUNT(id) FROM '.$d.'rank WHERE time BETWEEN '.$start.' AND '.$end.' AND id='.$log_data[0];
	$count = $db->single_num($query);

	$log_data['pt0'] = $count[0];
	$start = $time - $cfg['rev_kikan'] * 86400;
	$query = 'SELECT COUNT(id) FROM '.$d.'rev WHERE time BETWEEN '.$start.' AND '.$end.' AND id='.$log_data[0];

	$count = $db->single_num($query);
	$log_data['pt2'] = $count[0];
	$query = 'SELECT rank,rev FROM '.$d.'rank_counter WHERE id=\''.$log_data[0].'\'';

	$count = $db->single_assoc($query);

	if(isset($count['rank'])) {
		$log_data['pt1'] = $count['rank'];
	} else {
		$log_data['pt1'] = 0;
	}
	if(isset($count['rev'])) {
		$log_data['pt3'] = $count['rev'];
	} else {
		$log_data['pt3'] = 0;
	}
	$log_data[6] = str_replace('<br>', "\n", $log_data[6]);
	$log_data[7] = str_replace('<br>', "\n", $log_data[7]);
	header('Content-type: text/html; charset=UTF-8');
	if($_REQUEST['changer'] == 'admin') {
		require $cfg['temp_path'] . 'regist_mente_admin.html';
	} else {
		require $cfg['temp_path'] . 'regist_mente.html';
	}

// 登録内容変更(act_mente)
} elseif($_POST['mode'] == 'act_mente') {
	// パスワード認証(管理者認証)
	if($_POST['changer'] == 'admin') {
		$cr_pass = crypt($_POST['pass'], $cfg['pass']);
		if($cr_pass != $cfg['pass']) {
			if(!$_SERVER['REMOTE_HOST']) {
				$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			}
			mes('パスワードの認証に失敗しました<br>認証したコンピュータのIPアドレス：<b>'.$_SERVER['REMOTE_ADDR'].'</b><br>認証したコンピュータのホスト名：<b>'.$_SERVER['REMOTE_HOST'].'</b>', 'パスワード認証失敗', 'java');
		}
	} elseif($cfg_reg['no_mente']) {
		mes('現在、登録者による修正・削除は停止されています', 'エラー', 'java');
	}
	check(); // 入力内容のチェック
	// $pre_log取得&2重URL登録チェック
	if($cfg_reg['nijyu_url']) {
		get_id_url_ch(2);
	}

       if(preg_match('/\D/', $_POST['id'])) {
		mes('指定されたIDのデータは存在しません', 'エラー', 'java');
        }

	$query = 'SELECT id, title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd  FROM '.$d.'log WHERE id=\''.$_POST['id'].'\' LIMIT 1';
	$pre_log = $db->single_num($query);
	// 登録者のパスワード認証
	if($_POST['changer'] != 'admin'){
		// $cr_pass = crypt($_POST['pass'], $pre_log[5]);
		if($pre_log[5] != $_POST['Fpass']) {
			mes('パスワードが間違っています' . $_POST['Fpass'] . $pre_log[5], 'パスワード認証エラー', 'java');
		}
	}
	$Slog = join_fld($_POST['id']);
	// 本体ログデータに書き込み
	foreach($Slog as $key=>$val) {
		$Tlog[$key] = $db->escape_string($val);
	}
	$query = "UPDATE {$db->db_pre}log SET title='{$Tlog[1]}',url='{$Tlog[2]}',mark='{$Tlog[3]}',last_time='{$Tlog[4]}',message='{$Tlog[6]}',comment='{$Tlog[7]}',name='{$Tlog[8]}',mail='{$Tlog[9]}',category='{$Tlog[10]}',stamp='{$Tlog[11]}',banner='{$Tlog[12]}',renew='{$Tlog[13]}',keywd='{$Tlog[15]}' WHERE id={$_POST["id"]}";
	$db->query($query);
	unset($Tlog);
	// メールを送信
	$Slog[6] = str_replace('<br>', "\n", $Slog[6]);
	$Slog[7] = str_replace('<br>', "\n", $Slog[7]);
	if($cfg['mail_new']) {
		require $cfg['sub_path'] . 'mail_ys.php';
	}
	if($cfg['mail_to_admin'] && $cfg['mail_ch']) { // 管理人へメール送信
		sendmail($cfg['admin_email'], $Slog[9], "{$cfg['search_name']} 登録内容変更完了通知", 'mente', 'admin', $Slog, '', '', '', '', '');
	}
	if($cfg['mail_to_register'] && $cfg['mail_ch']) { // 登録者へメール送信
		sendmail($Slog[9], $cfg['admin_email'], "{$cfg['search_name']} 登録内容変更完了通知", 'mente', '', $Slog, '', '', '', '', '');
	}
	$Slog[6] = str_replace("\n", '<br>', $Slog[6]);
	$Slog[7] = str_replace("\n", '<br>', $Slog[7]);
	// 更新するカテゴリリストを作成
	// %TASKを使用
	// マークの表示設定
	$i = 1;
	$PR_mark = '';
	$mark = explode('_', $Slog[3]);
	foreach($mark as $tmp){
		if($tmp) {
			$PR_mark .= $cfg['name_m'.$i] . ' ';
		}
		$i++;
	}
	// カテゴリの変更表示設定
	if($cfg['user_change_kt']) {
		$PR_kt = '※登録者によるカテゴリ変更は現在禁止されています';
	} else {
		$PR_kt='';
	}
	// 登録結果出力
	header('Content-type: text/html; charset=UTF-8');
	require $cfg['temp_path'] . 'regist_mente_end.html';
}
?>