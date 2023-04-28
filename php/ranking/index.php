<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 Ranking Page
--------------------------------------------------------------------------*/

//DB - Connect
require_once $script['lib']['db']['main'];
if(!isset($db)){
	$db = new DB_Connecter($db_set['host'],$db_set['user'],$db_set['password'],$db_set['name'],$db_set['prefix'],$db_set['charset']);

	//Load Script Settings
	require_once $script['lib']['db']['get_setting'];
	$Settings = new Get_Settings($db);
	$cfg = array();
	$cfg = $Settings->Common;
	$cfg_reg = array();
	$cfg_reg = $Settings->Register;
	$text = array();
	$text = $Settings->Description;
}

require_once $script['lib']['function'];
require_once $script['lib']['plugin']['ad'];
require_once $script['lib']['plugin']['navigation'];

// #-- 目次 --#
// (1)リンクジャンプ処理(link)
// (1.1)アクセスジャンプ処理(r_link)
// (2)キーワードランキング表示画面(PR_keyrank)
// (3)アクセス(IN)ランキング表示画面(PR_rev)
// (4)人気(OUT)ランキング表示画面(PR_rank)

// ページ設定
if(isset($Form['page'])) {
        if(preg_match('/^\d+$/', $Form['page'])==false) {
            $Form['page'] = 1;
        }
	if($Form['page'] < 1 or $Form['page'] > 1000) {
		$Form['page'] = 1;
	}
} else {
	$Form['page']=1;
}


if(isset($Form['mode'])) {
	// (1)リンクジャンプ処理(link)
	switch($Form['mode']){
		case 'link' :
			$Form['id'] = preg_replace('/\D/', '', $Form['id']);
			if($Form['id']){
				// refererチェック
				// refererが無いときにカウントしない場合にはこのif文を削除
				if(!$_SERVER['HTTP_REFERER']) {
					$fl = 1;
				}
				$ref_list = explode(',', $cfg['rank_ref']);
				if(!$cfg['rank_ref']) {
					$fl = 1;
				} else {
					foreach($ref_list as $tmp){
						if(strstr($_SERVER['HTTP_REFERER'], $tmp)) {
							$fl = 1;
						}
					}
				}

				if($fl) {
					$time = time();
					$query = 'SELECT id FROM {prefix}rank WHERE id=\''.$Form['id'].'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - $cfg['rank_time'] * 3600);
					$tmp = $db->single_num($query);
					if(!$tmp) {
						$query = 'INSERT INTO {prefix}rank (id,time,ip) VALUES (\''.$Form['id'].'\', \''.$time.'\' ,\''.$_SERVER['REMOTE_ADDR'].'\')';
						$result = $db->query($query);
						$query = "UPDATE {prefix}rank_counter SET rank=rank+1 WHERE id='{$Form["id"]}'";
						$db->query($query);
					}
				}
		
				//リンク先URLの引き当て
				$getid = (int)$Form['id'];
				$query = 'SELECT url FROM {prefix}log WHERE id=\''.$getid.'\'';
				$url = $db->single_num($query) or $db->error('Query failed '.$query.__FILE__.__LINE__);
				//リンク先へ転送
				if(isset($url[0])){
					location($url[0]);
					exit();
				}
				unset($url);
				unset($getid);
			}
			mes("該当するリンクが有りません","エラー","java");
			exit;
		break;
		case 'r_link' :
			// (1.1)アクセスジャンプ処理(r_link)
			if($cfg['rev_fl']) {
				$Form['id'] = preg_replace("/\D/", "", $Form['id']);
				if($Form['id']){
					$query = 'SELECT COUNT(id) FROM {prefix}log WHERE id=\''.$Form['id'].'\'';
					$site_id = $db->single_num($query);
					if(isset($site_id[0])) {					$time = time();
						$Form['id'] = str_replace("\n", "", $Form['id']);
						$query = 'SELECT id FROM {prefix}rev WHERE id=\''.$Form['id'].'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - $cfg['rank_time'] * 3600);
						$tmp = $db->single_num($query);
						if(!$tmp) {
							$query = 'INSERT INTO {prefix}rev (id,time,ip) VALUES (\''.$Form['id'].'\', \''.$time.'\' ,\''.$_SERVER['REMOTE_ADDR'].'\')';
							$result = $db->query($query);
							$query = 'UPDATE {prefix}rank_counter SET rev=rev+1 WHERE id=\''.$Form['id'].'\'';
							$db->query($query);
						}
					}
				}
			}
			$cfg['location'] = 0; // refreshジャンプにする
			location($cfg['rev_url']);
			exit;
		break;
		case 'keyrank' :
			// (2)キーワードランキング表示画面(keyrank)
			require $script['lib']['ranking']['keyword'];
		break;
		// (3)アクセス(IN)ランキング表示画面(rev)
		case 'rev' :
		case 'rev_bf' :
		case 'rev_rui' :
		default :
			if(!$cfg['rev_fl']) {
				mes('アクセスランキングは実施しない設定になっています','エラー','java');
			}
			require $script['lib']['ranking']['access'];
		break;
	}
}else{
	// (4)人気ランキング表示画面
	if(!$cfg['rank_fl']) {
		mes('人気ランキングは実施しない設定になっています','エラー','java');
	}
	if(!isset($Form['mode'])) {
		$Form['mode'] = 'rank_ys';
	}
	require $script['lib']['ranking']['access'];
}

?>