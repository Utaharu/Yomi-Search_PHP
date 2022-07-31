<?php
// (2)リンク切れ報告フォーム(&no_link)
if($_REQUEST['mode'] == 'no_link') {
	if(!empty($_REQUEST['pre'])) {
		if($_GET['pre'] == 'on') {
			if(get_magic_quotes_gpc()) {
				$_GET['title'] = stripslashes($_GET['title']);
            }
			$Eref = urlencode($_SERVER['HTTP_REFERER']);
			header('Content-type: text/html; charset=UTF-8');
			require $cfg['temp_path'] . 'no_link.html';
            exit;
        }
    }
	$_POST['id'] = preg_replace('/\D/', '', $_POST['id']);
	if($_POST['id']) {
		$ip_fl = 1;
		if($cfg['no_link_ip']) {
			$ip = explode(',', $cfg['no_link_ip']);
			foreach($ip as $tmp) {
				if(strstr($_SERVER['REMOTE_ADDR'], $tmp)) {
					$ip_fl = 0;
					break;
				}
			}
		}
		if($ip_fl) {
			// 報告種別(リンク切れ=0/サイト移転=1/バナーリンク切れ=2/規約違反=3/その他=4)
			if(isset($_POST['type_no_link']) || isset($_POST['type_move']) || isset($_POST['type_bana_no_link']) || isset($_POST['type_ill']) || isset($_POST['type_other'])) {
				// コメント
				$com = str_replace(array("\r\n", "\r"), "\n", $_POST['com']);
				// 名前
				$name = str_replace(array("\r", "\n"), '', $_POST['c_name']);
				// E-Mail
				$email = str_replace(array("\r", "\n"), '', $_POST['c_email']);
				// 文字数制限チェック
				if(strlen($com.$name.$email) > 500) {
                    $msg = 'コメント、お名前、E-Mailの文字数は<br>合計250文字(全角換算)以内でご記入ください。';
                    $msgTitle = '文字数オーバー！';
					mes($msg, $msgTitle, 'java');
				}
				if(isset($_POST['type_no_link'])) {
					$type_no_link = '1';
				} else {
					$type_no_link = '';
				}
				if(isset($_POST['type_move'])) {
					$type_move = '1';
				} else {
					$type_move = '';
				}
				
				if(isset($_POST['type_bana_no_link'])) {
					$type_bana_no_link = '1';
				} else {
					$type_bana_no_link = '';
				}
				
				if(isset($_POST['type_ill'])) {
					$type_ill = '1';
				} else {
					$type_ill = '';
				}
				
				if(isset($_POST['type_other'])) {
					$type_other = '1';
				} else {
					$type_other = '';
				}
				$query = "INSERT INTO {$db->db_pre}report VALUES('{$_POST["id"]}', '{$_SERVER["REMOTE_ADDR"]}', '{$type_no_link}', '{$type_move}', '{$type_bana_no_link}', '{$type_ill}', '{$type_other}', '{$com}', '{$name}', '{$email}')";
				$db->query($query);
			} else {
                $msg = '「通知種別」に最低一つはチェックしてください。';
                $msgTitle = 'チェックミス';
                mes($msg, $msgTitle, 'java');
            }
        }
    }
	$_POST['ref'] = urldecode($_POST['ref']);
	if (get_magic_quotes_gpc()) {
        $title = stripslashes($_POST['title']);
	} else {
        $title = $_POST['title'];
    }
    $msg = 'ご報告ありがとうございました。<br>'
     . '管理人に「<b>' . $title . '</b>」についての通知を行いました。';
    $msgTitle = 'ご報告ありがとうございます。';
    mes($msg, $msgTitle, $_POST['ref']);
}
?>