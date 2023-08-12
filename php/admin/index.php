<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 AdministratorRoom Page
--------------------------------------------------------------------------*/
//DB - Connect
require $script['lib']['db']['main'];
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

require $script['lib']['function'];
require $script['lib']['plugin']['cookie'];
require_once $script['lib']['plugin']['navigation'];

$PR_menu_bar = "";
if(isset($text['menu_bar'])){$PR_menu_bar = $text['menu_bar'];}

// 登録申請中(仮登録状態)のログ件数を取得して
// 管理室用のリストボックスを生成
$count_temp = $db->log_count('log_temp');

// (1)ログイン画面(&login)
// header("Content-type: text/html; charset=UTF-8");
if(isset($Form["mode"])) {
	require $script['lib']['admin']['listbox'];

if($Form["mode"] == "kanri") {
	// (2)管理人室(&kanri)
	// パスワードチェック
	if($cfg["pass"] != "setup") {
		pass_check();
	}
	// クッキーの設定
	$CK_data = Cookie::get_cookie();
	if(isset($Form["set"])) {
		if($Form["set"] == "設定") {
			$CK_data[4] = 1;
		} else {
			$CK_data[4] = 0;
		}
		Cookie::set_cookie($CK_data);
	}
	if($CK_data[4]) {
		$PRset="設定";
	} else {
		$PRset="解除";
	}
	require $cfg["temp_path"] . "admin/admin.html";
	exit;
} elseif($Form["mode"] == "cfg_kanri") {
	// (2.1)認証設定画面表示(cfg_kanri)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_kanri.html";
	exit;
} elseif($Form["mode"] == "temp_to_regist") {
	// (5)登録待ち表示画面(&temp_to_regist)
	pass_check();
	$count_temp = $db->log_count("log_temp");
	$Ekreg = 10; // 表示数
	$size_url_w = 50; // URL/バナーURL/キーワードの横サイズ
	$size_syoukai_w = 40; // 紹介文/管理人コメント/メッセージの横サイズ
	if(!isset($Form["pre_no"])) {
		$Form["pre_no"] = 1;
	}
	if(isset($Form["page"])) {
		if($Form["page"] == "次のページ") {
			$Form["pre_no"] = $Form["pre_no"] + $Ekreg;
		} elseif($Form["page"] == "前のページ") {
			$Form["pre_no"] = $Form["pre_no"] - $Ekreg;
		}
	}
	$sql = "SELECT path FROM {prefix}category ORDER BY path";
	$rowset = $db->rowset_num($sql);
	$PRgane_list = "";
	foreach($rowset as $path) {
		$PRgane_list .="<option value=\"{$path[0]}\">" . full_category($path[0]) . "</option>\n";
	}
	$sql = "SELECT * FROM {prefix}log_temp";
	$rowset = $db->rowset_num($sql);
	require $cfg["temp_path"] . "admin/temp_to_regist.html";
	exit;
} elseif($Form["mode"] == "temp_to_regist_act") {
	// (5.1)登録待ちの処理決定実行(&temp_to_regist_act)
	pass_check();
	$Slog = array(); // 登録データの一時保存用
	// メール送信用ライブラリを読み込み
	if($cfg['mail_new']) {
		require_once $script['lib']['pulgin']['mailer'];
	}
	$sql = "SELECT id, passwd, ip FROM {prefix}log_temp";
	$rowset = $db->rowset_assoc($sql);
	foreach ($rowset as $Tlog) {
		if ($Form['R'][$Tlog['id']] == "reg") { // 登録
			// (t1)フォーム入力データを書き込みデータに反映(仮登録→正規登録用)
			//    (&form_to_temp)
			// タイトル(1)
			$Slog[1] = $Form['Ftitle'][$Tlog['id']];
			// URL(2)
			$Slog[2] = $Form['Furl'][$Tlog['id']];
			// マークデータ(3)
			$Slog[3] = "";
			for ($i = 1; $i <= 10; $i++) { // ←マーク数を増やす場合には修正
				if(!isset($Form["Fmark{$i}"][$Tlog["id"]])) {
					$Form["Fmark{$i}"][$Tlog["id"]] = 0;
				}
				$Slog[3] .= $Form["Fmark{$i}"][$Tlog["id"]] . "_";
			}
			$Slog[3] = substr($Slog[3], 0, -1);
			// 更新日(4)
			$Slog[4] = get_time(0, 1);
			// パスワード(5)
			// (未変更)
			$Slog[5] = $Tlog["passwd"];
			// 紹介文(6)
			$Slog[6] = $Form["Fsyoukai"][$Tlog["id"]];
			$Slog[6] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Slog[6]);
			// 管理人コメント(7)
			$Slog[7] = $Form["Fkanricom"][$Tlog["id"]];
			$Slog[7] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Slog[7]);
			// お名前(8)
			$Slog[8] = $Form["Fname"][$Tlog["id"]];
			// E-Mail(9)
			$Slog[9] = $Form["Femail"][$Tlog["id"]];
			// カテゴリ(10)
			$Slog[10]="&";
			for($i=1; $i <= $cfg_reg["kt_max"]; $i++) {
				$Slog[10] .= $Form["Fkt{$i}"][$Tlog["id"]] . "&";
			}
			// time形式(11)
			$Slog[11] = time();
			// バナーURL(12)
			$Slog[12] = $Form["Fbana_url"][$Tlog["id"]];
			// 更新フラグ(13)
			$Slog[13] = 0;
			// IP(14)
			$Slog[14] = $Tlog["ip"];
			// キーワード(15)
			$Slog[15] = $Form["Fkey"][$Tlog["id"]];
			foreach($Slog as $key=>$val) {
				$Slog[$key] = $db->escape_string($val);
			}
			$sql = "INSERT INTO {prefix}log VALUES(NULL,'{$Slog[1]}','{$Slog[2]}','{$Slog[3]}','{$Slog[4]}','{$Slog[5]}','{$Slog[6]}','{$Slog[7]}','{$Slog[8]}','{$Slog[9]}','{$Slog[10]}','{$Slog[11]}','{$Slog[12]}','{$Slog[13]}','{$Slog[14]}','{$Slog[15]}')";
			$db->query($sql);
			$Slog[0] = $db->last_id();
			$sql = "INSERT INTO {prefix}rank_counter VALUES ('{$Slog[0]}', '0', '0')";
			$db->query($sql);
			if($cfg["mail_new"]) {
				// 仮登録→新規登録時のメールを送信
				$Slog[6] = str_replace("<br>", "\n", $Slog[6]);
				$Slog[7] = str_replace("<br>", "\n", $Slog[7]);
				if(!isset($Form["Fadd_kt"][$Tlog["id"]])) {
					$Form["Fadd_kt"][$Tlog["id"]] = "";
				}
				if(!isset($Form["Fmark2"][$Tlog["id"]])) {
					$Form["Fmark2"][$Tlog["id"]] = 0;
				}
				if($cfg['mail_to_admin']) { // 管理人へメール送信
					sendmail($cfg['admin_email'], $Slog[9], "{$cfg['search_name']} 新規登録完了通知", "new", "admin", $Slog, $Form['Fmark2'][$Tlog['id']], $Form['Fadd_kt'][$Tlog['id']], $Form['Fto_admin'][$Tlog['id']], $Form['Fto_reg'][$Tlog['id']], "");
				}
				if($cfg['mail_to_register']) { // 登録者へメール送信
					sendmail($Slog[9], $cfg['admin_email'], "{$cfg['search_name']} 新規登録完了通知", "new", "", $Slog, $Form['Fmark2'][$Tlog['id']], $Form['Fadd_kt'][$Tlog['id']], $Form['Fto_admin'][$Tlog['id']], $Form['Fto_reg'][$Tlog['id']], "");
				}
			}
		}
		if($Form["R"][$Tlog["id"]] == "reg" || $Form["R"][$Tlog["id"]] == "del") {
			$sql = "DELETE FROM {prefix}log_temp WHERE id='{$Tlog["id"]}'";
			$result = $db->query($sql);
		}
	}
	mes("仮登録データの処理が完了しました", "仮登録データ処理完了", "kanri");
	exit;
} elseif($Form["mode"] == "key_cfg") {
	// (6)キーワードランキングの設定(key_cfg)
	pass_check();
	// ラジオボックスの設定
	// キーワードランキング(実施しない=0/実施する=1)
	$sql = "SELECT k.word wd,count(*) pt,r.word rwd,r.open_key opn,r.bad_key bad,r.view_word view FROM {prefix}key AS k LEFT JOIN {prefix}key_rank AS r ON k.word=r.word GROUP BY k.word ORDER BY pt DESC";
	$rowset = $db->rowset_assoc($sql);
	foreach($rowset as $row) {
		if(!$row["rwd"]) {
			$row['wd'] = $db->escape_string($row['wd']);
			$sql = "INSERT INTO {prefix}key_rank VALUES('{$row['wd']}','','','')";
			$db->query($sql);
		}
	}
	require $cfg["temp_path"] . "admin/key_cfg.html";
	exit;
} elseif($Form["mode"] == "key_cfg_act") {
	// (6.1)キーワードランキングの設定実行(&key_cfg_act)
	pass_check();
	if($Form["change"]) {
		foreach($Form["change"] as $key=>$val) {
			if($val or (isset($Form['view']) and isset($Form['view'][$key]) and $Form['view'][$key])) {
				$view = "";
				if((isset($Form['view']) and isset($Form['view'][$key]) and $Form['view'][$key])){$view = $db->escape_string($Form['view'][$key]);}
				$key = $db->escape_string($key);
				$opn = ($val == "open") ? "1" : NULL;
				$bad = ($val == "bad") ? "1" : NULL;
				$sql = "UPDATE {prefix}key_rank SET open_key='{$opn}',bad_key='{$bad}',view_word='{$view}' WHERE word='{$key}'";
				$db->query($sql);
				if($val == "del") {
					$sql = "DELETE FROM {prefix}key WHERE word='{$key}'";
					$db->query($sql);
					$sql = "DELETE FROM {prefix}key_rank WHERE word='{$key}'";
					$db->query($sql);
				}
			}
		}
	}
	mes("キーワード表示設定の変更が完了しました", "キーワード表示設定の変更完了", "kanri");
	exit;
} elseif($Form["mode"] == "key_del_act") {
	pass_check();
	$sql = "SELECT k.word wd,count(*) pt FROM {prefix}key AS k LEFT JOIN {prefix}key_rank AS r ON k.word=r.word GROUP BY k.word HAVING pt<={$Form["del_max"]}";
	$rowset = $db->rowset_assoc($sql);
	foreach($rowset as $row) {
        $row['wd'] = mysql_real_escape_string($row['wd']);
        $sql = "DELETE FROM {prefix}key WHERE word='{$row["wd"]}'";
		$db->query($sql);
		$sql = "DELETE FROM {prefix}key_rank WHERE word='{$row["wd"]}'";
		$db->query($sql);
	}
	mes("キーワード削除が完了しました", "キーワード削除完了", "kanri");
	exit;
} elseif($Form["mode"] == "key_cfg_del_word_act") {
	// (6.2)キーワードランキングの集計対象外のキーワードを一括登録実行(&key_cfg_del_word_act)
	pass_check();
	$del_key_list = explode(",", $Form['del_key_list']);
	if(is_array($del_key_list)){
		foreach($del_key_list as $key){
				$key = str_replace("\n","",$key);
				if($key){
					$key = $db->escape_string($key);
					//key_rank table check
					$sql = "SELECT * FROM {prefix}key_rank WHERE word='{$key}'";
					$rowset = $db->single_assoc($sql);
					if($rowset){//update
					$sql = "UPDATE {prefix}key_rank SET open_key='',bad_key='1' WHERE word='{$key}'";
					$db->query($sql);
				}else{//add
					$sql = "INSERT INTO {prefix}key_rank VALUES ('{$key}','','1','')";
					$db->query($sql);
				}
				//key table Check
				$sql = "SELECT word FROM {prefix}key WHERE word='{$key}'";
				$tmp = $db->single_num($sql);
				if(!$tmp){
					$time = time();
					$sql = "INSERT INTO {prefix}key (word, time, ip) VALUES ('{$key}','{$time}','{$_SERVER['REMOTE_ADDR']}')";
					$db->query($sql);
				}
			}
		}
	}
	mes("集計対象外のキーワードの一括登録が完了しました", "登録完了", "kanri");
	exit;
} elseif($Form["mode"] == "log_conv") {
	// (7)各種ログ変換(&log_conv)
	pass_check();
	require $cfg["temp_path"] . "admin/log_conv.html";
	exit;
} elseif($Form["mode"] == "log_conv_act") {
	// (7.1)各種ログ変換実行(&log_conv_act)
	pass_check();
	if($Form["check"] != "on") {
		mes("確認チェックがされていません。<br>戻ってチェックしてから実行してください。", "チェックエラー", "java");
	}
	if(!is_file($Form["bf_file"])) {
		mes("エラー：{$bf_file} が見つかりません", "ファイルが見つかりません", "java");
	} else {
		if($Form["log_mode"] == "v4todb") {
			$fp = fopen($Form["bf_file"], "r");
			while($line = fgets($fp)) {
				$line = mb_convert_encoding($line, "UTF-8", "SJIS");
				$line = $db->escape_string($line);
				$Slog = explode("<>", $line);
				$rank = explode("_", $Slog[13]);
				$sql = "INSERT INTO {prefix}rank_counter VALUES ('{$Slog[0]}', '{$rank[1]}', '{$rank[3]}')";
				$db->query($sql);
				$Slog[10] = str_replace("_", "/", $Slog[10]);
				$tmp = explode("&", $Slog[10]);
				$Slog[10] = "";
				foreach($tmp as $val) {
					if($val) {
						$Slog[10] .= "&{$val}/";
					}
				}
				$Slog[10] = $Slog[10] . "&";
				$Slog[13] = substr($Slog[11], -1);
				$Slog[11] = substr($Slog[11], 0, -2);
				$sql = "INSERT INTO {prefix}log"
				           . " VALUES ('{$Slog[0]}', '{$Slog[1]}', '{$Slog[2]}', '{$Slog[3]}', '{$Slog[4]}', '{$Slog[5]}', '{$Slog[6]}', '{$Slog[7]}', '{$Slog[8]}', '{$Slog[9]}', '{$Slog[10]}', '{$Slog[11]}', '{$Slog[12]}', '{$Slog[13]}', '{$Slog[14]}', '{$Slog[15]}')";
				$db->query($sql);
			}
			fclose($fp);
			$PR_msg = "Ver4形式→データベースへの変換が完了しました";
		}
	}
	mes("$PR_msg", "変換終了", "kanri");
	exit;
} elseif($Form['mode'] == 'log_conv_kt_sort') {
	// (7.3)カテゴリ・ソート変換実行(&log_conv_kt_sort)
	function sort_category($id, $path) {
		global $cfg, $db;
		$change_path = array();
		$sql = "SELECT id, path, sort_name"
		           . " FROM {prefix}category"
			       . " WHERE up_id='{$id}'"
			       . " ORDER BY sort_name, path";
		$rowset = $db->rowset_assoc($sql) or $db->error("Query failed {$sql}".__FILE__.__LINE__);
		$i = 1;
		foreach ($rowset as $row) {
			if ($row['id']) {
				$len = strlen(basename($row['path']));
				$new_path = $path . sprintf("%0{$len}d" , $i) . '/';
				$i++;
				if ($row['path'] != $new_path) {
					$sql = "UPDATE {prefix}category"
					           . " SET path = '{$new_path}'"
							   . " WHERE id = '{$row['id']}'";
					$db->query($sql) or $db->error("Query failed {$sql}".__FILE__.__LINE__);
					$change_path[$new_path] = $row['path'];
				}
				$change_path = array_merge($change_path, sort_category($row['id'], $new_path));
			}
		}
		return $change_path;
	}
	pass_check();
	$del_list = array();
	$change_path = array();
	if($Form['check'] != 'on') {
		mes('確認チェックにチェックしてから変換ボタンを押してください', 'チェックミス', 'java');
	}
	if($Form['all'] == 'on') {
		// $Form['all']の処理
		$change_path = sort_category(0, '');
	} elseif ($Form['kt_str']) {
		// $Form[kt_str]を解析
		if(preg_match("/[^\w\-\,]/", $Form['kt_str'])) {
			mes('カテゴリ指定文に全角文字が含まれています', 'エラー', 'java');
		}
		$kt_str = explode(',', $Form['kt_str']);
		foreach($kt_str as $tmp) {
			if(preg_match("/^(\d+)(n*)\-(\d+)(n*)$/", $tmp, $match)) {
				$kt1 = $match[1];
				$kt2 = $match[3];
				$n_fl = 0;
				if($match[4] == 'n') {
					$n_fl = 1;
				}
				$keta1 = strlen($kt1);
				$keta2 = strlen($kt2);
				if($keta1 != $keta2) {
					mes('カテゴリ指定文が間違っています：<b>'.$tmp.'</b>', 'エラー', 'java');
				}
				$i = 1;
				while($kt1 != $kt2) {
					$kt1_j = sprintf("%d", $kt1);
					if(!$n_fl) {
						$change[] = $kt1 . '/';
					} else {
						$del_list[] = $kt1 . '/';
					}
					$kt1_j++;
					$kt1 = sprintf("%0{$keta1}d", $kt1_j);
					$i++;
					if($i > 5000) {
						mes('<b>-</b> で 5000以上の連続するカテゴリを指定することはできません', 'エラー', 'java');
					}
				}
				if(!$n_fl) {
					$change[] = $kt2 . '/';
				} else {
					$del_list[] = $kt2 . '/';
				}
			} elseif(preg_match("/(\d+)(n*)/", $tmp, $match)) {
				if($match[2] == 'n') {
					$del_list = $match[1] . '/';
				} else {
					$change[] = $match[1] . '/';
				}
			} else {
				mes('カテゴリ指定文が間違っています', 'エラー', 'java');
			}
		}
		$change = array_diff($change, $del_list);
		$change = array_unique($change);
		sort($change);
		reset($change);
		// カテゴリを更新
		foreach($change as $path) {
			$sql = "SELECT id"
			           . " FROM {prefix}category"
					   . " WHERE path='{$path}'"
					   . " LIMIT 1";
			$id = $db->single_num($sql);
			$change_path = array_merge($change_path, sort_category($id[0], $path));
		}
	}
	// 本体ログを更新
	if (!empty($change_path)) {
		$change_path_where = array();
		foreach ($change_path as $val) {
			$change_path_where[] = "(category LIKE '%&{$val}&%')";
		}
		$sql = "SELECT id, category"
		           . " FROM {prefix}log"
				   . " WHERE " . implode(' or ' , $change_path_where);
		$rowset = $db->rowset_assoc($sql) or $db->error("Query failed {$sql}".__FILE__.__LINE__);
		foreach($rowset as $row) {
			$kt_list = explode('&', $row['category']);
			foreach ($kt_list as $key => $val) {
				if (!empty($val)) {
					$new_path = array_search($val, $change_path);
					if (!empty($new_path)) {
						$val = $new_path;
					}
					$kt_list[$key] = $val;
				}
			}
			$row['category'] = implode('&', $kt_list);
			$sql = "UPDATE {prefix}log"
			           . " SET category='{$row['category']}'"
					   . " WHERE id='{$row['id']}'";
			$db->query($sql);
		}
	}
	mes('カテゴリ・ソート変換が完了しました', 'カテゴリ・ソート変換完了', 'kanri');
} elseif($Form["mode"] == "log_kt_change") {
	// (8)ログデータの交換・移動・削除(&log_kt_change)
	pass_check();
	$PR_kt_list = "";
	$sql = "SELECT path FROM {prefix}category ORDER BY path";
	$rowset = $db->rowset_assoc($sql);
	foreach($rowset as $tmp) {
		$PR_kt_list .= "<option value=\"{$tmp["path"]}\">" . full_category($tmp["path"]) . "\n";
	}
	require $cfg["temp_path"] . "admin/log_kt_change.html";
	exit;
} elseif($Form["mode"] == "log_kt_change_act") {
	// (8.1)ログデータの交換・移動・削除実行(&log_kt_change_act)
	pass_check();
	if($Form["check"] != "on") {
		mes("確認チェックがされていません。<br>戻ってチェックしてから実行してください。", "チェックエラー", "java");
	}
	// 記入漏れのチェック
	if($Form["log_mode"] == "change") {
		if(!$Form["change_kt1"] || !$Form["change_kt2"]) {
			mes("交換対象のカテゴリを指定してください", "カテゴリ選択ミス", "java");
		}
	} elseif($Form["log_mode"] == "move") {
		if(!$Form["bf_move_kt"] || !$Form["af_move_kt"]) {
			mes("移動対象のカテゴリを指定してください", "カテゴリ選択ミス", "java");
		}
	} elseif($Form["log_mode"] == "del") {
		if(!$Form["del_kt"]) {
			mes("削除対象のカテゴリを選択してください", "カテゴリ選択ミス", "java");
		}
	} else {
		mes("log_modeが選択されていません", "log_mode選択エラー", "java");
	}
	if($Form["log_mode"] == "change"){ // change
		// ログデータの交換
		$PR_mes = "ログデータの交換が完了しました<br>『" . full_category($Form["change_kt1"]) . "}』と『" . full_category($Form["change_kt2"]) . "』を交換しました";
		$change_kt1 = $Form["change_kt1"];
		$change_kt2 = $Form["change_kt2"];
		$kousin_kt = array("&{$change_kt1}&"=>"&{$change_kt2}&", "&{$change_kt2}&"=>"&{$change_kt1}&");
		$sql = "SELECT id,category FROM {prefix}log WHERE (category LIKE '%&{$change_kt1}&%') OR (category LIKE '%&{$change_kt2}&%')";
		$rowset = $db->rowset_assoc($sql);
		foreach($rowset as $line) {
			$line["category"] = strtr($line["category"], $kousin_kt);
			$sql ="UPDATE {prefix}log SET category='{$line["category"]}' WHERE id='{$line["id"]}'";
			$result = $db->query($sql);
		}
	} elseif($Form['log_mode'] == 'move') { // move
        // ログデータの移動
        $bf_move_kt = $Form['bf_move_kt'];
        $af_move_kt = $Form['af_move_kt'];
        // 移動元カテゴリと移動先カテゴリの両方に登録されているレコードの処理
        $sql = "SELECT "
               .     "id, category "
               . "FROM "
               .     "{prefix}log "
               . "WHERE "
               .     "category LIKE '%&" . $bf_move_kt . "&%' AND category LIKE '%&" . $af_move_kt . "&%'";
        $rowset = $db->rowset_assoc($sql);
        foreach ($rowset as $lineBoth) {
            $lineBoth['category'] = str_replace('&'.$bf_move_kt.'&', '&&', $lineBoth['category']);
            $sql = "UPDATE "
                   . "{prefix}log "
                   . "SET "
                   .     "category = '" . $lineBoth['category'] . "' "
                   . "WHERE "
                   .     "id = " . $lineBoth['id'];
            $result = $db->query($sql);
        }
        // 移動元カテゴリに登録されているレコードの処理
        $sql = "SELECT "
               .     "id, category "
               . "FROM "
               . "{prefix}log "
               . "WHERE "
               .     "category LIKE '%&" . $bf_move_kt . "&%'";
        $rowset = $db->rowset_assoc($sql);
        foreach ($rowset as $line) {
            $line['category'] = str_replace('&'.$bf_move_kt.'&', '&'.$af_move_kt.'&', $line['category']);
            $sql = "UPDATE "
                   . "{prefix}log "
                   . "SET "
                   .     "category = '" . $line['category'] . "' "
                   . "WHERE "
                   .     "id = " . $line['id'];
            $result = $db->query($sql);
        }
        // 完了メッセージ
        $categoryNameBefore = full_category($Form['bf_move_kt']);
        $categoryNameAfter  = full_category($Form['af_move_kt']);
        $PR_mes = "ログデータの移動が完了しました。<br />"
                . "『" . $categoryNameBefore . "』を『" . $categoryNameAfter . "』に移動しました。";
	} else { // del
		// ログデータの削除
		$PR_mes = "ログデータの削除が完了しました<br>『" . full_category($Form["del_kt"]) . "』を削除しました";
		$del_kt = $Form["del_kt"];
		$sql = "SELECT id,category FROM {prefix}log WHERE category LIKE '%&{$del_kt}&%'";
		$rowset = $db->rowset_assoc($sql);
		foreach($rowset as $line) {
			$line["category"] = str_replace("&{$del_kt}&", "&", $line["category"]);
			if(preg_match("/\d+/", $line["category"])) {
				$sql = "UPDATE {prefix}log SET category='{$line["category"]}' WHERE id='{$line["id"]}'";
				$result = $db->query($sql);
			} else {
				$sql="DELETE FROM {prefix}log WHERE id='{$line["id"]}'";
				$result = $db->query($sql);
				$sql="DELETE FROM {prefix}rank WHERE id='{$line["id"]}'";
				$result = $db->query($sql);
				$sql="DELETE FROM {prefix}rev WHERE id='{$line["id"]}'";
				$result = $db->query($sql);
				$sql="DELETE FROM ${prefix}rank_counter WHERE id='{$line["id"]}'";
				$result = $db->query($sql);
			}
		}
	}
	mes($PR_mes, "ログデータの交換・移動・削除完了", "kanri");
	exit;
} elseif($Form["mode"] == "log_repair") {
	// (9)ログ(登録データ)の修復(&log_repair)
	pass_check();
	require $cfg["temp_path"] . "admin/log_repair.html";
	exit;
} elseif($Form["mode"] == "log_repair_act") {
	// (9.1)ログ(登録データ)の修復実行(&log_repair_act)
	pass_check();
	if($Form["act"] == "dump") {
		if($Form["dump"] != "on") {
			mes("修復確認のため、確認チェックを入れてからもう一度実行してください", "確認チェックをしてください", "java");
		}
		$sql = "SELECT * FROM {prefix}log ORDER BY id";
		$result = $db->query($sql);
		$fp = fopen($Form["file"], "w");
		while($line = $db->fetch_num($result)) {
			fputs($fp, implode("\t", $line)."\n");
		}
		fclose($fp);

		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=".basename($Form['file']));
		
		$dump_data = file_get_contents($Form['file']);
		echo $dump_data;

//		mes("データのバックアップが完了しました", "バックアップ完了", "kanri");
	} elseif($Form["act"] == "restore") {
		if($Form["restore"] != "on") {
			mes("修復確認のため、確認チェックを入れてからもう一度実行してください", "確認チェックをしてください", "java");
		}
		//Remake Log Table
		$query = "DROP TABLE {prefix}log";
		$db->query($query);
		require_once $script['lib']['db']['create'];
		Create_Table::Log($db);
		
		$fp = fopen($Form["file"], "r");
		while($line =  fgets($fp)) {
			$line = rtrim(addslashes($line));
			$Slog = explode("\t", $line);
			$sql = "INSERT INTO {prefix}log VALUES ('{$Slog[0]}', '{$Slog[1]}', '{$Slog[2]}', '{$Slog[3]}', '{$Slog[4]}', '{$Slog[5]}', '{$Slog[6]}', '{$Slog[7]}', '{$Slog[8]}', '{$Slog[9]}', '{$Slog[10]}', '{$Slog[11]}', '{$Slog[12]}', '{$Slog[13]}', '{$Slog[14]}', '{$Slog[15]}')";
			$db->query($sql);
		}
		fclose($fp);
		mes("データの復元が完了しました", "復元完了", "kanri");
	}
	exit;
} elseif($Form["mode"] == "config") {
	// (11)環境設定 (&config)
	pass_check();
	require $cfg["temp_path"] . "admin/config.html";
	exit;
} elseif($Form["mode"] == "config_kt") {
	// (12)カテゴリ設定 (&config_kt)
	pass_check();
	$sql = "SELECT * FROM {prefix}category ORDER BY path";
	$rowset = $db->rowset_assoc($sql);
	foreach($rowset as $row) {
		$ref[$row["id"]] = $row["path"];
	}
	foreach($rowset as $num=>$row) {
		if($row["reffer"]) {
			$reflist = explode("&", $row["reffer"]);
			foreach($reflist as $key=>$refid) {
				if($refid) {
					$reflist[$key] = $ref[$refid];
				}
			}
			$rowset[$num]["reffer"] = implode("&", $reflist);
		}
	}
	require $cfg["temp_path"] . "admin/config_kt.html";
	exit;
} elseif($Form["mode"] == "rank_cfg") {
	// (13)人気ランキングの設定(&rank_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/rank_cfg.html";
	exit;
} elseif($Form["mode"] == "dl_check") {
	// (14)デッドリンクチェック画面(&dl_check)
	pass_check();
	$query = "SELECT {prefix}report.id, count(*) AS total,{prefix}log.title,{prefix}log.url,{prefix}log.banner,sum(no_url) AS no_url,sum(move) AS move,sum(no_banner) AS no_banner,sum(violation) AS violation,sum(other) as other FROM {prefix}report INNER JOIN {prefix}log ON {prefix}report.id = {prefix}log.id GROUP BY id";
	$report = $db->rowset_assoc($query);
	if(is_array($report)){
		foreach($report as $key=>$id) {
			$query = "SELECT * FROM {prefix}report WHERE id={$id["id"]}";
			$report_data = $db->rowset_assoc($query);
			if(!isset($report[$key]["data"])){$report[$key]["data"] = array();}		
			if(is_array($report_data)){$report[$key]["data"] = $report_data;}
		}
	}
	require $cfg["temp_path"] . "admin/dl_check.html";
	exit;
} elseif($Form["mode"] == "dl_check_dl") {
	// (14.1)デッドリンクチェック用ファイルをダウンロード(&dl_check_dl)
	pass_check();
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=Item.dat");
	$sql = "SELECT id,url FROM {prefix}log";
	$rowset = $db->rowset_assoc($sql) or $db->error("Query failed".__FILE__.__LINE__);
	foreach ($rowset as $Slog){
		echo $Slog["id"] . "\t" . $Slog["url"] . "\t\t\t0\t\t\t\t\t\t\t\t0\t\t\t\n";
	}
	exit;
} elseif($Form["mode"] == "dl_check_act") {
	// (14.2)デッドリンクチェック実行画面(&dl_check_act)
	$lines = array();
	pass_check();
	if(!is_file($Form["checkfile"])) {
		mes("指定されたファイルは存在しません", "エラー", "java");
	}
	$fp = fopen("./{$Form["checkfile"]}", "r");
	while($tmp = fgets($fp)) {
		$data = explode("\t", $tmp);
		if(strstr($data[13], "Not Found") || strstr($data[13], "Forbidden")) {
			$url[$data[0]] = $data[1];
			array_push($lines, $tmp);
		}
	}
	fclose($fp);
	$fp = fopen("./{$Form["checkfile"]}", "w");
	foreach($lines as $tmp) {
		fputs($fp, $tmp);
	}
	fclose($fp);
	require $cfg["temp_path"] . "admin/dl_check_act.html";
	exit;
} elseif($Form["mode"] == "mylink_cfg") {
	// (15)マイリンクの設定(&mylink_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/mylink_cfg.html";
	exit;
} elseif($Form["mode"] == "ver_info") {
	// (18)バージョン情報(&ver_info)
	pass_check();
	require $cfg["temp_path"] . "admin/ver_info.html";
	exit;
} elseif($Form["mode"] == "cfg_admin_pass") {
	// 管理者パスワード変更(cfg_admin_pass)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_admin_pass.html";
	exit;
} elseif($Form["mode"] == "look_mes") {
	// (20)登録者のメッセージを見る(&look_mes)
	// look_mes.cgiのログフォーマット
	// [0]=>ID[0]
	// [1]=>登録日[4]
	// [2]=>新設希望カテゴリ
	// [3]=>管理人へのコメント
	// [4]=>メールアドレス[9]
	// [5]=>お名前[8]
	// [6]=>URL[2]
	// [7]=>タイトル[1]
	pass_check();
	require $cfg["temp_path"] . "admin/look_mes.html";
	exit;
} elseif($Form["mode"] == "new_pass") {
	// パスワードを更新(new_pass)
	// パスワードチェック
	if($cfg["pass"] != "setup") {
		pass_check();
	}
	if(!preg_match("/^\w{3,8}$/", $Form["new_pass"])) {
		$Form["pass"] = "";
		mes("パスワードは英数、_(アンダースコア)で3-8文字です", "パスワード入力エラー", "kanri");
		exit;
	}
	if($Form["new_pass"] != $Form["re_pass"]) {
		$Form["pass"] = "";
		mes("確認用再入力が一致しませんでした", "パスワード入力エラー", "kanri");
		exit;
	}
	// パスワードを暗号化する
	$Form["pass"] = $Form["new_pass"];
	$Form["new_pass"] = crypt($Form["new_pass"]);
	$sql = "UPDATE {prefix}cfg SET value='{$Form["new_pass"]}' WHERE name='pass'";
	$result = $db->query($sql);
	mes("パスワードの設定、変更が完了しました", "パスワード設定完了", "kanri");
	exit;
} elseif($Form["mode"] == "cfg_make") {
	// (cfg1)環境設定($cfg)を更新(cfg_make)
	pass_check();
	$new_cfg_list = array(
		'sp_search_name'=>$cfg['search_name'],
		'sp_path_url'=>"./",
		'sp_home'=>$cfg['sp_path_url'],
		'sp_temp_path'=>$path_set['template']['root'],
		'sp_sub_path'=>"../" . $cfg['sub_path'],
		'sp_img_path'=>"../" . $cfg['img_path_url']
	);

	foreach ($new_cfg_list as $new_cfg_key=>$new_cfg_value){
		if(!isset($cfg[$new_cfg_key])){
			$sql = "INSERT INTO {prefix}cfg VALUES('{$new_cfg_key}','{$new_cfg_value}')";
			$result = $db->query($sql);
			$cfg[$new_cfg_key] = $new_cfg_value;
		}
	}

	foreach($cfg as $key=>$val) {
		if(isset($Form[$key]) && $Form[$key] != $cfg[$key] && $key != "pass") {
			$Form[$key] = $db->escape_string($Form[$key]);
			$sql = "UPDATE {prefix}cfg SET value='{$Form[$key]}' WHERE name='{$key}'";
			$result = $db->query($sql);
		}
	}
	mes("環境設定の変更設定が完了しました", "環境設定設定完了", "kanri");
	exit;
} elseif($Form["mode"] == "cfg_make_PR_menu") {
	// (cfg2)環境設定(&search_form/&menu_bar)を更新(&cfg_make_PR_menu)
	$file_data=array();
	pass_check();
	$fl = 0;
	$p_fl = 1;
	$Form["search_form"] = str_replace("&lt;", "<", $Form["search_form"]);
	$Form["search_form"] = str_replace("&gt;", ">", $Form["search_form"]);
	$Form["search_form"] = str_replace("’", "'", $Form["search_form"]);
	$Form["search_form"] = $db->escape_string($Form["search_form"]);
	$Form["menu_bar"] = str_replace("&lt;", "<", $Form["menu_bar"]);
	$Form["menu_bar"] = str_replace("&gt;", ">", $Form["menu_bar"]);
	$Form["menu_bar"] = str_replace("’", "'", $Form["menu_bar"]);
	$Form["menu_bar"] = $db->escape_string($Form["menu_bar"]);
	$Form["head_sp"] = str_replace("&lt;", "<", $Form["head_sp"]);
	$Form["head_sp"] = str_replace("&gt;", ">", $Form["head_sp"]);
	$Form["head_sp"] = str_replace("’", "'", $Form["head_sp"]);
	$Form["head_sp"] = $db->escape_string($Form["head_sp"]);
	$Form["foot_sp"] = str_replace("&lt;", "<", $Form["foot_sp"]);
	$Form["foot_sp"] = str_replace("&gt;", ">", $Form["foot_sp"]);
	$Form["foot_sp"] = str_replace("’", "'", $Form["foot_sp"]);
	$Form["foot_sp"] = $db->escape_string($Form["foot_sp"]);
	$sql = "UPDATE {prefix}text SET value='{$Form["search_form"]}' WHERE name='search_form'";
	$result = $db->query($sql);
	$sql = "UPDATE {prefix}text SET value='{$Form["menu_bar"]}' WHERE name='menu_bar'";
	$result = $db->query($sql);
	$sql = "UPDATE {prefix}text SET value='{$Form["head_sp"]}' WHERE name='head_sp'";
	$result = $db->query($sql);
	$sql = "UPDATE {prefix}text SET value='{$Form["foot_sp"]}' WHERE name='foot_sp'";
	$result = $db->query($sql);
	mes("メニューバー/外部検索エンジン/ヘッダ・フッタスペースの設定が完了しました", "更新完了", "kanri");
	exit;
} elseif($Form["mode"] == "cfg_make_reg") {
	// (cfg3)環境設定(登録処理関係)を更新 (&cfg_make_reg)
	pass_check();
	if(!isset($cfg_reg['kt_select_mode'])){
		$sql = "INSERT INTO {prefix}cfg_reg VALUES('kt_select_mode','multiple')";
		$result = $db->query($sql);
		$cfg_reg['kt_select_mode'] = "multiple";
	}
	foreach($cfg_reg as $key=>$val) {
		if(isset($Form[$key]) and $Form[$key] != $cfg_reg[$key]) {
			$Form[$key] = $db->escape_string($Form[$key]);
			$sql = "UPDATE {prefix}cfg_reg SET value='{$Form[$key]}' WHERE name='{$key}'";
			$result = $db->query($sql);
		}
	}
	mes("環境設定(登録処理関係)の変更が完了しました", "環境設定(登録処理関係)設定完了", "kanri");
	exit;
} elseif($Form["mode"] == "cfg_make_kt") {
	// (cfg5)カテゴリ設定を更新 (&cfg_make_kt)
	pass_check();
	if($Form["mente_mode"] == "mente") {
        // 削除指定されたカテゴリにログが残っていないか確認
        foreach($Form['kt'] as $key => $val) {
            if (isset($Form['d'][$key])) {
                $sql = "SELECT "
                       .     "COUNT(*) "
                       . "FROM "
                       . "{prefix}log "
                       . "WHERE "
                       .     "category LIKE '%&" . $key . "%&%'";
                $res = $db->query($sql);
                $tmp = mysql_fetch_row($res);
                $leftRecordNum = (int)$tmp[0];
                if ($leftRecordNum > 0) {
                    $categoryName = full_category($key);
                    $msg = "カテゴリ「<b>" . $categoryName . "</b>」には <b>" . $leftRecordNum . "</b> 件のレコードが残っています。"
                              . "カテゴリの削除を行う前に、残っているレコードを別カテゴリへ移動もしくは削除してください。";
                    mes($msg, "カテゴリ削除エラー", "kanri");
                    exit;
                }
            }
        }
        // カテゴリ設定を更新
        foreach ($Form['kt'] as $key => $val) {
            // 削除でなければ
			if (!isset($Form['d'][$key])) {
				if($Form["ref"][$key]) {
					$reflist = explode("&", $Form["ref"][$key]);
					foreach($reflist as $num=>$refpath) {
						if($refpath) {
							$sql = "SELECT id FROM {prefix}category WHERE path='{$refpath}' LIMIT 1";
							$row = $db->single_assoc($sql);
							$reflist[$num] = $row["id"];
						}
					}
					$Form["ref"][$key] = implode("&", $reflist);
				}
				$t = $Form["t"][$key];
				$o = $Form["o"][$key];
				$no = $Form["no"][$key];
				$ref = $Form["ref"][$key];
				$furi = $Form["furi"][$key];
				$cmt = $Form["cmt"][$key];
				$sql = "SELECT "
                       .     "id "
                       . "FROM "
                       .     "{prefix}category "
                       . "WHERE "
                       .     "title = '" . $val . "' "
                       .     "AND path = '" . $key . "' "
                       .     "AND top_list = '" . $t . "' "
                       .     "AND etc_list = '" . $o . "' "
                       .     "AND regist = '" . $no . "' "
                       .     "AND reffer = '" . $ref . "' "
                       .     "AND sort_name = '" . $furi . "' "
                       .     "AND comment = '" . $cmt . "' "
                       . "LIMIT "
                       .     "1";
				$row = $db->single_assoc($sql);
				if($row["id"]) {
					continue;
				} else {
					$val = $db->escape_string($val);
					$furi = $db->escape_string($furi);
					$cmt = $db->escape_string($cmt);
					$sql = "UPDATE {prefix}category SET title='{$val}', top_list='{$t}', etc_list='{$o}', regist='{$no}', reffer='{$ref}', sort_name='{$furi}', comment='{$cmt}' WHERE path='{$key}'";
					$db->query($sql);
				}
			} else {
				$sql = "DELETE FROM {prefix}category WHERE path='{$key}'";
				$db->query($sql);
			}
		}
	}
	if($Form["mente_mode"] == "new" && $Form["kt_new"]) {
		// 新規追加分を定義
		$Form["kt_new"] = str_replace(array("&gt;", "&lt;"), array(">", "<"), $Form["kt_new"]);
		$Form["kt_new"] = trim($Form["kt_new"]);
		$new = explode("\n", $Form["kt_new"]);
		foreach($new as $tmp) {
			$tmp = trim($tmp);
			$kt = explode("<>", $tmp);
			$check = explode("/", $kt[0]);
			array_pop($check);
			foreach($check as $val) {
				if(!preg_match("/^\d+$/", $val)) {
					mes("カテゴリの設定に半角数字以外があります($val)", "カテゴリ設定エラー", "kanri");
					exit;
				}
			}
			if (substr($kt[0], -1) != "/") {
				mes("カテゴリはスラッシュ「/」で終わってください({$kt[0]}<b><font color=\"#FF0000\">/</font></b>)", "カテゴリ設定エラー", "kanri");
				exit;
			}
			$dir = dirname($kt[0]);
			$up_id = 0;
			if($dir != ".") {
				$sql = "SELECT id FROM {prefix}category WHERE path='{$dir}/'";
				$row = $db->single_assoc($sql);
				if($row["id"]) {
					$up_id = $row[id];
				} else {
					mes("カテゴリの上位階層がありません({$dir}/)", "カテゴリ設定エラー", "kanri");
					exit;
				}
			}
			$sql = "DELETE FROM {prefix}category WHERE path='{$kt[0]}'";
			$db->query($sql);
			$sql = "INSERT INTO {prefix}category VALUES(NULL, '{$kt[1]}', '{$up_id}', '{$kt[0]}', '', '', '', '', '', '')";
			$db->query($sql);
		}
	}
	mes("カテゴリの設定が完了しました", "カテゴリ設定完了", "kanri");
	exit;
} elseif($Form["mode"] == "cfg_reg") {
	// 環境設定(登録処理関係)
	require $cfg["temp_path"] . "admin/cfg_reg.html";
	exit;
} elseif($Form["mode"] == "cfg_html") {
	// HTMLの設定
	pass_check();
	$PR_search_form = rtrim($text["search_form"]);
	$PR_search_form = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_search_form);
	$PR_menu_bar = rtrim($PR_menu_bar);
	$PR_menu_bar = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_menu_bar);
	$PR_head_sp = rtrim($text["head_sp"]);
	$PR_head_sp = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_head_sp);
	$PR_foot_sp = rtrim($text["foot_sp"]);
	$PR_foot_sp = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_foot_sp);
	require $cfg["temp_path"] . "admin/cfg_html.html";
	exit;
} elseif($Form["mode"] == "cfg_marks") {
	require $cfg["temp_path"] . "admin/cfg_marks.html";
	exit;
}elseif($Form['mode'] == "db_ipv6"){
	require_once $script['lib']['db']['expand']['ipv4_v6'];
	$db_ipv_conv = new DB_Expand_IPv4Tov6();
	$db_ipv_conv->Change_Ipv6_Column();
	
	exit;
}	

} else {
	// (1)ログイン画面(&login)
	require $cfg["temp_path"] . "admin/login.html";
	exit;
}

?>