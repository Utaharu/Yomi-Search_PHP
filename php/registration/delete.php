<?php
// (8)削除実行(act_del)
if($Form['mode'] == 'act_del') {
	if(!isset($Form['del_check'])){$Form['del_check'] = "";}
	$Cdel = 0;
	if($Form['del_mode'] == 'single') { // del_mode:single
		if($Form['del_check'] != 'on') {
			mes('削除確認のためにチェックを入れてから削除ボタンを押してください', '確認チェックをしてください', 'java');
		}
		if($Form['changer'] != 'admin' && $cfg_reg['no_mente']) {
			mes('現在、登録者による修正・削除は停止されています', 'エラー', 'java');
		}
		if($Form['changer'] == 'admin') {
			pass_check();
		}
		$fl=0;
		$query = 'SELECT passwd FROM {prefix}log WHERE id=\''.$Form['id'].'\' LIMIT 1';
		$Slog = $db->single_num($query);
		if($Slog) {
			if($Form['changer'] != 'admin') { // 削除する人が登録者の場合
				$cr_pass = crypt($Form['pass'], $Slog[0]);
				if($cr_pass != $Slog[0]) {
					mes('パスワードの認証に失敗しました', 'エラー', 'java');
				}
			}
                        
                        if(preg_match('/\D/', $Form['id'])) {
        			mes('IDが正しくありません', 'エラー', 'java');
                        }

			$query = 'DELETE FROM {prefix}log WHERE id=\''.$Form['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM {prefix}rank WHERE id=\''.$Form['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM {prefix}rev WHERE id=\''.$Form['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM {prefix}rank_counter WHERE id=\''.$Form['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM {prefix}report WHERE id=\''.$Form['id'].'\'';
			$db->query($query);
                        
		} else {
			mes('該当するデータは見つかりません', 'エラー', 'java');
		}
	} else { // del_mode:multi
		if($Form['changer'] != 'admin') {
			mes('変更者指定が不正です', 'エラー', 'java');
		}
		pass_check();
		// リンク切れリストからの削除の場合
		if($Form['no_link'] == 'on' && $Form['id']) {
			foreach($Form['id'] as $id=>$val) {
				if(!$val) {
					continue;
				} elseif($val == 'on') {

                                        if(preg_match('/\D/', $id)) {
                                            mes('IDが正しくありません＞'.$id, 'エラー', 'java');
                                        }
                                        
                			$query = 'DELETE FROM {prefix}log WHERE id=\''.$id.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM {prefix}rank WHERE id=\''.$id.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM {prefix}rev WHERE id=\''.$id.'\'';
					$db->query($query);

                			$query = 'DELETE FROM {prefix}rank_counter WHERE id=\''.$id.'\'';
					$db->query($query);
				}
              			$query = 'DELETE FROM {prefix}report WHERE id=\''.$id.'\'';
				$db->query($query);
			}
		}
                
		// デッドリンクチェック済みリストからの削除の場合
		if($Form['dl_check'] == 'on' && $Form['id']) {
			if(!is_file($Form['checkfile'])) {
				mes('ファイル指定が異常です', 'エラー', 'java');
			}
			if(!is_writable($Form['checkfile'])) {
				mes('ファイル('.$Form['checkfile'].')のパーミッションを606にしてください', 'エラー', 'java');
			}
			$lines = array();
			$fp = fopen('./'.$Form['checkfile'], 'r');
				while($tmp = fgets($fp)) {
					$data = explode("\t", $tmp); // id=0<><><>url=13<>\n
					if(!$Form['id'][$data[0]]) {
						array_push($lines, $tmp);
					}
					if($Form['id'][$data[0]] == 'on') {
						$Form['del'][] = $data[0];
					}
				}
			fclose($fp);
			$fp = fopen('./'.$Form['checkfile'], 'w');
				foreach($lines as $tmp) {
					fputs($fp, $tmp);
				}
			fclose($fp);
			if($Form['del']) {
				foreach($Form["del"] as $del) {

                                        if(preg_match('/\D/', $del)) {
                                            mes('削除IDが正しくありません＞'.$del, 'エラー', 'java');
                                        }

                			$query = 'DELETE FROM {prefix}log WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM {prefix}rank WHERE id=\''.$del.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM {prefix}rev WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM {prefix}rank_counter WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM {prefix}report WHERE id=\''.$del.'\'';
					$db->query($query);
				}
			}
		}
	}
	if(!isset($Form['no_link'])) {
		$Form['no_link'] = '';
	}
	if(!isset($Form['dl_check'])) {
		$Form['dl_check'] = '';
	}
	if($Form['changer'] == 'admin' && ($Form['no_link'] == 'on' || $Form['dl_check'] == 'on')) {
		mes('削除処理が完了しました', '削除完了', 'kanri');
	} else {
		mes('削除処理が完了しました', '削除完了', $cfg['home']);
	}
}
?>