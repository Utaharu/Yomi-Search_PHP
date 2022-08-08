<?php
// エラーレポート設定
require 'config4debug.php';
if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}

// 言語設定
mb_internal_encoding("UTF-8");
mb_language("ja");

// インクルード
require("class/db.php");
require("functions.php");

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

// [SQL-SET-NAMES]設定
$db->sql_setnames();

//local変数に
$db_pre = $db->db_pre;

// cfgテーブルから設定情報を配列($cfg)へ読込
$cfg = array();

// cfg_regテーブルから設定情報を配列($cfg_reg)へ読込
$cfg_reg = array();

// textテーブルから設定情報を配列($text)へ読込
$text = array();

// cfgテーブルから設定情報を配列($cfg)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'cfg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg[$tmp[0]] = $tmp[1];
}

// textテーブルから設定情報を配列($text)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'text';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$text[$tmp[0]] = $tmp[1];
}

// cfg_regテーブルから設定情報を配列($cfg_reg)へ読込
$query = 'SELECT name, value FROM '.$db_pre.'cfg_reg';
$rowset = $db->rowset_num($query);
foreach($rowset as $tmp) {
	$cfg_reg[$tmp[0]] = $tmp[1];
}


// 登録申請中(仮登録状態)のログ件数を取得して
// 管理室用のリストボックスを生成
$count_temp = $db->log_count($db_pre.'log_temp');
require 'admin_listbox.php';

