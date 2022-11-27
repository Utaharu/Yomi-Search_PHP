<?php
//----------------------------------------------------------------------------
// (f1)登録するカテゴリを表示(print_category)
function print_category($category = '', $smartphone_flg = false) {
	global $cfg, $cfg_reg, $db;
	if(!isset($_POST['changer'])) {
		$_POST['changer'] = '';
	}

	$selected_categories = array();
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($_POST["Fkt{$i}"]) and $_POST["Fkt{$i}"]){
			$selected_categories[] = $_POST["Fkt{$i}"];
		}
	}
	
	//Get DB Categories
	$query = 'SELECT path, regist FROM '.$db->db_pre.'category ORDER BY path';
	$categories_data = $db->rowset_assoc($query);
	
    $writeStr = '';
	$category_list = explode('&', $category);
	if($cfg_reg['kt_min'] != $cfg_reg['kt_max']){
		$writeStr .=  '<ul>※<b>'.$cfg_reg['kt_min'].'</b>～<b>'.$cfg_reg['kt_max'].'</b>個まで選択できます<br>';
	} else {
		$writeStr .=   '<ul>※<b>'.$cfg_reg['kt_max'].'</b>個選択してください<br>';
	}
	$writeStr .=   '※各カテゴリの詳細は「<a href="sitemap.php" target="_blank">カテゴリ一覧</a>」を参考にしてください<br>'."\n";
	/*
		$selecter_mode = '' or 'multiple'
		$selecter_name is <select name="$selecter_name">
	*/
	//selecter_mode
	$selecter_mode = "multiple";
	if(isset($cfg_reg['kt_select_mode']) and $cfg_reg['kt_select_mode'] != "multiple"){$selecter_mode = "";}
	
	//selecter_max
	$selecter_max = $cfg_reg['kt_max'];
	if($selecter_mode == "multiple"){$selecter_max = 1;}
	
	//html - print selectbox
	for($category_no = 1; $category_no <= $selecter_max; $category_no++){
		//selecter name
		$selecter_name = "Fkt" . $category_no;
		if($selecter_mode == "multiple"){$selecter_name .= "[]";}
		
		//Html - Select Tag
        if(defined('SMARTPHONE_SITE_NAME')){$writeStr .=   '<div data-role="fieldcontain"><label for="Fkt'.$category_no.'" class="select"></label>';}
		$writeStr .=  "<select name=\"{$selecter_name}\" size=\"7\"";
			if(defined('SMARTPHONE_SITE_NAME')) $writeStr .=  " id=\"Fkt{$category_no}\" data-native-menu=\"false\"";
			if($selecter_mode == "multiple"){$writeStr .= " multiple=\"multiple\"";}
        $writeStr .=  '>';
		
		$select = ' selected';
		if(isset($category_list[$category_no]) && $category_list[$category_no] != ''){
			if($selecter_mode == "multiple"){
				foreach ($category_list as $category_value){
					if($category_value){
						$writeStr .= '<option value="'. $category_value . '"'.$select.'>' . full_category($category_value) . "</option>\n";
					}
				}
			}else{
				$writeStr .=  '<option value="'. $category_list[$category_no] . '"'.$select.'>' . full_category($category_list[$category_no]) . "</option>\n";
			}
			$select = '';
		}
		
		if(!defined('SMARTPHONE_SITE_NAME')){
			if(count($selected_categories) > 0){$select = "";}
			$writeStr .=  "<option value=\"\" {$select} >--指定しない--</option>";
		}else{$writeStr .= "<option value=\"\">カテゴリ</option>\n";}
		
		//DB - Print Categories
		if(is_array($categories_data)){
			foreach($categories_data as $row){
				$selected_flag = "";
				if($_POST['changer'] == 'admin' || !$row['regist']){
					$pm = preg_replace("/\//","\/",$row['path']);
					if(preg_grep("/^" . $pm . "$/",$selected_categories)){$selected_flag = " selected";}
					$writeStr .=  "<option value=\"" . $row['path'] . "\"" . $selected_flag .">" . full_category($row['path']) . "</option>\n";
				}
			}
		}

		$writeStr .=  '</select>';
        if(defined('SMARTPHONE_SITE_NAME')) $writeStr .=  '</div> ';
        $writeStr .=  '<br><br>'."\n";
	}
	$writeStr .=  '</ul><br>'."\n";
    if($smartphone_flg == true) {
        $writeStr =  str_replace(array('<ul>','</ul>'), '', $writeStr);
        $writeStr =  str_replace('<br><br>', '', $writeStr);
        $writeStr =  str_replace('エンターテイメント', 'ｴﾝﾀﾒ', $writeStr);
        $writeStr =  str_replace('ホームページ', 'HP', $writeStr);
        echo $writeStr;
    } else {
        echo $writeStr;
    }
}

