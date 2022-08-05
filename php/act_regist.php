<?php
// 新規登録実行(act_regist)
if($_POST['mode'] == 'act_regist') {

	// (0)スパム登録対策用コード認証を実行
	check_certification_cord();

	// (1)プレビュー画面(preview)
	if(!@$_POST['preview']) {
		$_POST['preview'] = '';
	}
	if($_POST['preview'] == 'on') {
		if(!isset($_POST['Smode_name'])){$_POST['Smode_name'] = "";}
		if(!isset($_POST['pass'])){$_POST['pass'] = "";}
		// ※登録者の新規登録時にのみ使用
		check();
		// その他の設定
		// 相互リンクの有無
		$MES_sougo[1] = ' checked';
		$MES_sougo[0] = '';
		// 紹介文、管理人へのメッセージの改行を変換(<br>→\n)
		$_POST['Fsyoukai'] = str_replace('<br>', "\n", $_POST['Fsyoukai']);
		$_POST['Fto_admin'] = str_replace('<br>', "\n", $_POST['Fto_admin']);
		require $cfg['temp_path'] . 'regist_new_preview.html';
		exit;
	}
	// $new=>追加データ書き込み用
	// $hyouji_log=>結果表示用のログデータ
	// パスワード認証(管理者認証)
	if($_POST['changer'] == 'admin') {
		$cr_pass = crypt($_POST['pass'], $cfg['pass']);
		if($cr_pass != $cfg['pass']) {
			if(!$_SERVER['REMOTE_HOST']) {
				$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			}
			mes('パスワードの認証に失敗しました<br>認証したコンピュータのIPアドレス：<b>'.$_SERVER['REMOTE_ADDR'].'</b><br>認証したコンピュータのホスト名：<b>'.$_SERVER['REMOTE_HOST'].'</b>', 'パスワード認証失敗', 'java');
		}
	}
	check(); // 入力内容のチェック
	// ID取得&2重URL登録チェック
	if(@$cfg_reg['nijyu_url']) {
		get_id_url_ch(1);
	}
	$hyouji_log = join_fld(); // 入力内容の整形
	if($cfg['user_check'] && $_POST['changer'] != 'admin') { // <仮登録時>
		// 仮登録ログデータに追加書き込み
		foreach($hyouji_log as $key=>$val) {
			$log_data[$key] = $db->escape_string($val);
		}
		$query = 'INSERT INTO '.$db->db_pre.'log_temp VALUES ('.
			"NULL,
			'{$log_data[1]}',
			'{$log_data[2]}',
			'{$log_data[3]}',
			'{$log_data[4]}',
			'{$log_data[5]}',
			'{$log_data[6]}',
			'{$log_data[7]}',
			'{$log_data[8]}',
			'{$log_data[9]}',
			'{$log_data[10]}',
			'{$log_data[11]}',
			'{$log_data[12]}',
			'{$log_data[13]}',
			'{$log_data[14]}',
			'{$log_data[15]}'
		)";
		$result = $db->query($query);
		// メールを送信
		$log_data = $hyouji_log;
		// メールの件名に付けるマークを設定
		if($_POST['Fsougo']) {
			$PR_mail_sougo = '(link)';
		} else {
			$PR_mail_sougo = '';
		}
		if($_POST['Fto_admin']) {
			$PR_mail_com = '(com)';
			$PRto_admin = htmlspecialchars($_POST['Fto_admin']);
		} else {
			$PR_mail_com = '';
		}
		if($_POST['Fadd_kt']) {
			$PR_mail_kt = '(kt)';
			$PRadd_kt = htmlspecialchars($_POST['Fadd_kt']);
		} else {
			$PR_mail_kt = '';
		}
		$PR_mail_add_line = $PR_mail_sougo . $PR_mail_com . $PR_mail_kt;
		$log_data[6] = str_replace('<br>', "\n", $log_data[6]);
		$log_data[7] = '';
		if($cfg['mail_temp']) {
			require $cfg['sub_path'] . 'mail_ys.php';
		}
		if($cfg['mail_to_admin'] && $cfg['mail_temp']) { // 管理人へメール送信
			sendmail($cfg['admin_email'], $log_data[9], $cfg['search_name'].' 仮登録完了通知' . $PR_mail_add_line, 'temp', 'admin', $log_data, $_POST['Fsougo'], $_POST['Fadd_kt'], $_POST['Fto_admin'], "", "");
		}
		if($cfg['mail_to_register'] && $cfg['mail_temp']) { // 登録者へメール送信
			sendmail($log_data[9], $cfg['admin_email'], "{$cfg['search_name']} 仮登録完了通知", "temp", "", $log_data, $_POST['Fsougo'], $_POST['Fadd_kt'], $_POST['Fto_admin'], '', '');
		}
		$log_data[6] = str_replace("\n", '<br>', $log_data[6]);
		// 登録結果出力
		header('Content-type: text/html; charset=UTF-8');
		require $cfg['temp_path'] . 'regist_tmp_end.html';
		// </仮登録時>
	} else { // <新規登録時>
		foreach($hyouji_log as $key=>$val) {
			$log_data[$key] = $db->escape_string($val);
		}
		$query = "INSERT INTO {$db->db_pre}log VALUES (NULL,'{$log_data[1]}','{$log_data[2]}','{$log_data[3]}','{$log_data[4]}','{$log_data[5]}','{$log_data[6]}','{$log_data[7]}','{$log_data[8]}','{$log_data[9]}','{$log_data[10]}','{$log_data[11]}','{$log_data[12]}','{$log_data[13]}','{$log_data[14]}','{$log_data[15]}')";
		$db->query($query);
		$hyouji_log[0] = $db->last_id();
		$query = 'INSERT INTO '.$db->db_pre.'rank_counter VALUES (\''.$hyouji_log[0].'\', \'0\', \'0\')';
		$db->query($query);
		$log_data = $hyouji_log;
		// 登録者のメッセージを保存する設定の場合
		if(($_POST['Fadd_kt'] || $_POST['Fto_admin']) && $cfg_reg['look_mes'] && preg_match('/(\d+)(\w*)/', $cfg_reg['look_mes'], $match)) {
			$i = 0;
			$look_mes_list = array();
			$max = $match[1];
			$fp = fopen('./'.$cfg['log_path'].'look_mes.cgi', 'r');
			while($tmp = fgets($fp)) {
				if($i < $max) {
					array_push($look_mes_list, $tmp);
				} else {
					break;
				}
				$i++;
			}
			fclose($fp);
			// 一括送信する場合
			if($match[2] == 'm' && $i >= $max) {
				$mail_mes = $cfg['search_name'] . ' 登録者からのメッセージ通知';
				foreach($look_mes_list as $tmp) {
					$tlook_mes = explode('<>', $tmp);
					$mail_mes .= <<<EOM
+-------------------------+
登録日：$tlook_mes[1] / お名前：$tlook_mes[5] / Email： $tlook_mes[4]
タイトル：$tlook_mes[7]
URL：
{$tlook_mes[6]}
修正用URL：
{$cfg["cgi_path_url"]}regist_ys.php?mode=enter&id={$tlook_mes[0]}
EOM;
					if(@$tlook_mes[2]) {
						$mail_mes .= "\n新設希望カテゴリ：{$tlook_mes[2]}\n";
					}
					if(@$tlook_mes[3]) {
						$tlook_mes[3] = str_replace('<br>', "\n", $tlook_mes[3]);
						$mail_mes .= "\n" . $tlook_mes[3] . "\n";
					}
				}
				$mail_mes .= "+-------------------------+\n\n";
				require_once $cfg['sub_path'] . "mail_ys.php";
				$PRhonbun = $mail_mes;
				sendmail($cfg['admin_email'], $cfg['admin_email'], $cfg['search_name'].' 登録者からのメッセージ通知('.$max.'件)', 'any', '', '', '', '', '', '', '');
				$i = 0;
				$look_mes_list = array();
			}
			if($i == $max) {
				array_pop($look_mes_list);
			}
			// 新規追加データ($look_mes)を作成
			$look_mes[0] = $log_data[0];
			$look_mes[1] = $log_data[4];
			$look_mes[2] = $_POST['Fadd_kt'];
			$look_mes[3] = $_POST['Fto_admin'];
			$look_mes[4] = $log_data[9];
			$look_mes[4] = str_replace(array("\r\n", "\r", "\n"), '<br>', $look_mes[4]);
			$look_mes[5] = $log_data[8];
			$look_mes[6] = $log_data[2];
			$look_mes[7] = $log_data[1];
			$look_mes = join('<>', $look_mes);
			$look_mes = str_replace(array("\r\n", "\r", "\n"), '', $look_mes);
			$look_mes .= "<>\n";
			array_unshift($look_mes_list, $look_mes);
			$fp = fopen($cfg['log_path'].'look_mes.cgi', 'w');
			foreach ($look_mes_list as $tmp) {
				fputs($fp, $tmp);
			}
			fclose($fp);
		}
		// メールを送信
		if(!isset($_POST['FCmail'])) {
			$_POST['FCmail'] = '';
		}
		if($_POST['FCmail'] != 'no' || $_POST['changer'] != 'admin') { // 送信する設定なら
			// 件名に付けるマークを設定
			if($_POST['Fsougo']) {
				$mail_sougo = '(link)';
			} else {
				$mail_sougo = '';
			}
			if($_POST['Fto_admin']) {
				$mail_com = '(com)';
			} else {
				$mail_com = '';
			}
			if($_POST['Fadd_kt']) {
				$PR_mail_kt = '(kt)';
			} else {
				$PR_mail_kt = '';
			}
			$mail_add_line = $mail_sougo . $mail_com . $PR_mail_kt;
			$log_data[6] = str_replace('<br>', "\n", $log_data[6]);
			$log_data[7] = str_replace('<br>', "\n", $log_data[7]);
			if($cfg['mail_new']) {
				require_once $cfg['sub_path'] . 'mail_ys.php';
				if($cfg['mail_to_admin']) { // 管理人へメール送信
					sendmail($cfg['admin_email'], $log_data[9], $cfg['search_name'].' 新規登録完了通知'.$mail_add_line, 'new', 'admin', $log_data, $_POST['Fsougo'], $_POST['Fadd_kt'], $_POST['Fto_admin'], '', '');
				}
				if($cfg['mail_to_register']) { //登録者へメール送信
					sendmail($log_data[9], $cfg['admin_email'], $cfg['search_name'].' 新規登録完了通知', 'new', '', $log_data, $_POST['Fsougo'], $_POST['Fadd_kt'], $_POST['Fto_admin'], '', '');
				}
			}
		}
		$log_data = $hyouji_log;
		// 登録結果出力
		require $cfg['temp_path'] . 'regist_new_end.html';
	} // </新規登録時>
}
?>