// (1)ログイン画面(&login)
// header("Content-type: text/html; charset=UTF-8");
if(isset($_POST["mode"])) {
if($_POST["mode"] == "kanri") {
	// (2)管理人室(&kanri)
	// パスワードチェック
	if($cfg["pass"] != "setup") {
		pass_check();
	}
	// クッキーの設定
	$CK_data = get_cookie();
	if(isset($_POST["set"])) {
		if($_POST["set"] == "設定") {
			$CK_data[4] = 1;
		} else {
			$CK_data[4] = 0;
		}
		set_cookie($CK_data);
	}
	if($CK_data[4]) {
		$PRset="設定";
	} else {
		$PRset="解除";
	}
	require $cfg["temp_path"] . "admin/admin.html";
	exit;
} elseif($_POST["mode"] == "cfg_kanri") {
	// (2.1)認証設定画面表示(cfg_kanri)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_kanri.html";
	exit;
} elseif($_POST["mode"] == "temp_to_regist") {
	// (5)登録待ち表示画面(&temp_to_regist)
	pass_check();
	$count_temp = $db->log_count("{$db->db_pre}log_temp");
	$Ekreg = 10; // 表示数
	$size_url_w = 50; // URL/バナーURL/キーワードの横サイズ
	$size_syoukai_w = 40; // 紹介文/管理人コメント/メッセージの横サイズ
	if(!isset($_POST["pre_no"])) {
		$_POST["pre_no"] = 1;
	}
	if(isset($_POST["page"])) {
		if($_POST["page"] == "次のページ") {
			$_POST["pre_no"] = $_POST["pre_no"] + $Ekreg;
		} elseif($_POST["page"] == "前のページ") {
			$_POST["pre_no"] = $_POST["pre_no"] - $Ekreg;
		}
	}
	$query = "SELECT path FROM {$db->db_pre}category ORDER BY path";
	$rowset = $db->rowset_num($query);
	$PRgane_list = "";
	foreach($rowset as $path) {
		$PRgane_list .="<option value=\"{$path[0]}\">" . full_category($path[0]) . "</option>\n";
	}
	$query = "SELECT * FROM {$db->db_pre}log_temp";
	$rowset = $db->rowset_num($query);
	require $cfg["temp_path"] . "admin/temp_to_regist.html";
	exit;
} elseif($_POST["mode"] == "temp_to_regist_act") {
	// (5.1)登録待ちの処理決定実行(&temp_to_regist_act)
	pass_check();
	$Slog = array(); // 登録データの一時保存用
	// メール送信用ライブラリを読み込み
	if($cfg['mail_new']) {
		require $cfg['sub_path'] . "mail_ys.php";
	}
	$query = "SELECT id, passwd, ip FROM {$db->db_pre}log_temp";
	$rowset = $db->rowset_assoc($query);
	foreach ($rowset as $Tlog) {
		if ($_POST['R'][$Tlog['id']] == "reg") { // 登録
			// (t1)フォーム入力データを書き込みデータに反映(仮登録→正規登録用)
			//    (&form_to_temp)
			// タイトル(1)
			$Slog[1] = $_POST['Ftitle'][$Tlog['id']];
			// URL(2)
			$Slog[2] = $_POST['Furl'][$Tlog['id']];
			// マークデータ(3)
			$Slog[3] = "";
			for ($i = 1; $i <= 10; $i++) { // ←マーク数を増やす場合には修正
				if(!isset($_POST["Fmark{$i}"][$Tlog["id"]])) {
					$_POST["Fmark{$i}"][$Tlog["id"]] = 0;
				}
				$Slog[3] .= $_POST["Fmark{$i}"][$Tlog["id"]] . "_";
			}
			$Slog[3] = substr($Slog[3], 0, -1);
			// 更新日(4)
			$Slog[4] = get_time(0, 1);
			// パスワード(5)
			// (未変更)
			$Slog[5] = $Tlog["passwd"];
			// 紹介文(6)
			$Slog[6] = $_POST["Fsyoukai"][$Tlog["id"]];
			$Slog[6] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Slog[6]);
			// 管理人コメント(7)
			$Slog[7] = $_POST["Fkanricom"][$Tlog["id"]];
			$Slog[7] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Slog[7]);
			// お名前(8)
			$Slog[8] = $_POST["Fname"][$Tlog["id"]];
			// E-Mail(9)
			$Slog[9] = $_POST["Femail"][$Tlog["id"]];
			// カテゴリ(10)
			$Slog[10]="&";
			for($i=1; $i <= $cfg_reg["kt_max"]; $i++) {
				$Slog[10] .= $_POST["Fkt{$i}"][$Tlog["id"]] . "&";
			}
			// time形式(11)
			$Slog[11] = time();
			// バナーURL(12)
			$Slog[12] = $_POST["Fbana_url"][$Tlog["id"]];
			// 更新フラグ(13)
			$Slog[13] = 0;
			// IP(14)
			$Slog[14] = $Tlog["ip"];
			// キーワード(15)
			$Slog[15] = $_POST["Fkey"][$Tlog["id"]];
			foreach($Slog as $key=>$val) {
				$Slog[$key] = $db->escape_string($val);
			}
			$query = "INSERT INTO {$db->db_pre}log VALUES(NULL,'{$Slog[1]}','{$Slog[2]}','{$Slog[3]}','{$Slog[4]}','{$Slog[5]}','{$Slog[6]}','{$Slog[7]}','{$Slog[8]}','{$Slog[9]}','{$Slog[10]}','{$Slog[11]}','{$Slog[12]}','{$Slog[13]}','{$Slog[14]}','{$Slog[15]}')";
			$db->query($query);
			$Slog[0] = $db->last_id();
			$query = "INSERT INTO {$db->db_pre}rank_counter VALUES ('{$Slog[0]}', '0', '0')";
			$db->query($query);
			if($cfg["mail_new"]) {
				// 仮登録→新規登録時のメールを送信
				$Slog[6] = str_replace("<br>", "\n", $Slog[6]);
				$Slog[7] = str_replace("<br>", "\n", $Slog[7]);
				if(!isset($_POST["Fadd_kt"][$Tlog["id"]])) {
					$_POST["Fadd_kt"][$Tlog["id"]] = "";
				}
				if(!isset($_POST["Fmark2"][$Tlog["id"]])) {
					$_POST["Fmark2"][$Tlog["id"]] = 0;
				}
				if($cfg['mail_to_admin']) { // 管理人へメール送信
					sendmail($cfg['admin_email'], $Slog[9], "{$cfg['search_name']} 新規登録完了通知", "new", "admin", $Slog, $_POST['Fmark2'][$Tlog['id']], $_POST['Fadd_kt'][$Tlog['id']], $_POST['Fto_admin'][$Tlog['id']], $_POST['Fto_reg'][$Tlog['id']], "");
				}
				if($cfg['mail_to_register']) { // 登録者へメール送信
					sendmail($Slog[9], $cfg['admin_email'], "{$cfg['search_name']} 新規登録完了通知", "new", "", $Slog, $_POST['Fmark2'][$Tlog['id']], $_POST['Fadd_kt'][$Tlog['id']], $_POST['Fto_admin'][$Tlog['id']], $_POST['Fto_reg'][$Tlog['id']], "");
				}
			}
		}
		if($_POST["R"][$Tlog["id"]] == "reg" || $_POST["R"][$Tlog["id"]] == "del") {
			$query = "DELETE FROM {$db->db_pre}log_temp WHERE id='{$Tlog["id"]}'";
			$result = $db->query($query);
		}
	}
	mes("仮登録データの処理が完了しました", "仮登録データ処理完了", "kanri");
	exit;
} elseif($_POST["mode"] == "key_cfg") {
	// (6)キーワードランキングの設定(key_cfg)
	pass_check();
	// ラジオボックスの設定
	// キーワードランキング(実施しない=0/実施する=1)
	$query = "SELECT k.word wd,count(*) pt,r.word rwd,r.open_key opn,r.bad_key bad,r.view_word view FROM {$db->db_pre}key AS k LEFT JOIN {$db->db_pre}key_rank AS r ON k.word=r.word GROUP BY k.word ORDER BY pt DESC";
	$rowset = $db->rowset_assoc($query);
	foreach($rowset as $row) {
		if(!$row["rwd"]) {
			$row['wd'] = mysql_real_escape_string($row['wd']);
			$query = "INSERT INTO {$db->db_pre}key_rank VALUES('{$row['wd']}','','','')";
			$db->query($query);
		}
	}
	require $cfg["temp_path"] . "admin/key_cfg.html";
	exit;
} elseif($_POST["mode"] == "key_cfg_act") {
	// (6.1)キーワードランキングの設定実行(&key_cfg_act)
	pass_check();
	if($_POST["change"]) {
		foreach($_POST["change"] as $key=>$val) {
			if($val or $_POST["view"][$key]) {
				$view = $db->escape_string($_POST["view"][$key]);
				$key = $db->escape_string($key);
				$opn = ($val == "open") ? "1" : NULL;
				$bad = ($val == "bad") ? "1" : NULL;
				$query = "UPDATE {$db->db_pre}key_rank SET open_key='{$opn}',bad_key='{$bad}',view_word='{$view}' WHERE word='{$key}'";
				$db->query($query);
				if($val == "del") {
					$query = "DELETE FROM {$db->db_pre}key WHERE word='{$key}'";
					$db->query($query);
					$query = "DELETE FROM {$db->db_pre}key_rank WHERE word='{$key}'";
					$db->query($query);
				}
			}
		}
	}
	mes("キーワード表示設定の変更が完了しました", "キーワード表示設定の変更完了", "kanri");
	exit;
} elseif($_POST["mode"] == "key_del_act") {
	pass_check();
	$query = "SELECT k.word wd,count(*) pt FROM {$db->db_pre}key AS k LEFT JOIN {$db->db_pre}key_rank AS r ON k.word=r.word GROUP BY k.word HAVING pt<={$_POST["del_max"]}";
	$rowset = $db->rowset_assoc($query);
	foreach($rowset as $row) {
        $row['wd'] = mysql_real_escape_string($row['wd']);
        $query = "DELETE FROM {$db->db_pre}key WHERE word='{$row["wd"]}'";
		$db->query($query);
		$query = "DELETE FROM {$db->db_pre}key_rank WHERE word='{$row["wd"]}'";
		$db->query($query);
	}
	mes("キーワード削除が完了しました", "キーワード削除完了", "kanri");
	exit;
} elseif($_POST["mode"] == "key_cfg_del_word_act") {
	// (6.2)キーワードランキングの集計対象外のキーワードを一括登録実行(&key_cfg_del_word_act)
	pass_check();
	require $cfg["log_path"] . "keyrank_ys.php";
	$fp = fopen("{$cfg["log_path"]}keyrank_ys.php", "w");
	fputs($fp, "<?php\n\{$keyrank}=array(\n");
	while(list($key, $value) = each($keyrank)) {
		fputs($fp, "'{$key}'=>'{$value}',\n");
	}
	fputs($fp, ");\n\{$bad_key}=array(\n");
	while(list($key, $value) = each($bad_key)){
		fputs($fp, "'{$key}'=>'{$value}',\n");
	}
	$del_key_list = explode(",", $_POST["del_key_list"]);
	foreach($del_key_list as $tmp) {
		$tmp = str_replace("\n", "", $tmp);
		fputs($fp, "'{$tmp}'=>'1',\n");
	}
	fputs($fp, ");\n\{$open_key}=array(\n");
	while(list($key, $value) = each($open_key)) {
		fputs($fp, "'{$key}'=>'{$value}',\n");
	}
	fputs($fp, ");\n?>");
	mes("集計対象外のキーワードの一括登録が完了しました", "登録完了", "kanri");
	exit;
} elseif($_POST["mode"] == "log_conv") {
	// (7)各種ログ変換(&log_conv)
	pass_check();
	require $cfg["temp_path"] . "admin/log_conv.html";
	exit;
} elseif($_POST["mode"] == "log_conv_act") {
	// (7.1)各種ログ変換実行(&log_conv_act)
	pass_check();
	if($_POST["check"] != "on") {
		mes("確認チェックがされていません。<br>戻ってチェックしてから実行してください。", "チェックエラー", "java");
	}
	if(!is_file($_POST["bf_file"])) {
		mes("エラー：{$bf_file} が見つかりません", "ファイルが見つかりません", "java");
	} else {
		if($_POST["log_mode"] == "v4todb") {
			$fp = fopen($_POST["bf_file"], "r");
			while($line = fgets($fp)) {
				$line = mb_convert_encoding($line, "UTF-8", "SJIS");
				$line = $db->escape_string($line);
				$Slog = explode("<>", $line);
				$rank = explode("_", $Slog[13]);
				$query = "INSERT INTO {$db->db_pre}rank_counter VALUES ('{$Slog[0]}', '{$rank[1]}', '{$rank[3]}')";
				$db->query($query);
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
				$query = "INSERT INTO {$db->db_pre}log"
				           . " VALUES ('{$Slog[0]}', '{$Slog[1]}', '{$Slog[2]}', '{$Slog[3]}', '{$Slog[4]}', '{$Slog[5]}', '{$Slog[6]}', '{$Slog[7]}', '{$Slog[8]}', '{$Slog[9]}', '{$Slog[10]}', '{$Slog[11]}', '{$Slog[12]}', '{$Slog[13]}', '{$Slog[14]}', '{$Slog[15]}')";
				$db->query($query);
			}
			fclose($fp);
			$PR_msg = "Ver4形式→データベースへの変換が完了しました";
		}
	}
	mes("$PR_msg", "変換終了", "kanri");
	exit;
} elseif($_POST['mode'] == 'log_conv_kt_sort') {
	// (7.3)カテゴリ・ソート変換実行(&log_conv_kt_sort)
	function sort_category($id, $path) {
		global $cfg, $db;
		$change_path = array();
		$query = "SELECT id, path, sort_name"
		           . " FROM {$db->db_pre}category"
			       . " WHERE up_id='{$id}'"
			       . " ORDER BY sort_name, path";
		$rowset = $db->rowset_assoc($query) or $db->error("Query failed {$query}".__FILE__.__LINE__);
		$i = 1;
		foreach ($rowset as $row) {
			if ($row['id']) {
				$len = strlen(basename($row['path']));
				$new_path = $path . sprintf("%0{$len}d" , $i) . '/';
				$i++;
				if ($row['path'] != $new_path) {
					$query = "UPDATE {$db->db_pre}category"
					           . " SET path = '{$new_path}'"
							   . " WHERE id = '{$row['id']}'";
					$db->query($query) or $db->error("Query failed {$query}".__FILE__.__LINE__);
					$change_path[$new_path] = $row['path'];
				}
				$change_path = array_merge($change_path, sort_category($row['id'], $new_path));
			}
		}
		return $change_path;
	}
	pass_check();
	$del_list = array();
	if($_POST['check'] != 'on') {
		mes('確認チェックにチェックしてから変換ボタンを押してください', 'チェックミス', 'java');
	}

	if($_POST['all'] == 'on') {
		// $_POST['all']の処理
		$change_path = sort_category(0, '');
	} elseif ($_POST['kt_str']) {
		// $_POST[kt_str]を解析
		if(preg_match("/[^\w\-\,]/", $_POST['kt_str'])) {
			mes('カテゴリ指定文に全角文字が含まれています', 'エラー', 'java');
		}
		$kt_str = explode(',', $_POST['kt_str']);
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
			$query = "SELECT id"
			           . " FROM {$db->db_pre}category"
					   . " WHERE path='{$path}'"
					   . " LIMIT 1";
			$id = $db->single_num($query);
			$change_path = array_merge($change_path, sort_category($id[0], $path));
		}
	}
	// 本体ログを更新
	if (!empty($change_path)) {
		$change_path_where = array();
		foreach ($change_path as $val) {
			$change_path_where[] = "(category LIKE '%&{$val}&%')";
		}
		$query = "SELECT id, category"
		           . " FROM {$db->db_pre}log"
				   . " WHERE " . implode(' or ' , $change_path_where);
		$rowset = $db->rowset_assoc($query) or $db->error("Query failed {$query}".__FILE__.__LINE__);
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
			$query = "UPDATE {$db->db_pre}log"
			           . " SET category='{$row['category']}'"
					   . " WHERE id='{$row['id']}'";
			$db->query($query);
		}
	}
	mes('カテゴリ・ソート変換が完了しました', 'カテゴリ・ソート変換完了', 'kanri');
} elseif($_POST["mode"] == "log_kt_change") {
	// (8)ログデータの交換・移動・削除(&log_kt_change)
	pass_check();
	$PR_kt_list = "";
	$query = "SELECT path FROM {$db->db_pre}category ORDER BY path";
	$rowset = $db->rowset_assoc($query);
	foreach($rowset as $tmp) {
		$PR_kt_list .= "<option value=\"{$tmp["path"]}\">" . full_category($tmp["path"]) . "\n";
	}
	require $cfg["temp_path"] . "admin/log_kt_change.html";
	exit;
} elseif($_POST["mode"] == "log_kt_change_act") {
	// (8.1)ログデータの交換・移動・削除実行(&log_kt_change_act)
	pass_check();
	if($_POST["check"] != "on") {
		mes("確認チェックがされていません。<br>戻ってチェックしてから実行してください。", "チェックエラー", "java");
	}
	// 記入漏れのチェック
	if($_POST["log_mode"] == "change") {
		if(!$_POST["change_kt1"] || !$_POST["change_kt2"]) {
			mes("交換対象のカテゴリを指定してください", "カテゴリ選択ミス", "java");
		}
	} elseif($_POST["log_mode"] == "move") {
		if(!$_POST["bf_move_kt"] || !$_POST["af_move_kt"]) {
			mes("移動対象のカテゴリを指定してください", "カテゴリ選択ミス", "java");
		}
	} elseif($_POST["log_mode"] == "del") {
		if(!$_POST["del_kt"]) {
			mes("削除対象のカテゴリを選択してください", "カテゴリ選択ミス", "java");
		}
	} else {
		mes("log_modeが選択されていません", "log_mode選択エラー", "java");
	}
	if($_POST["log_mode"] == "change"){ // change
		// ログデータの交換
		$PR_mes = "ログデータの交換が完了しました<br>『" . full_category($_POST["change_kt1"]) . "}』と『" . full_category($_POST["change_kt2"]) . "』を交換しました";
		$change_kt1 = $_POST["change_kt1"];
		$change_kt2 = $_POST["change_kt2"];
		$kousin_kt = array("&{$change_kt1}&"=>"&{$change_kt2}&", "&{$change_kt2}&"=>"&{$change_kt1}&");
		$query = "SELECT id,category FROM {$db->db_pre}log WHERE (category LIKE '%&{$change_kt1}&%') OR (category LIKE '%&{$change_kt2}&%')";
		$rowset = $db->rowset_assoc($query);
		foreach($rowset as $line) {
			$line["category"] = strtr($line["category"], $kousin_kt);
			$query ="UPDATE {$db->db_pre}log SET category='{$line["category"]}' WHERE id='{$line["id"]}'";
			$result = $db->query($query);
		}
	} elseif($_POST['log_mode'] == 'move') { // move
        // ログデータの移動
        $bf_move_kt = $_POST['bf_move_kt'];
        $af_move_kt = $_POST['af_move_kt'];
        // 移動元カテゴリと移動先カテゴリの両方に登録されているレコードの処理
        $query = "SELECT "
               .     "id, category "
               . "FROM "
               .     $db->db_pre . "log "
               . "WHERE "
               .     "category LIKE '%&" . $bf_move_kt . "&%' AND category LIKE '%&" . $af_move_kt . "&%'";
        $rowset = $db->rowset_assoc($query);
        foreach ($rowset as $lineBoth) {
            $lineBoth['category'] = str_replace('&'.$bf_move_kt.'&', '&&', $lineBoth['category']);
            $query = "UPDATE "
                   .     $db->db_pre . "log "
                   . "SET "
                   .     "category = '" . $lineBoth['category'] . "' "
                   . "WHERE "
                   .     "id = " . $lineBoth['id'];
            $result = $db->query($query);
        }
        // 移動元カテゴリに登録されているレコードの処理
        $query = "SELECT "
               .     "id, category "
               . "FROM "
               .     $db->db_pre . "log "
               . "WHERE "
               .     "category LIKE '%&" . $bf_move_kt . "&%'";
        $rowset = $db->rowset_assoc($query);
        foreach ($rowset as $line) {
            $line['category'] = str_replace('&'.$bf_move_kt.'&', '&'.$af_move_kt.'&', $line['category']);
            $query = "UPDATE "
                   .     $db->db_pre . "log "
                   . "SET "
                   .     "category = '" . $line['category'] . "' "
                   . "WHERE "
                   .     "id = " . $line['id'];
            $result = $db->query($query);
        }
        // 完了メッセージ
        $categoryNameBefore = full_category($_POST['bf_move_kt']);
        $categoryNameAfter  = full_category($_POST['af_move_kt']);
        $PR_mes = "ログデータの移動が完了しました。<br />"
                . "『" . $categoryNameBefore . "』を『" . $categoryNameAfter . "』に移動しました。";
	} else { // del
		// ログデータの削除
		$PR_mes = "ログデータの削除が完了しました<br>『" . full_category($_POST["del_kt"]) . "』を削除しました";
		$del_kt = $_POST["del_kt"];
		$query = "SELECT id,category FROM {$db->db_pre}log WHERE category LIKE '%&{$del_kt}&%'";
		$rowset = $db->rowset_assoc($query);
		foreach($rowset as $line) {
			$line["category"] = str_replace("&{$del_kt}&", "&", $line["category"]);
			if(preg_match("/\d+/", $line["category"])) {
				$query = "UPDATE {$db->db_pre}log SET category='{$line["category"]}' WHERE id='{$line["id"]}'";
				$result = $db->query($query);
			} else {
				$query="DELETE FROM {$db->db_pre}log WHERE id='{$line["id"]}'";
				$result = $db->query($query);
				$query="DELETE FROM {$db->db_pre}rank WHERE id='{$line["id"]}'";
				$result = $db->query($query);
				$query="DELETE FROM {$db->db_pre}rev WHERE id='{$line["id"]}'";
				$result = $db->query($query);
				$query="DELETE FROM ${$db->db_pre}rank_counter WHERE id='{$line["id"]}'";
				$result = $db->query($query);
			}
		}
	}
	mes($PR_mes, "ログデータの交換・移動・削除完了", "kanri");
	exit;
} elseif($_POST["mode"] == "log_repair") {
	// (9)ログ(登録データ)の修復(&log_repair)
	pass_check();
	require $cfg["temp_path"] . "admin/log_repair.html";
	exit;
} elseif($_POST["mode"] == "log_repair_act") {
	// (9.1)ログ(登録データ)の修復実行(&log_repair_act)
	pass_check();
	if($_POST["act"] == "dump") {
		if($_POST["dump"] != "on") {
			mes("修復確認のため、確認チェックを入れてからもう一度実行してください", "確認チェックをしてください", "java");
		}
		$query = "SELECT * FROM {$db->db_pre}log ORDER BY id";
		$result = $db->query($query);
		$fp = fopen($_POST["file"], "w");
		while($line = $db->fetch_num($result)) {
			fputs($fp, implode("\t", $line)."\n");
		}
		fclose($fp);
		mes("データのバックアップが完了しました", "バックアップ完了", "kanri");
	} elseif($_POST["act"] == "restore") {
		if($_POST["restore"] != "on") {
			mes("修復確認のため、確認チェックを入れてからもう一度実行してください", "確認チェックをしてください", "java");
		}
		$db->remake("{$db->db_pre}log") or $db->error("Query failed".__FILE__.__LINE__);
		$fp = fopen($_POST["file"], "r");
		while($line =  fgets($fp)) {
			$line = rtrim(addslashes($line));
			$Slog = explode("\t", $line);
			$query = "INSERT INTO {$db->db_pre}log VALUES ('{$Slog[0]}', '{$Slog[1]}', '{$Slog[2]}', '{$Slog[3]}', '{$Slog[4]}', '{$Slog[5]}', '{$Slog[6]}', '{$Slog[7]}', '{$Slog[8]}', '{$Slog[9]}', '{$Slog[10]}', '{$Slog[11]}', '{$Slog[12]}', '{$Slog[13]}', '{$Slog[14]}', '{$Slog[15]}')";
			$db->query($query);
		}
		fclose($fp);
		mes("データの復元が完了しました", "復元完了", "kanri");
	}
	exit;
} elseif($_POST["mode"] == "config") {
	// (11)環境設定 (&config)
	pass_check();
	require $cfg["temp_path"] . "admin/config.html";
	exit;
} elseif($_POST["mode"] == "config_kt") {
	// (12)カテゴリ設定 (&config_kt)
	pass_check();
	$query = "SELECT * FROM {$db->db_pre}category ORDER BY path";
	$rowset = $db->rowset_assoc($query);
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
} elseif($_POST["mode"] == "rank_cfg") {
	// (13)人気ランキングの設定(&rank_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/rank_cfg.html";
	exit;
} elseif($_POST["mode"] == "dl_check") {
	// (14)デッドリンクチェック画面(&dl_check)
	pass_check();
	$query = "SELECT id,count(*) total FROM {$db->db_pre}report GROUP BY id";
	$report = $db->rowset_assoc($query);
	foreach($report as $key=>$id) {
		$query = "SELECT sum(no_url),sum(move),sum(no_banner),sum(violation),sum(other) FROM {$db->db_pre}report WHERE id={$id["id"]}";
		$count = $db->single_num($query);
		$report[$key]["count"] = $count;
		$query = "SELECT ip,name,email,comment FROM {$db->db_pre}report WHERE id={$id["id"]}";
		$rowset = $db->rowset_assoc($query);
		foreach($rowset as $row) {
			$report[$key]["comment"] .= "{$row["name"]} / {$row["email"]}\nIP:{$row["ip"]}:\n{$row["comment"]}\n+--------+\n";
		}
		$query = "SELECT title,url,banner FROM {$db->db_pre}log WHERE id={$id["id"]}";
		$res = $db->single_num($query);
		$report[$key]["title"] = $res[0];
		$report[$key]["url"] = $res[1];
		$report[$key]["banner"] = $res[2];
	}
	require $cfg["temp_path"] . "admin/dl_check.html";
	exit;
} elseif($_POST["mode"] == "dl_check_dl") {
	// (14.1)デッドリンクチェック用ファイルをダウンロード(&dl_check_dl)
	pass_check();
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=Item.dat");
	$query = "SELECT id,url FROM {$db->db_pre}log";
	$rowset = $db->rowset_assoc($query) or $db->error("Query failed".__FILE__.__LINE__);
	foreach ($rowset as $Slog){
		echo $Slog["id"] . "\t" . $Slog["url"] . "\t\t\t0\t\t\t\t\t\t\t\t0\t\t\t\n";
	}
	exit;
} elseif($_POST["mode"] == "dl_check_act") {
	// (14.2)デッドリンクチェック実行画面(&dl_check_act)
	$lines = array();
	pass_check();
	if(!is_file($_POST["checkfile"])) {
		mes("指定されたファイルは存在しません", "エラー", "java");
	}
	$fp = fopen("./{$_POST["checkfile"]}", "r");
	while($tmp = fgets($fp)) {
		$data = explode("\t", $tmp);
		if(strstr($data[13], "Not Found") || strstr($data[13], "Forbidden")) {
			$url[$data[0]] = $data[1];
			array_push($lines, $tmp);
		}
	}
	fclose($fp);
	$fp = fopen("./{$_POST["checkfile"]}", "w");
	foreach($lines as $tmp) {
		fputs($fp, $tmp);
	}
	fclose($fp);
	require $cfg["temp_path"] . "admin/dl_check_act.html";
	exit;
} elseif($_POST["mode"] == "mylink_cfg") {
	// (15)マイリンクの設定(&mylink_cfg)
	pass_check();
	require $cfg["temp_path"] . "admin/mylink_cfg.html";
	exit;
} elseif($_POST["mode"] == "ver_info") {
	// (18)バージョン情報(&ver_info)
	pass_check();
	require $cfg["temp_path"] . "admin/ver_info.html";
	exit;
} elseif($_POST["mode"] == "cfg_admin_pass") {
	// 管理者パスワード変更(cfg_admin_pass)
	pass_check();
	require $cfg["temp_path"] . "admin/cfg_admin_pass.html";
	exit;
} elseif($_POST["mode"] == "look_mes") {
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
} elseif($_POST["mode"] == "new_pass") {
	// パスワードを更新(new_pass)
	// パスワードチェック
	if($cfg["pass"] != "setup") {
		pass_check();
	}
	if(!preg_match("/^\w{3,8}$/", $_POST["new_pass"])) {
		$_POST["pass"] = "";
		mes("パスワードは英数、_(アンダースコア)で3-8文字です", "パスワード入力エラー", "kanri");
		exit;
	}
	if($_POST["new_pass"] != $_POST["re_pass"]) {
		$_POST["pass"] = "";
		mes("確認用再入力が一致しませんでした", "パスワード入力エラー", "kanri");
		exit;
	}
	// パスワードを暗号化する
	$_POST["pass"] = $_POST["new_pass"];
	$_POST["new_pass"] = crypt($_POST["new_pass"]);
	$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST["new_pass"]}' WHERE name='pass'";
	$result = $db->query($query);
	mes("パスワードの設定、変更が完了しました", "パスワード設定完了", "kanri");
	exit;
} elseif($_POST["mode"] == "cfg_make") {
	// (cfg1)環境設定($cfg)を更新(cfg_make)
	pass_check();
	foreach($cfg as $key=>$val) {
		if(isset($_POST[$key]) && $_POST[$key] != $cfg[$key] && $key != "pass") {
			$_POST[$key] = $db->escape_string($_POST[$key]);
			$query = "UPDATE {$db->db_pre}cfg SET value='{$_POST[$key]}' WHERE name='{$key}'";
			$result = $db->query($query);
		}
	}
	mes("環境設定の変更設定が完了しました", "環境設定設定完了", "kanri");
	exit;
} elseif($_POST["mode"] == "cfg_make_PR_menu") {
	// (cfg2)環境設定(&search_form/&menu_bar)を更新(&cfg_make_PR_menu)
	$file_data=array();
	pass_check();
	$fl = 0;
	$p_fl = 1;
	$_POST["search_form"] = str_replace("&lt;", "<", $_POST["search_form"]);
	$_POST["search_form"] = str_replace("&gt;", ">", $_POST["search_form"]);
	$_POST["search_form"] = str_replace("’", "'", $_POST["search_form"]);
	$_POST["search_form"] = $db->escape_string($_POST["search_form"]);
	$_POST["menu_bar"] = str_replace("&lt;", "<", $_POST["menu_bar"]);
	$_POST["menu_bar"] = str_replace("&gt;", ">", $_POST["menu_bar"]);
	$_POST["menu_bar"] = str_replace("’", "'", $_POST["menu_bar"]);
	$_POST["menu_bar"] = $db->escape_string($_POST["menu_bar"]);
	$_POST["head_sp"] = str_replace("&lt;", "<", $_POST["head_sp"]);
	$_POST["head_sp"] = str_replace("&gt;", ">", $_POST["head_sp"]);
	$_POST["head_sp"] = str_replace("’", "'", $_POST["head_sp"]);
	$_POST["head_sp"] = $db->escape_string($_POST["head_sp"]);
	$_POST["foot_sp"] = str_replace("&lt;", "<", $_POST["foot_sp"]);
	$_POST["foot_sp"] = str_replace("&gt;", ">", $_POST["foot_sp"]);
	$_POST["foot_sp"] = str_replace("’", "'", $_POST["foot_sp"]);
	$_POST["foot_sp"] = $db->escape_string($_POST["foot_sp"]);
	$query = "UPDATE {$db->db_pre}text SET value='{$_POST["search_form"]}' WHERE name='search_form'";
	$result = $db->query($query);
	$query = "UPDATE {$db->db_pre}text SET value='{$_POST["menu_bar"]}' WHERE name='menu_bar'";
	$result = $db->query($query);
	$query = "UPDATE {$db->db_pre}text SET value='{$_POST["head_sp"]}' WHERE name='head_sp'";
	$result = $db->query($query);
	$query = "UPDATE {$db->db_pre}text SET value='{$_POST["foot_sp"]}' WHERE name='foot_sp'";
	$result = $db->query($query);
	mes("メニューバー/外部検索エンジン/ヘッダ・フッタスペースの設定が完了しました", "更新完了", "kanri");
	exit;
} elseif($_POST["mode"] == "cfg_make_reg") {
	// (cfg3)環境設定(登録処理関係)を更新 (&cfg_make_reg)
	pass_check();
	if(!isset($cfg_reg['kt_select_mode'])){
		$query = "INSERT INTO {$db->db_pre}cfg_reg VALUES('kt_select_mode','multiple')";
		$result = $db->query($query);
	}
	foreach($cfg_reg as $key=>$val) {
		if(isset($_POST[$key]) and $_POST[$key] != $cfg_reg[$key]) {
			$_POST[$key] = $db->escape_string($_POST[$key]);
			$query = "UPDATE {$db->db_pre}cfg_reg SET value='{$_POST[$key]}' WHERE name='{$key}'";
			$result = $db->query($query);
		}
	}
	mes("環境設定(登録処理関係)の変更が完了しました", "環境設定(登録処理関係)設定完了", "kanri");
	exit;
} elseif($_POST["mode"] == "cfg_make_kt") {
	// (cfg5)カテゴリ設定を更新 (&cfg_make_kt)
	pass_check();
	if($_POST["mente_mode"] == "mente") {
        // 削除指定されたカテゴリにログが残っていないか確認
        foreach($_POST['kt'] as $key => $val) {
            if (isset($_POST['d'][$key])) {
                $query = "SELECT "
                       .     "COUNT(*) "
                       . "FROM "
                       .     $db->db_pre . "log "
                       . "WHERE "
                       .     "category LIKE '%&" . $key . "%&%'";
                $res = $db->query($query);
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
        foreach ($_POST['kt'] as $key => $val) {
            // 削除でなければ
			if (!isset($_POST['d'][$key])) {
				if($_POST["ref"][$key]) {
					$reflist = explode("&", $_POST["ref"][$key]);
					foreach($reflist as $num=>$refpath) {
						if($refpath) {
							$query = "SELECT id FROM {$db->db_pre}category WHERE path='{$refpath}' LIMIT 1";
							$row = $db->single_assoc($query);
							$reflist[$num] = $row["id"];
						}
					}
					$_POST["ref"][$key] = implode("&", $reflist);
				}
				$t = $_POST["t"][$key];
				$o = $_POST["o"][$key];
				$no = $_POST["no"][$key];
				$ref = $_POST["ref"][$key];
				$furi = $_POST["furi"][$key];
				$cmt = $_POST["cmt"][$key];
				$query = "SELECT "
                       .     "id "
                       . "FROM "
                       .     $db->db_pre . "category "
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
				$row = $db->single_assoc($query);
				if($row["id"]) {
					continue;
				} else {
					$val = $db->escape_string($val);
					$furi = $db->escape_string($furi);
					$cmt = $db->escape_string($cmt);
					$query = "UPDATE {$db->db_pre}category SET title='{$val}', top_list='{$t}', etc_list='{$o}', regist='{$no}', reffer='{$ref}', sort_name='{$furi}', comment='{$cmt}' WHERE path='{$key}'";
					$db->query($query);
				}
			} else {
				$query = "DELETE FROM {$db->db_pre}category WHERE path='{$key}'";
				$db->query($query);
			}
		}
	}
	if($_POST["mente_mode"] == "new" && $_POST["kt_new"]) {
		// 新規追加分を定義
		$_POST["kt_new"] = str_replace(array("&gt;", "&lt;"), array(">", "<"), $_POST["kt_new"]);
		$_POST["kt_new"] = trim($_POST["kt_new"]);
		$new = explode("\n", $_POST["kt_new"]);
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
				$query = "SELECT id FROM {$db->db_pre}category WHERE path='{$dir}/'";
				$row = $db->single_assoc($query);
				if($row["id"]) {
					$up_id = $row[id];
				} else {
					mes("カテゴリの上位階層がありません({$dir}/)", "カテゴリ設定エラー", "kanri");
					exit;
				}
			}
			$query = "DELETE FROM {$db->db_pre}category WHERE path='{$kt[0]}'";
			$db->query($query);
			$query = "INSERT INTO {$db->db_pre}category VALUES(NULL, '{$kt[1]}', '{$up_id}', '{$kt[0]}', '', '', '', '', '', '')";
			$db->query($query);
		}
	}
	mes("カテゴリの設定が完了しました", "カテゴリ設定完了", "kanri");
	exit;
} elseif($_POST["mode"] == "cfg_reg") {
	// 環境設定(登録処理関係)
	require $cfg["temp_path"] . "admin/cfg_reg.html";
	exit;
} elseif($_POST["mode"] == "cfg_html") {
	// HTMLの設定
	pass_check();
	$PR_search_form = rtrim($text["search_form"]);
	$PR_search_form = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_search_form);
	$PR_menu_bar = rtrim($text["menu_bar"]);
	$PR_menu_bar = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_menu_bar);
	$PR_head_sp = rtrim($text["head_sp"]);
	$PR_head_sp = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_head_sp);
	$PR_foot_sp = rtrim($text["foot_sp"]);
	$PR_foot_sp = str_replace(array("<",">"), array("&lt;","&gt;"), $PR_foot_sp);
	require $cfg["temp_path"] . "admin/cfg_html.html";
	exit;
} elseif($_POST["mode"] == "cfg_marks") {
	require $cfg["temp_path"] . "admin/cfg_marks.html";
	exit;
}
} else {
	// (1)ログイン画面(&login)
	require $cfg["temp_path"] . "admin/login.html";
	exit;
}