// (f2)メッセージ画面出力(mes)
// 書式:mes($arg1,$arg2,$arg3);
// 機能:メッセージ画面を出力する
// 引数:$arg1=>表示するメッセージ
//      $arg2=>ページのタイトル(省略時は「メッセージ画面」)
//      $arg3=>・JavaScriptによる「戻る」ボタン表示=java
//             ・HTTP_REFERERを使う場合=env
//             ・管理室へのボタン=kanri
//             ・通常のURL又はパスを指定する場合にはそのURL又はパスを記入
//             ・省略時は非表示
// 戻り値:なし
function mes($mes, $title = '', $arg3 = '') {
	global $cfg, $cfg_reg, $db;

    $back_url = "\n";

	if (!$title) {
		$title = 'メッセージ画面';
	}
	if ($arg3 == 'java' || ($arg3 == 'back_reg' && $_POST['mode'] == 'act_mente')) {
		$back_url = '<form>'
		          . '<input type="button" value="    戻る    " onClick="history.back()">'
				  . '</form>' . "\n";
	} elseif($arg3 == 'env') {
		$back_url = '【<a href="' . $_SERVER['HTTP_REFERER'] . '">戻る</a>】';
	} elseif($arg3 == 'kanri') {
		$back_url  = '<form action="' . $cfg['admin'] . '" method="post">' . "\n";
		$back_url .= '<input type="hidden" name="mode" value="kanri">' . "\n";
		$back_url .= '<input type="hidden" name="pass" value="' . $_POST["pass"] . '">' . "\n";
		$back_url .= '<input type="submit" value="管理室へ"></form>' . "\n";
	} elseif (!$arg3) {
		$back_url = '';
	} elseif ($arg3 == 'back_reg') {
		$_POST['Fsyoukai'] = str_replace('<br>', "\n", $_POST['Fsyoukai']);
		$back_url .= '<form action="regist_ys.php" method="post">' . "\n";
        if ($_POST['changer'] == 'admin') {
            $back_url .= '    <input type="hidden" name="mode" value="new_dairi">' . "\n";
        }
		if(isset($_POST['pass'])) {
			$back_url .= '    <input type="hidden" name="pass" value="' . $_POST["pass"] . '">' . "\n";
		} else {
			$back_url .= '    <input type="hidden" name="pass" value="">' . "\n";
		}
		$back_url .= <<<EOM
    <input type="hidden" name="changer" value="{$_POST['changer']}">
    <input type="hidden" name="Fname" value="{$_POST['Fname']}">
    <input type="hidden" name="Femail" value="{$_POST['Femail']}">
    <input type="hidden" name="Fpass" value="{$_POST['Fpass']}">
    <input type="hidden" name="Fpass2" value="{$_POST['Fpass2']}">
    <input type="hidden" name="Furl" value="{$_POST['Furl']}">
    <input type="hidden" name="Fbana_url" value="{$_POST['Fbana_url']}">
    <input type="hidden" name="Ftitle" value="{$_POST['Ftitle']}">
    <input type="hidden" name="Fsyoukai" value="{$_POST['Fsyoukai']}">
EOM;
		if (isset($_POST['Fkanricom'])) {
			$back_url .= '<input type="hidden" name="Fkanricom" value="' . $_POST['Fkanricom'] . '">' . "\n";
		} else {
			$back_url .= '<input type="hidden" name="Fkanricom" value="">';
		}
        for ($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
			if(isset($_POST['Fkt'.$i]) and !is_array($_POST['Fkt'.$i])){
				$back_url .= '<input type="hidden" name="Fkt' . $i . '" value="' . $_POST['Fkt'.$i] . '">' . "\n";
			}
        }
		$back_url .= <<<EOM
    <input type="hidden" name="Fkey" value="{$_POST['Fkey']}">
    <input type="hidden" name="Fadd_kt" value="{$_POST['Fadd_kt']}">
    <input type="hidden" name="Fto_admin" value="{$_POST['Fto_admin']}">
EOM;
		if (isset($_POST['Fsougo'])) {
			$back_url .= '    <input type="hidden" name="Fsougo" value="' . $_POST['Fsougo'] . '">' . "\n";
		} else {
			$back_url .= '    <input type="hidden" name="Fsougo" value="">' . "\n";
		}
		$back_url .= '    <input type="submit" value="登録画面に戻る">' . "\n";
	} else {
		$back_url = '【<a href="' . $arg3 . '">戻る</a>】' . "\n";
	}
	header('Content-type: text/html; charset=UTF-8');
	require $cfg['temp_path'] . 'mes.html';
	exit;
}

