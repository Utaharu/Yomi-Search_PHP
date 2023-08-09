<?php
// (8)削除実行(act_del)
if($_POST['mode'] == 'act_del') {
	$d = $db->db_pre;
	if(!isset($_POST['del_check'])){$_POST['del_check'] = "";}
	$Cdel = 0;
	if($_POST['del_mode'] == 'single') { // del_mode:single
		if($_POST['del_check'] != 'on') {
			mes('削除確認のためにチェックを入れてから削除ボタンを押してください', '確認チェックをしてください', 'java');
		}
		if($_POST['changer'] != 'admin' && $cfg_reg['no_mente']) {
			mes('現在、登録者による修正・削除は停止されています', 'エラー', 'java');
		}
		if($_POST['changer'] == 'admin') {
			pass_check();
		}
		$fl=0;
		$query = 'SELECT passwd FROM '.$d.'log WHERE id=\''.$_POST['id'].'\' LIMIT 1';
		$Slog = $db->single_num($query);
		if($Slog) {
			if($_POST['changer'] != 'admin') { // 削除する人が登録者の場合
				$cr_pass = crypt($_POST['pass'], $Slog[0]);
				if($cr_pass != $Slog[0]) {
					mes('パスワードの認証に失敗しました', 'エラー', 'java');
				}
			}
                        
                        if(preg_match('/\D/', $_POST['id'])) {
        			mes('IDが正しくありません', 'エラー', 'java');
                        }

			$query = 'DELETE FROM '.$d.'log WHERE id=\''.$_POST['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM '.$d.'rank WHERE id=\''.$_POST['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM '.$d.'rev WHERE id=\''.$_POST['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM '.$d.'rank_counter WHERE id=\''.$_POST['id'].'\'';
			$db->query($query);

			$query = 'DELETE FROM '.$d.'report WHERE id=\''.$_POST['id'].'\'';
			$db->query($query);
                        
		} else {
			mes('該当するデータは見つかりません', 'エラー', 'java');
		}
	} else { // del_mode:multi
		if($_POST['changer'] != 'admin') {
			mes('変更者指定が不正です', 'エラー', 'java');
		}
		pass_check();
		if(!isset($_POST['no_link'])) {
			$_POST['no_link'] = '';
		}
		if(!isset($_POST['dl_check'])) {
			$_POST['dl_check'] = '';
		}
		// リンク切れリストからの削除の場合
		if($_POST['no_link'] == 'on' && $_POST['id']) {
			foreach($_POST['id'] as $id=>$val) {
				if(!$val) {
					continue;
				} elseif($val == 'on') {

                                        if(preg_match('/\D/', $id)) {
                                            mes('IDが正しくありません＞'.$id, 'エラー', 'java');
                                        }
                                        
                			$query = 'DELETE FROM '.$d.'log WHERE id=\''.$id.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM '.$d.'rank WHERE id=\''.$id.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM '.$d.'rev WHERE id=\''.$id.'\'';
					$db->query($query);

                			$query = 'DELETE FROM '.$d.'rank_counter WHERE id=\''.$id.'\'';
					$db->query($query);
				}
              			$query = 'DELETE FROM '.$d.'report WHERE id=\''.$id.'\'';
				$db->query($query);
			}
		}
                
		// デッドリンクチェック済みリストからの削除の場合
		if($_POST['dl_check'] == 'on' && $_POST['id']) {
			if(!is_file($_POST['checkfile'])) {
				mes('ファイル指定が異常です', 'エラー', 'java');
			}
			if(!is_writable($_POST['checkfile'])) {
				mes('ファイル('.$_POST['checkfile'].')のパーミッションを606にしてください', 'エラー', 'java');
			}
			$lines = array();
			$fp = fopen('./'.$_POST['checkfile'], 'r');
				while($tmp = fgets($fp)) {
					$data = explode("\t", $tmp); // id=0<><><>url=13<>\n
					if(!$_POST['id'][$data[0]]) {
						array_push($lines, $tmp);
					}
					if($_POST['id'][$data[0]] == 'on') {
						$_POST['del'][] = $data[0];
					}
				}
			fclose($fp);
			$fp = fopen('./'.$_POST['checkfile'], 'w');
				foreach($lines as $tmp) {
					fputs($fp, $tmp);
				}
			fclose($fp);
			if($_POST['del']) {
				foreach($_POST["del"] as $del) {

                                        if(preg_match('/\D/', $del)) {
                                            mes('削除IDが正しくありません＞'.$del, 'エラー', 'java');
                                        }

                			$query = 'DELETE FROM '.$d.'log WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM '.$d.'rank WHERE id=\''.$del.'\'';
					$db->query($query);
                                        
                			$query = 'DELETE FROM '.$d.'rev WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM '.$d.'rank_counter WHERE id=\''.$del.'\'';
					$db->query($query);

                			$query = 'DELETE FROM '.$d.'report WHERE id=\''.$del.'\'';
					$db->query($query);
				}
			}
		}
	}
	if($_POST['changer'] == 'admin' && ($_POST['no_link'] == 'on' || $_POST['dl_check'] == 'on')) {
		mes('削除処理が完了しました', '削除完了', 'kanri');
	} else {
		mes('削除処理が完了しました', '削除完了', $cfg['home']);
	}
}
?>