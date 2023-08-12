<?php
// (2)リンク切れ報告フォーム(&no_link)
if($Form['mode'] == 'no_link') {
	if($Form['pre'] == 'on'){
		$Eref = "";
		if(isset($_SERVER['HTTP_REFERER'])){
			$Eref = urlencode($_SERVER['HTTP_REFERER']);
		}
		require $path_set['template']['root'] . '/no_link.html';
		exit;
	}
	
	$Form['id'] = preg_replace('/\D/', '', $Form['id']);
	// コメント
	$com = str_replace(array("\r\n", "\r"), "\n", $Form['com']);
	$com = htmlspecialchars($com,ENT_HTML5 and ENT_QUOTES);
	// 名前
	$name = str_replace(array("\r", "\n"), '', $Form['c_name']);
	$name = htmlspecialchars($name,ENT_HTML5 and ENT_QUOTES);
	// E-Mail
	$email = str_replace(array("\r", "\n"), '', $Form['c_email']);
	$email = htmlspecialchars($email,ENT_HTML5 and ENT_QUOTES);
	
	if(!$Form['id'] or !is_numeric($Form['id'])){
		mes("IDが入力されていません。","リンク切れ報告エラー","java");
	}elseif(!isset($Form['type_no_link']) and !isset($Form['type_move']) and !isset($Form['type_bana_no_link']) and !isset($Form['type_ill']) and !isset($Form['type_other'])){
		// 報告種別のチェック無し(リンク切れ=0/サイト移転=1/バナーリンク切れ=2/規約違反=3/その他=4)
		mes("「通知種別」に最低一つはチェックしてください。", "リンク切れ報告エラー", 'java');
	}elseif(strlen($com.$name.$email) > 500){
		// 文字数制限チェック
		mes("コメント、お名前、E-Mailの文字数は<br>合計250文字(全角換算)以内でご記入ください。", "リンク切れ報告エラー", 'java');
	}else{
		/*** 正規表現でのチェックに変更した方が良いかも。　***/
		//通知拒否するIPアドレスか確認
		if($cfg['no_link_ip']){
			$ip_list = explode(',', $cfg['no_link_ip']);
			$ip_flag = array_search($_SERVER['REMOTE_ADDR'],$ip_list);
			
			if($ip_flag !== False){
				mes("不正な要求です。","リンク切れ報告エラー","java");
				exit;
			}
		}
		
		//リンクIDの存在チェック
		$link_id = (int)$Form['id'];
		$query = "SELECT id FROM {prefix}log WHERE id='{$link_id}'";
		$result = $db->single_num($query);
		if(!isset($result[0]) or !is_numeric($result[0]) or $result[0] != $link_id or $link_id <= 0){
			mes("不明な報告の為、処理を行えませんでした。","リンク切れ報告エラー",'java');
			exit;
		}
		
		//reportログのチェック
		$remote_ip = $_SERVER['REMOTE_ADDR'];
		$query = "SELECT id,ip FROM {prefix}report WHERE ip='{$remote_ip}'";
		$result = $db->rowset_assoc($query);
		if(is_array($result)){
			//同一IPからの報告数制限
			if(count($result) > 5){
				mes("受付できません。管理者に、直接ご報告お願いいたします。","リンク切れ報告エラー","java");
				exit;
			}
			//同じLinkIDへの多重報告
			foreach($result as $line){
				if($line and $line['id'] == $link_id){
					mes("既に受付済みです","リンク切れ報告エラー",'java');
					exit;
				}
			}
		}		
	 /*** /////////////////////////////// ***/

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
		
		//報告の保存。
		$query = "INSERT INTO {prefix}report VALUES('{$link_id}', '{$remote_ip}', '{$type_no_link}', '{$type_move}', '{$type_bana_no_link}', '{$type_ill}', '{$type_other}', '{$com}', '{$name}', '{$email}')";
		$db->query($query);
		
		$Form['ref'] = urldecode($Form['ref']);
		$msg = 'ご報告ありがとうございました。<br>'
		 . '管理人に「<b>' . $link_id. '</b>」についての通知を行いました。';
		$msgTitle = 'ご報告ありがとうございます。';
		mes($msg, $msgTitle, $Form['ref']);
	}
}
?>