// (f3)入力内容のチェック(check)
function check() {
	// 禁止ワードのチェック
	global $cfg, $cfg_reg, $db;
	
	if(!isset($_POST['changer'])) {
		$_POST['changer'] = '';
	}

	if($cfg_reg['kt_no_word']) {
		// ワードチェック対象の項目
		$check_str = implode(' ', array($_POST['Fname'], $_POST['Femail'], $_POST['Furl'], $_POST['Fbana_url'], $_POST['Ftitle'], $_POST['Fsyoukai'], $_POST['Fkey']));
		$no_words = explode(' ', $cfg_reg['kt_no_word']);
		foreach($no_words as $word) {
			if(stristr($check_str, $word)) {
				mes("登録データの中にが禁止されている言葉が入っています。<br>登録しようとしているデータのジャンルをこのサーチエンジンが禁止している可能性があります。", "ワードチェックエラー", "back_reg");
			}
		}
		if(!$_SERVER["REMOTE_HOST"]) {
			$_SERVER["REMOTE_HOST"] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
		}
		$addr_host = $_SERVER["REMOTE_ADDR"] . " " . $_SERVER["REMOTE_HOST"];
		foreach($no_words as $word) {
			if(stristr($addr_host, $word)) {
				mes("このIP又はホスト名からの登録は禁止されている可能性があります。<br>{$_SERVER["REMOTE_ADDR"]}/{$_SERVER["REMOTE_HOST"]}<br>", "IP/HOSTチェックエラー", "back_reg");
			}
		}
	}
	// 名前
	if($cfg_reg["Fname"] && !$_POST["Fname"]) {
		mes("<b>お名前</b>は<font color=red>記入必須項目</font>です", "記入ミス", "back_reg");
	}
	$num = mb_strwidth($_POST['Fname']) - $cfg_reg['Mname'] * 2;
	if($num > 0) {
		mes("<b>お名前</b>は全角<b>{$cfg_reg['Mname']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	$_POST['Fname'] = str_replace(array("\r", "\n"), "", $_POST['Fname']);
	// メールアドレス
	if($cfg_reg['Femail'] && !$_POST['Femail']) {
		mes("<b>メールアドレス</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	} elseif(strlen($_POST['Femail']) - $cfg_reg['Memail'] > 0) {
		$num = strlen($_POST['Femail']) - $cfg_reg['Memail'];
		mes("<b>メールアドレス</b>は半角<b>{$cfg_reg['Memail']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	} elseif($cfg_reg['Femail'] && !preg_match("/(.+)\@(.+)\.(.+)/", $_POST['Femail'])) {
		mes("<b>メールアドレス</b>の入力が正しくありません", "記入ミス", "back_reg");
	}
	$_POST['Femail'] = str_replace(array("\r", "\n"), "", $_POST['Femail']);
	// パスワード
	if($_POST['mode'] != "act_mente"){
		if(!$_POST['Fpass']) {
			mes("<b>パスワード</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		} elseif(preg_match("/\W/", $_POST['Fpass'])) {
			$_POST['Fpass'] = "";
			$_POST['Fpass2'] = "";
			mes("<b>パスワード</b>には全角文字は使用できません", "入力ミス", "back_reg");
		} elseif(strlen($_POST['Fpass']) > 8) {
			$num = strlen($_POST['Fpass']) - 8;
			mes("<b>パスワード</b>は半角英数<b>8</b>文字以内でご記入ください", "文字数オーバー({$num}文字分)", "back_reg");
		} elseif($_POST['Fpass'] != $_POST['Fpass2']) {
			mes("2回の<b>パスワード</b>入力が一致しませんでした", "入力ミス", "back_reg");
		}
		$_POST['Fpass'] = str_replace(array("\r", "\n"), "", $_POST['Fpass']);
	}
	// ホームページアドレス(2重登録チェックは別のところに記述)
	if($_POST['Furl'] == "http://") { $_POST['Furl']=""; }
	if($cfg_reg['Furl'] && !$_POST['Furl']) {
		mes("<b>ホームページアドレス</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	} elseif(strlen($_POST['Furl']) - $cfg_reg['Murl'] > 0) {
		$num = strlen($_POST['Furl']) - $cfg_reg['Murl'];
		mes("<b>ホームページアドレス</b>は半角<b>{$cfg_reg['Murl']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	} elseif($_POST['Furl'] && !preg_match("/^https?:\/\/.+\..+/", $_POST['Furl'])) {
		mes("<b>ホームページアドレス</b>の入力が正しくありません", "記入ミス", "back_reg");
	}
	$_POST['Furl'] = str_replace(array("\r", "\n"), "", $_POST['Furl']);
	// タイトルバナーのURL
	if ($cfg_reg['bana_url']) {
		if (!@$_POST['Fbana_url']) { $_POST['Fbana_url'] = ""; }
		if ($_POST['Fbana_url'] == "http://") { $_POST['Fbana_url'] = ""; }
		if ($cfg_reg['Fbana_url'] && !$_POST['Fbana_url']) {
            $msg = '<b>タイトルバナーのURL</b>は<font color="#FF0000">記入必須項目</font>です。';
            mes($msg, "記入ミス", "back_reg");
        } elseif (strlen($_POST['Fbana_url']) - $cfg_reg['Mbana_url'] > 0) {
			$num = strlen($_POST['Fbana_url']) - $cfg_reg['Mbana_url'];
            $msg = '<b>タイトルバナーのURL</b>は半角<b>' . $cfg_reg['Mbana_url'] . '</b>文字以内でご記入ください。';
            mes($msg, "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
        } elseif ($_POST['Fbana_url'] && !preg_match("/^https?:\/\/.+\..+\.(gif|jpg|jpeg|png)$/", $_POST['Fbana_url'])) {
            $msg = '<b>タイトルバナーのURL</b>の入力が正しくありません。';
            mes($msg, "記入ミス", "back_reg");
        } elseif ($_POST['Fbana_url']) {
            $FbanaInfo = @getimagesize($_POST['Fbana_url']);
            if (!$FbanaInfo) {
                $msg = 'バナー画像を取得できませんでした。<br />'
                     . 'バナーURLに入力ミスがあるか、又はバナーを設置しているサーバーが外部からの参照を禁止している可能性があります。';
                mes($msg, '記入ミス', 'back_reg');
			}
		}
	} else {
		$_POST['Fbana_url'] = "";
	}
	$_POST['Fbana_url'] = str_replace(array("\r", "\n"), "", $_POST['Fbana_url']);
	// ホームページのタイトル
	if($cfg_reg['Ftitle'] && !$_POST['Ftitle']) {
		mes("<b>ホームページのタイトル</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	}
	if(mb_strwidth($_POST['Ftitle']) - ($cfg_reg['Mtitle'] * 2) > 0) {
		$num = mb_strwidth($_POST['Ftitle']) - ($cfg_reg['Mtitle'] * 2);
		mes("<b>ホームページのタイトル</b>は全角<b>{$cfg_reg['Mtitle']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	$_POST['Ftitle'] = str_replace(array("\r", "\n"), "", $_POST['Ftitle']);
	// ホームページの紹介文
	if($cfg_reg['Fsyoukai'] && !$_POST['Fsyoukai']) {
		mes("<b>ホームページの紹介文</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	}
	if(mb_strwidth($_POST['Fsyoukai']) - ($cfg_reg['Msyoukai'] * 2) > 0) {
		$num = mb_strwidth($_POST['Fsyoukai']) - ($cfg_reg['Msyoukai'] * 2);
		mes("<b>ホームページの紹介文</b>は全角<b>{$cfg_reg['Msyoukai']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	if(!$cfg['syoukai_br']) {
		$_POST['Fsyoukai'] = str_replace(array("\r", "\n"), "", $_POST['Fsyoukai']);
	} else {
		$_POST['Fsyoukai'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $_POST['Fsyoukai']);
	}
	// 管理人コメント
	if(@$_POST['Fkanricom']) {
		$_POST['Fkanricom'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $_POST['Fkanricom']);
	}
	
	// カテゴリ
	// form - 登録するカテゴリ - 情報整形
	$regist_categories = array();
	$post_category_count = 0;
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($_POST['Fkt'.$i])){
			if($_POST['Fkt'.$i] != ""){
				$post_category_count++;
				if(is_array($_POST['Fkt'.$i])){
					foreach($_POST['Fkt'.$i] as $fkt_line){
						if($fkt_line){
							$regist_categories['Fkt'.$post_category_count] = $fkt_line;
							$post_category_count++;
						}
					}
				}elseif(!is_array($_POST['Fkt'.$i]) and $_POST['Fkt'.$i] != ""){
					$regist_categories['Fkt'.$post_category_count] = $_POST['Fkt'.$i];
				}
			}
			unset($_POST['Fkt'.$i]);			
		}
	}
	$_POST = array_merge($_POST,$regist_categories);
	
	$kt_fl = array();
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($_POST["Fkt{$i}"]) and $_POST["Fkt{$i}"]){
			$query = "SELECT id, regist FROM {$db->db_pre}category WHERE path='". $_POST["Fkt{$i}"] . "'";
			$regist = $db->single_assoc($query) or $db->error("Query failed $query".__FILE__.__LINE__);
			if(isset($_POST["Fkt{$i}"])) {
				$_POST["Fkt{$i}"] = str_replace(array("\r", "\n"), "", $_POST["Fkt{$i}"]);
			}
			if(isset($kt_fl[$_POST["Fkt{$i}"]])) {
				$_POST["Fkt{$i}"] = '';
			} elseif($regist['id']) {
				$kt_fl[$_POST["Fkt{$i}"]] = 1;
			} else {
				$_POST["Fkt{$i}"] = '';
			}

			// 禁止カテゴリに登録しようとした場合
			if($_POST['changer'] != "admin" && $regist['regist']) {
				mes("登録者の登録ができないカテゴリに変更しようとしています", "カテゴリ選択ミス", "back_reg");
			}
		}
		
	}
	$j = count($kt_fl);
	if ($cfg_reg['kt_min'] == $cfg_reg['kt_max']) {
		$PR_kt="<b>{$cfg_reg['kt_max']}</b>個";
	} else {
		$PR_kt="<b>{$cfg_reg['kt_min']}</b>～<b>{$cfg_reg['kt_max']}</b>個";
	}
	if ($cfg_reg['kt_min'] > $j || $j > $cfg_reg['kt_max']) {
		mes("<b>カテゴリ</b>は{$PR_kt}選択してください", "選択数ミス", "back_reg");
	}
	// キーワード
	if ($cfg_reg['Fkey'] && !$_POST['Fkey']) {
        $msg = '<b>キーワード</b>は<font color="#FF0000">記入必須項目</font>です。';
        mes($msg, "記入ミス", "back_reg");
	}
	if (mb_strwidth($_POST['Fkey']) - ($cfg_reg['Mkey'] * 2) > 0) {
		$num = mb_strwidth($_POST['Fkey']) - ($cfg_reg['Mkey'] * 2);
        $msg      = '<b>キーワード</b>は全角<b>' . $cfg_reg['Mkey'] . '</b>文字以内でご記入ください。';
        $msgTitle = '文字数オーバー(半角換算で' . $num . '文字分)';
        mes($msg, $msgTitle, 'back_reg');
	}
    $targetValues = array('・', '、', '　', ',');
    $_POST['Fkey'] = str_replace($targetValues, ' ', $_POST['Fkey']);
    $_POST['Fkey'] = preg_replace('/\r|\r\n|\n/', ' ', $_POST['Fkey']);
    $_POST['Fkey'] = preg_replace('/\s\s+/', ' ', $_POST['Fkey']);
    $_POST['Fkey'] = trim($_POST['Fkey']);
	// 追加して欲しいカテゴリ
	if($_POST['mode'] != "act_mente" && $_POST['changer'] != "admin") {
		if($cfg_reg['Fadd_kt'] && !$_POST['Fadd_kt']) {
			mes("<b>追加して欲しいカテゴリ</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		}
		if(mb_strwidth($_POST['Fadd_kt']) - ($cfg_reg['Madd_kt'] * 2) > 0) {
			$num = mb_strwidth($_POST['Fadd_kt']) - ($cfg_reg['Madd_kt'] * 2);
			mes("<b>追加して欲しいカテゴリ</b>は全角<b>{$cfg_reg["Madd_kt"]}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
		}
		$_POST['Fadd_kt'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $_POST['Fadd_kt']);
	}
	// 相互リンクの有無
	$sougo[1] = "する";
	$sougo[0] = "しない";
	if(!isset($_POST['Fsougo'])) {
		$_POST['Fsougo'] = 0;
	}
	if($_POST['Fsougo'] != '1') {
		$_POST['Fsougo'] = 0;
	}
	// 管理人へのメッセージ
	if($_POST['mode'] != "act_mente" && $_POST['changer'] != "admin") {
		if($cfg_reg['Fto_admin'] && !$_POST['Fto_admin']) {
			mes("<b>管理人へのメッセージ</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		}
		if(mb_strwidth($_POST['Fto_admin']) - ($cfg_reg['Mto_admin'] * 2) > 0) {
			$num = mb_strwidth($_POST['Fto_admin']) - ($cfg_reg['Mto_admin'] * 2);
			mes('<b>管理人へのメッセージ</b>は全角<b>'.$cfg_reg['Mto_admin'].'</b>文字以内でご記入ください', '文字数オーバー(半角換算で'.$num.'文字分)', 'back_reg');
		}
		if(!$cfg['syoukai_br']) {
			$_POST['Fto_admin'] = str_replace(array("\r", "\n"), '', $_POST['Fto_admin']);
		} else {
			$_POST['Fto_admin'] = str_replace(array("\r\n", "\r", "\n"), '<br>', $_POST['Fto_admin']);
		}
	}
}

// (f4)カテゴリを表示1(preview_category1)
function preview_category1() {
	global $cfg_reg;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		if(isset($_POST['Fkt'.$kt_no])){
			$value = $_POST['Fkt'.$kt_no];
			echo '<input type="hidden" name="Fkt'.$kt_no.'" value="'.$value.'">'."\n";
		}
	}
}

// (f5)カテゴリを表示2(preview_category2)
function preview_category2() {
	global $cfg_reg;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		if(isset($_POST['Fkt'.$kt_no])){
			$value = $_POST['Fkt'.$kt_no];
			echo full_category($value);
			echo '<input type="hidden" name="Fkt'.$kt_no.'" value="'.$value.'"><br>'."\n";
		}
	}
}

// (f6)入力内容の整形(join_fld)
function join_fld($id = '') {
	// 登録更新用のデータ配列
	// $id=登録用のデータID
	// [モード]
	// $_POST['changer'] => 変更者(なし,admin)
	// ※登録内容変更の場合の変更前データは「$pre_log」に格納されている
	global $pre_log, $cfg, $cfg_reg;
	// 登録No(データID)(0)
	$log_data[0] = $id;
	// タイトル(1)
	$log_data[1] = $_POST['Ftitle'];
	// URL(2)
	$log_data[2] = $_POST['Furl'];
	// マークデータ(3)
	if($_POST['changer'] == 'admin') { // 変更者が管理人
		$_POST['Fmark'] = '';
		for($i = 1; $i <= 10; $i++) { // ←マーク数を増やすときは修正
			if(isset($_POST['Fmark'.$i])) {
				$_POST['Fmark'] .= '1_';
			} else {
				$_POST['Fmark'] .= '0_';
			}
		}
		$_POST['Fmark'] = substr($_POST['Fmark'], 0, -1);
		$log_data[3] = $_POST['Fmark'];
	} elseif($_POST['mode'] == 'act_mente'){ // 登録者の変更
		$log_data[3] = $pre_log[3];
	} else { // 登録者の新規登録
		$log_data[3] = '0_0_0_0_0_0_0_0_0_0';
	}
	// 更新日(4)
	// 日時の取得
	$log_data[4] = get_time(0, 1);
	// パスワード(5)
	if(isset($_POST['Fpass'])) {
		$log_data[5] = crypt($_POST['Fpass'], 'ys');
	} else {
		$log_data[5] = '';
	}
	// 紹介文(6)
	$log_data[6] = $_POST['Fsyoukai'];
	// 管理人コメント(7)
	if($_POST['changer'] == 'admin'){ // 変更者が管理人
		$log_data[7] = $_POST['Fkanricom'];
	} else { // 登録者の変更
		if(isset($pre_log[7])){$log_data[7] = $pre_log[7];}
		else{$log_data[7] = "";}
	}
	// お名前(8)
	$log_data[8] = $_POST['Fname'];
	// E-mail(9)
	$log_data[9] = $_POST['Femail'];
	// カテゴリ(10)
	if($cfg['user_change_kt'] && $_POST['mode'] == 'act_mente' && $_POST['changer'] != 'admin') { // 登録者の変更でカテゴリ変更禁止の場合
		$i = 0;
		$category = explode('&', $pre_log[10]);
		$log_data[10] = $pre_log[10];
		foreach($category as $tmp) {
			$_POST['Fkt'.$i] = $tmp;
			$i++;
		}
	} else { // その他の場合
		$log_data[10] = '&';
		for($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
			if(isset($_POST['Fkt'.$i])){$log_data[10] .= $_POST['Fkt'.$i] . '&';}
		}
	}
	// time形式(11)新規or更新(13)
	$times = time();
	$log_data[11] = $times;
	if($_POST['mode'] == 'act_mente') { // 内容変更時
		if(!$pre_log[13] && $times - $pre_log[11] < $cfg['new_time'] * 86400) {
			$log_data[13] = '0';
		} else {
			$log_data[13] = '1';
		}
	} else { // 新規登録時
		$log_data[13] = '0';
	}
	// バナーURL(12)
	$log_data[12] = $_POST['Fbana_url'];
	// 最終アクセスIP(14)
	$log_data[14] = $_SERVER['REMOTE_ADDR'];
	// キーワード(15)
	$log_data[15] = $_POST['Fkey'];
	// 仮登録モードの場合の設定
	if($cfg['user_check'] && $_POST['changer'] != 'admin' && $_POST['mode'] == 'act_regist') {
		$log_data[7] = implode('<1>', array($_POST['Fsougo'], $_POST['Fadd_kt'], $_POST['Fto_admin']));
	}
	if($_POST['changer'] == 'admin' && $pre_log) {
		$log_data[4] = $pre_log[4];
		$log_data[11] = $pre_log[11];
		$log_data[13] = $pre_log[13];
	}
	ksort($log_data);
	return $log_data;
}

// (f7)2重URL登録チェック(get_id_url_ch)
// $fl => (新規登録=1/内容変更=2)
function get_id_url_ch($fl) {
	global $cfg, $db;
	$i = 0;
	$log_data = array();
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db->db_pre.'log WHERE url=\''.$_POST['Furl'].'\' LIMIT 1';
	$log_data = $db->single_num($query);
	if($log_data) {
		if($_POST['Furl'] == $log_data[2]) {
			$i++;
			$pre_title = $log_data[1];
		}
		if(isset($_POST['id'])) {
			if($_POST['id'] == $log_data[0]) {
				$pre_log = $log_data;
			}
		}
		if($fl <= $i) {
			mes('そのURLはすでに登録されています<br><br>'.$log_data[1].' :<br>'.$log_data[2], '2重登録エラー', 'java');
		}
		if($fl == '2' && $i == '1' && $pre_log[2] != $_POST['Furl']) {
			mes('そのURLはすでに登録されています<br><br>'.$pre_title.' :<br>'.$_POST['Furl'], '2重登録エラー', 'java');
		}
	}
	if($_POST['changer'] != 'admin' && $cfg['user_check'] && $_POST['mode'] == 'act_regist' && $fl == 1) {
		// 仮登録モードでユーザの新規登録時
		$query = 'SELECT title FROM '.$db->db_pre.'log_temp WHERE url=\''.$_POST['Furl'].'\' LIMIT 1';
		$log_data = $db->single_num($query);
		if($log_data) {
			mes('そのURLは現在登録申請中です<br><br>'.$log_data[0].' :<br>'.$_POST['Furl'], '2重登録エラー', 'java');
		}
	}
}

// (f8)登録結果画面出力(PRend)
function PRend() {
	global $cfg;
	require $cfg['sub_path'] . 'regist_new_end.php';
}

// (f9)マークForm管理者画面出力(PR_mark)
function PR_mark($data = '') {
	global $cfg;
	if ($_REQUEST['changer'] == 'admin') {
            if ($data == '') {  // 配列$markを初期化
                $mark = array('','','','','','','','','','');
            } else {
                $mark = explode('_', $data);
            }
            $w = '';
            $w .= '<li>【マーク】' . "\n";
            $w .= '<ul>' . "\n";
            for ($i = 1; $i <= 10; $i++) { // ←マーク数を増やすときは修正
                    $w .= '<input type="checkbox" name="Fmark' . $i . '" value="1"';
                    if ($mark[$i-1]) {
                            $w .= ' checked="checked"';
                    }
                    $w .= '>' . $cfg['name_m'.$i] . '&nbsp;&nbsp;&nbsp;';
            }
            $w .= '</ul><br>' . "\n";
            echo $w;
	}
}

// (f10)スパム登録対策用コード認証チェック(check_certification_cord)
function check_certification_cord() {

	// 認証前の準備
	if(!isset($_POST['changer'])) {
		$_POST['changer'] = '';
	}
	if($_POST['changer'] != 'admin') {
		$_POST['certification_cord'] = htmlspecialchars($_POST['certification_cord']);
		$_POST['Fcertification_cord'] = htmlspecialchars($_POST['Fcertification_cord']);
		// 認証実行
		if($_POST['certification_cord'] != $_POST['Fcertification_cord'] || empty($_POST['Fcertification_cord'])) {
			mes('<b>認証コード</b>が不正です。', '記入ミス', 'back_reg');
		}
	}
}

// 自動リンクの生成(auto_link)
function auto_link($url) {
	return (preg_replace("/([^=^\"]|^)(http\:[\w\.\~\-\/\?\&\+\=\:\@\%\;\#]+)/","$1<a href=\"$2\">$2</a>", $url));
}
?>