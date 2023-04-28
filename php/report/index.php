<?php
// (2)リンク切れ報告フォーム(&no_link)
if($Form['mode'] == 'no_link') {
	if(!empty($Form['pre'])) {
		if($Form['pre'] == 'on'){
			$Eref = "";
			if(isset($_SERVER['HTTP_REFERER'])){
				$Eref = urlencode($_SERVER['HTTP_REFERER']);
			}
			require $path_set['template']['root'] . '/no_link.html';
            exit;
        }
    }
	$Form['id'] = preg_replace('/\D/', '', $Form['id']);
	if($Form['id']) {
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
			if(isset($Form['type_no_link']) || isset($Form['type_move']) || isset($Form['type_bana_no_link']) || isset($Form['type_ill']) || isset($Form['type_other'])) {
				// コメント
				$com = str_replace(array("\r\n", "\r"), "\n", $Form['com']);
				// 名前
				$name = str_replace(array("\r", "\n"), '', $Form['c_name']);
				// E-Mail
				$email = str_replace(array("\r", "\n"), '', $Form['c_email']);
				// 文字数制限チェック
				if(strlen($com.$name.$email) > 500) {
                    $msg = 'コメント、お名前、E-Mailの文字数は<br>合計250文字(全角換算)以内でご記入ください。';
                    $msgTitle = '文字数オーバー！';
					mes($msg, $msgTitle, 'java');
				}
				if(isset($Form['type_no_link'])) {
					$type_no_link = '1';
				} else {
					$type_no_link = '';
				}
				if(isset($Form['type_move'])) {
					$type_move = '1';
				} else {
					$type_move = '';
				}
				
				if(isset($Form['type_bana_no_link'])) {
					$type_bana_no_link = '1';
				} else {
					$type_bana_no_link = '';
				}
				
				if(isset($Form['type_ill'])) {
					$type_ill = '1';
				} else {
					$type_ill = '';
				}
				
				if(isset($Form['type_other'])) {
					$type_other = '1';
				} else {
					$type_other = '';
				}
				$query = "INSERT INTO {prefix}report VALUES('{$Form["id"]}', '{$_SERVER["REMOTE_ADDR"]}', '{$type_no_link}', '{$type_move}', '{$type_bana_no_link}', '{$type_ill}', '{$type_other}', '{$com}', '{$name}', '{$email}')";
				$db->query($query);
			} else {
                $msg = '「通知種別」に最低一つはチェックしてください。';
                $msgTitle = 'チェックミス';
                mes($msg, $msgTitle, 'java');
            }
        }
    }
	$Form['ref'] = urldecode($Form['ref']);
    $title = $Form['title'];
    $msg = 'ご報告ありがとうございました。<br>'
     . '管理人に「<b>' . $title . '</b>」についての通知を行いました。';
    $msgTitle = 'ご報告ありがとうございます。';
    mes($msg, $msgTitle, $Form['ref']);
}
?>