// (6)メッセージ画面出力(&mes)
// 書式:&mes($arg1,$arg2,$arg3);
// 機能:メッセージ画面を出力する
// 引数:$arg1=>表示するメッセージ
//      $arg2=>ページのタイトル(省略時は「メッセージ画面」)
//      $arg3=>・JavaScriptによる「戻る」ボタン表示=java
//             ・$ENV{'HTTP_REFERER'}を使う場合=env
//             ・管理室へのボタン=kanri
//             ・通常のURL又はパスを指定する場合にはそのURL又はパスを記入
//             ・省略時は非表示
// 戻り値:なし
function mes($mes, $title="", $arg3="") {
	global $cfg, $db;
	if(!$title) {
		$title = "メッセージ画面";
	}
	if($arg3 == "java") {
		$back_url="<form><input type=\"button\" value=\"&nbsp;&nbsp;&nbsp;&nbsp;戻る&nbsp;&nbsp;&nbsp;&nbsp;\" onClick=\"history.back()\"></form>";
	} elseif($arg3 == "env") {
		$back_url="【<a href=\"$_SERVER[HTTP_REFERER]\">戻る</a>】";
	} elseif($arg3 == "kanri") {
		$back_url="<form action=\"{$cfg["admin"]}\" method=\"post\"><input type=\"hidden\" name=\"mode\" value=\"kanri\"><input type=\"hidden\" name=\"pass\" value=\"{$_POST["pass"]}\"><input type=\"submit\" value=\"管理室へ\"></form>";
	} elseif(!$arg3) {
		$back_url = "";
	} else {
		$back_url="【<a href=\"{$arg3}\">戻る</a>】";
	}
	require $cfg['temp_path'] . 'mes.html';
	exit;
}
?>