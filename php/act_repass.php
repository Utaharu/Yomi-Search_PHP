<?php
// (7)パスワードの再発行・変更(act_repass)
if($_POST['mode'] == 'act_repass') {
	if($_POST['repass_mode'] == 'repass') { // パスワード再発行時
		if(!isset($_POST['repass_check'])){$_POST['repass_check'] = "";}
		if($_POST['repass_check'] != 'on') {
			mes('パスワード再発行の確認チェックがありません。もう一度戻ってからチェックを入れて再度実行してください', '確認チェックをしてください', 'java');
		}
		if(!$cfg['re_pass_fl']) {
			mes('パスワードの再発行はできない設定になっています', 'エラー', 'java');
		}
		// 新しいパスワードを作成
		$new_pass = uniqid('');
		$cr_new_pass = crypt($new_pass, 'ys');
		if($cfg['mail_pass']) {
			$PR_mes = 'パスワードの再発行が完了しました<br>新しいパスワードはメールアドレスに送信されます';
		} else {
			$PR_mes = 'パスワードの再発行が完了しました<br>新しいパスワードは「 <b>{$new_pass}</b> 」です';
		}
	} else { // パスワード変更時
		if(!$_POST['new_pass']) {
			mes('<b>パスワード</b>が記入されていません', '記入ミス', 'java');
		} elseif(preg_match('/\W/', $_POST['new_pass'])) {
			$_POST['new_pass'] = '';
			mes('<b>パスワード</b>には全角文字は使用できません', '入力ミス', 'java');
		} elseif(strlen($_POST['new_pass']) > 8) {
			$num = strlen($_POST['new_pass']) - 8;
			$_POST['new_pass'] = '';
			mes('<b>パスワード</b>は半角英数<b>8</b>文字以内でご記入ください', '文字数オーバー('.$num.'文字分)', 'java');
		}
		$new_pass = $_POST['new_pass'];
		$cr_new_pass = crypt($new_pass, 'ys');
		if($cfg['mail_pass']) {
			$PR_mes='パスワードの変更が完了しました<br>新しいパスワードはメールアドレスに送信されます';
		} else {
			$PR_mes='パスワードの変更が完了しました<br>新しいパスワードは「 <b>'.$new_pass.'</b> 」です';
		}
	}
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE id=\''.$_POST['id'].'\' LIMIT 1';
	$Slog = $db->single_num($query);
	if($Slog) {
		if($_POST['repass_mode'] != 'repass') {
			if($_POST['changer'] != 'admin') {
				$cr_pass = crypt($_POST['pass'], $Slog[5]);
				if($cr_pass != $Slog[5]) {
					mes('パスワードが間違っています', 'エラー', 'java');
				}
			} else {
				$cr_pass = crypt($_POST['pass'], $cfg['pass']);
				if($cr_pass != $cfg['pass']) {
					mes('管理パスワードが間違っています', 'エラー', 'java');
				}
			}
		} elseif($_POST['email'] != $Slog[9]) {
			mes('IDとメールアドレスが一致しませんでした', 'エラー', 'java');
		}
		$mail_to = $Slog[9];
		$Slog[5] = $cr_new_pass;
		$query = 'UPDATE '.$db->db_pre.'log SET passwd=\''.$Slog[5].'\' WHERE id=\''.$_POST['id'].'\'';
		$db->query($query);
		if($cfg['mail_pass']) {
			require $cfg['sub_path'] . 'mail_ys.php';
			sendmail($mail_to, $cfg['admin_email'], $cfg['search_name'].' パスワード変更通知', 'pass', '', $Slog, '', '', '', '', '');
		}
		mes($PR_mes, 'パスワード変更完了', $cfg['home']);
	} else {
		mes('該当するIDはありません', 'エラー', 'java');
	}
}
?>