<?php
//----------------------------------------------------------------------------
// (f1)登録するカテゴリを表示(print_category)
function print_category($category = '', $smartphone_flag = false) {
	global $cfg, $cfg_reg, $db, $Form;
	if(!isset($Form['changer'])) {
		$Form['changer'] = '';
	}

	$selected_categories = array();
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($Form["Fkt{$i}"]) and $Form["Fkt{$i}"]){
			$selected_categories[] = $Form["Fkt{$i}"];
		}
	}
	
	//Get DB Categories
	$query = 'SELECT path, regist FROM {prefix}category ORDER BY path';
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
        if($smartphone_flag){$writeStr .=   '<div data-role="fieldcontain"><label for="Fkt'.$category_no.'" class="select"></label>';}
		$writeStr .=  "<select name=\"{$selecter_name}\" size=\"7\"";
			if($smartphone_flag) $writeStr .=  " id=\"Fkt{$category_no}\" data-native-menu=\"false\"";
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
		
		if(!$smartphone_flag){
			if(count($selected_categories) > 0){$select = "";}
			$writeStr .=  "<option value=\"\" {$select} >--指定しない--</option>";
		}else{$writeStr .= "<option value=\"\">カテゴリ</option>\n";}
		
		//DB - Print Categories
		if(is_array($categories_data)){
			$selected_count = 0;
			foreach($categories_data as $row){
				$selected_flag = "";
				if($Form['changer'] == 'admin' || !$row['regist']){
					$pm = preg_replace("/\//","\/",$row['path']);
					if($match = preg_grep("/^" . $pm . "$/",$selected_categories)){
						$selected_count++;
						$selected_flag = " selected";
						if($selecter_mode != "multiple" and $selected_count > 1){$selected_flag = "";}
						else{unset($selected_categories[key($match)]);}
					}
					$writeStr .=  "<option value=\"" . $row['path'] . "\"" . $selected_flag .">" . full_category($row['path']) . "</option>\n";
				}
			}
		}

		$writeStr .=  '</select>';
        if($smartphone_flag) $writeStr .=  '</div> ';
        $writeStr .=  '<br><br>'."\n";
	}
	$writeStr .=  '</ul><br>'."\n";
    if($smartphone_flag == true) {
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
/*
function mes($mes, $title = '', $arg3 = '') {
	global $cfg, $cfg_reg, $db;

    $back_url = "\n";

	if (!$title) {
		$title = 'メッセージ画面';
	}
	if ($arg3 == 'java' || ($arg3 == 'back_reg' && $Form['mode'] == 'act_mente')) {
		$back_url = '<form>'
		          . '<input type="button" value="    戻る    " onClick="history.back()">'
				  . '</form>' . "\n";
	} elseif($arg3 == 'env') {
		$back_url = '【<a href="' . $_SERVER['HTTP_REFERER'] . '">戻る</a>】';
	} elseif($arg3 == 'kanri') {
		$back_url  = '<form action="' . $cfg['admin'] . '" method="post">' . "\n";
		$back_url .= '<input type="hidden" name="mode" value="kanri">' . "\n";
		$back_url .= '<input type="hidden" name="pass" value="' . $Form["pass"] . '">' . "\n";
		$back_url .= '<input type="submit" value="管理室へ"></form>' . "\n";
	} elseif (!$arg3) {
		$back_url = '';
	} elseif ($arg3 == 'back_reg') {
		$Form['Fsyoukai'] = str_replace('<br>', "\n", $Form['Fsyoukai']);
		$back_url .= '<form action="regist_ys.php" method="post">' . "\n";
        if ($Form['changer'] == 'admin') {
            $back_url .= '    <input type="hidden" name="mode" value="new_dairi">' . "\n";
        }
		if(isset($Form['pass'])) {
			$back_url .= '    <input type="hidden" name="pass" value="' . $Form["pass"] . '">' . "\n";
		} else {
			$back_url .= '    <input type="hidden" name="pass" value="">' . "\n";
		}
		$back_url .= <<<EOM
    <input type="hidden" name="changer" value="{$Form['changer']}">
    <input type="hidden" name="Fname" value="{$Form['Fname']}">
    <input type="hidden" name="Femail" value="{$Form['Femail']}">
    <input type="hidden" name="Fpass" value="{$Form['Fpass']}">
    <input type="hidden" name="Fpass2" value="{$Form['Fpass2']}">
    <input type="hidden" name="Furl" value="{$Form['Furl']}">
    <input type="hidden" name="Fbana_url" value="{$Form['Fbana_url']}">
    <input type="hidden" name="Ftitle" value="{$Form['Ftitle']}">
    <input type="hidden" name="Fsyoukai" value="{$Form['Fsyoukai']}">
EOM;
		if (isset($Form['Fkanricom'])) {
			$back_url .= '<input type="hidden" name="Fkanricom" value="' . $Form['Fkanricom'] . '">' . "\n";
		} else {
			$back_url .= '<input type="hidden" name="Fkanricom" value="">';
		}
        for ($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
			if(isset($Form['Fkt'.$i]) and !is_array($Form['Fkt'.$i])){
				$back_url .= '<input type="hidden" name="Fkt' . $i . '" value="' . $Form['Fkt'.$i] . '">' . "\n";
			}
        }
		$back_url .= <<<EOM
    <input type="hidden" name="Fkey" value="{$Form['Fkey']}">
    <input type="hidden" name="Fadd_kt" value="{$Form['Fadd_kt']}">
    <input type="hidden" name="Fto_admin" value="{$Form['Fto_admin']}">
EOM;
		if (isset($Form['Fsougo'])) {
			$back_url .= '    <input type="hidden" name="Fsougo" value="' . $Form['Fsougo'] . '">' . "\n";
		} else {
			$back_url .= '    <input type="hidden" name="Fsougo" value="">' . "\n";
		}
		$back_url .= '    <input type="submit" value="登録画面に戻る">' . "\n";
	} else {
		$back_url = '【<a href="' . $arg3 . '">戻る</a>】' . "\n";
	}
	require $path_set['template']['root'] . '/mes.html';
	exit;
}
*/

// (f3)入力内容のチェック(check)
function check() {
	// 禁止ワードのチェック
	global $cfg, $cfg_reg, $db, $Form;
	
	if(!isset($Form['changer'])) {
		$Form['changer'] = '';
	}

	if($cfg_reg['kt_no_word']) {
		// ワードチェック対象の項目
		$check_str = implode(' ', array($Form['Fname'], $Form['Femail'], $Form['Furl'], $Form['Fbana_url'], $Form['Ftitle'], $Form['Fsyoukai'], $Form['Fkey']));
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
	if($cfg_reg["Fname"] && !$Form["Fname"]) {
		mes("<b>お名前</b>は<font color=red>記入必須項目</font>です", "記入ミス", "back_reg");
	}
	$num = mb_strwidth($Form['Fname']) - $cfg_reg['Mname'] * 2;
	if($num > 0) {
		mes("<b>お名前</b>は全角<b>{$cfg_reg['Mname']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	$Form['Fname'] = str_replace(array("\r", "\n"), "", $Form['Fname']);
	// メールアドレス
	if($cfg_reg['Femail'] && !$Form['Femail']) {
		mes("<b>メールアドレス</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	} elseif(strlen($Form['Femail']) - $cfg_reg['Memail'] > 0) {
		$num = strlen($Form['Femail']) - $cfg_reg['Memail'];
		mes("<b>メールアドレス</b>は半角<b>{$cfg_reg['Memail']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	} elseif($cfg_reg['Femail'] && !preg_match("/(.+)\@(.+)\.(.+)/", $Form['Femail'])) {
		mes("<b>メールアドレス</b>の入力が正しくありません", "記入ミス", "back_reg");
	}
	$Form['Femail'] = str_replace(array("\r", "\n"), "", $Form['Femail']);
	// パスワード
	if($Form['mode'] != "act_mente"){
		if(!$Form['Fpass']) {
			mes("<b>パスワード</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		} elseif(preg_match("/\W/", $Form['Fpass'])) {
			$Form['Fpass'] = "";
			$Form['Fpass2'] = "";
			mes("<b>パスワード</b>には全角文字は使用できません", "入力ミス", "back_reg");
		} elseif(strlen($Form['Fpass']) > 8) {
			$num = strlen($Form['Fpass']) - 8;
			mes("<b>パスワード</b>は半角英数<b>8</b>文字以内でご記入ください", "文字数オーバー({$num}文字分)", "back_reg");
		} elseif($Form['Fpass'] != $Form['Fpass2']) {
			mes("2回の<b>パスワード</b>入力が一致しませんでした", "入力ミス", "back_reg");
		}
		$Form['Fpass'] = str_replace(array("\r", "\n"), "", $Form['Fpass']);
	}
	// ホームページアドレス(2重登録チェックは別のところに記述)
	if($Form['Furl'] == "http://") { $Form['Furl']=""; }
	if($cfg_reg['Furl'] && !$Form['Furl']) {
		mes("<b>ホームページアドレス</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	} elseif(strlen($Form['Furl']) - $cfg_reg['Murl'] > 0) {
		$num = strlen($Form['Furl']) - $cfg_reg['Murl'];
		mes("<b>ホームページアドレス</b>は半角<b>{$cfg_reg['Murl']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	} elseif($Form['Furl'] && !preg_match("/^https?:\/\/.+\..+/", $Form['Furl'])) {
		mes("<b>ホームページアドレス</b>の入力が正しくありません", "記入ミス", "back_reg");
	}
	$Form['Furl'] = str_replace(array("\r", "\n"), "", $Form['Furl']);
	// タイトルバナーのURL
	if ($cfg_reg['bana_url']) {
		if (!@$Form['Fbana_url']) { $Form['Fbana_url'] = ""; }
		if ($Form['Fbana_url'] == "http://") { $Form['Fbana_url'] = ""; }
		if ($cfg_reg['Fbana_url'] && !$Form['Fbana_url']) {
            $msg = '<b>タイトルバナーのURL</b>は<font color="#FF0000">記入必須項目</font>です。';
            mes($msg, "記入ミス", "back_reg");
        } elseif (strlen($Form['Fbana_url']) - $cfg_reg['Mbana_url'] > 0) {
			$num = strlen($Form['Fbana_url']) - $cfg_reg['Mbana_url'];
            $msg = '<b>タイトルバナーのURL</b>は半角<b>' . $cfg_reg['Mbana_url'] . '</b>文字以内でご記入ください。';
            mes($msg, "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
        } elseif ($Form['Fbana_url'] && !preg_match("/^https?:\/\/.+\..+\.(gif|jpg|jpeg|png)$/", $Form['Fbana_url'])) {
            $msg = '<b>タイトルバナーのURL</b>の入力が正しくありません。';
            mes($msg, "記入ミス", "back_reg");
        } elseif ($Form['Fbana_url']) {
            $FbanaInfo = @getimagesize($Form['Fbana_url']);
            if (!$FbanaInfo) {
                $msg = 'バナー画像を取得できませんでした。<br />'
                     . 'バナーURLに入力ミスがあるか、又はバナーを設置しているサーバーが外部からの参照を禁止している可能性があります。';
                mes($msg, '記入ミス', 'back_reg');
			}
		}
	} else {
		$Form['Fbana_url'] = "";
	}
	$Form['Fbana_url'] = str_replace(array("\r", "\n"), "", $Form['Fbana_url']);
	// ホームページのタイトル
	if($cfg_reg['Ftitle'] && !$Form['Ftitle']) {
		mes("<b>ホームページのタイトル</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	}
	if(mb_strwidth($Form['Ftitle']) - ($cfg_reg['Mtitle'] * 2) > 0) {
		$num = mb_strwidth($Form['Ftitle']) - ($cfg_reg['Mtitle'] * 2);
		mes("<b>ホームページのタイトル</b>は全角<b>{$cfg_reg['Mtitle']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	$Form['Ftitle'] = str_replace(array("\r", "\n"), "", $Form['Ftitle']);
	// ホームページの紹介文
	if($cfg_reg['Fsyoukai'] && !$Form['Fsyoukai']) {
		mes("<b>ホームページの紹介文</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
	}
	if(mb_strwidth($Form['Fsyoukai']) - ($cfg_reg['Msyoukai'] * 2) > 0) {
		$num = mb_strwidth($Form['Fsyoukai']) - ($cfg_reg['Msyoukai'] * 2);
		mes("<b>ホームページの紹介文</b>は全角<b>{$cfg_reg['Msyoukai']}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
	}
	if(!$cfg['syoukai_br']) {
		$Form['Fsyoukai'] = str_replace(array("\r", "\n"), "", $Form['Fsyoukai']);
	} else {
		$Form['Fsyoukai'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Form['Fsyoukai']);
	}
	// 管理人コメント
	if(@$Form['Fkanricom']) {
		$Form['Fkanricom'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Form['Fkanricom']);
	}
	
	// カテゴリ
	// form - 登録するカテゴリ - 情報整形
	$regist_categories = array();
	$post_category_count = 0;
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($Form['Fkt'.$i])){
			if($Form['Fkt'.$i] != ""){
				$post_category_count++;
				if(is_array($Form['Fkt'.$i])){
					foreach($Form['Fkt'.$i] as $fkt_line){
						if($fkt_line){
							$regist_categories['Fkt'.$post_category_count] = $fkt_line;
							$post_category_count++;
						}
					}
				}elseif(!is_array($Form['Fkt'.$i]) and $Form['Fkt'.$i] != ""){
					$regist_categories['Fkt'.$post_category_count] = $Form['Fkt'.$i];
				}
			}
			unset($Form['Fkt'.$i]);			
		}
	}
	$Form = array_merge($Form,$regist_categories);
	
	$kt_fl = array();
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++){
		if(isset($Form["Fkt{$i}"]) and $Form["Fkt{$i}"]){
			$query = "SELECT id, regist FROM {prefix}category WHERE path='". $Form["Fkt{$i}"] . "'";
			$regist = $db->single_assoc($query) or $db->error("Query failed $query".__FILE__.__LINE__);
			if(isset($Form["Fkt{$i}"])) {
				$Form["Fkt{$i}"] = str_replace(array("\r", "\n"), "", $Form["Fkt{$i}"]);
			}
			if(isset($kt_fl[$Form["Fkt{$i}"]])) {
				$Form["Fkt{$i}"] = '';
			} elseif($regist['id']) {
				$kt_fl[$Form["Fkt{$i}"]] = 1;
			} else {
				$Form["Fkt{$i}"] = '';
			}

			// 禁止カテゴリに登録しようとした場合
			if($Form['changer'] != "admin" && $regist['regist']) {
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
	if ($cfg_reg['Fkey'] && !$Form['Fkey']) {
        $msg = '<b>キーワード</b>は<font color="#FF0000">記入必須項目</font>です。';
        mes($msg, "記入ミス", "back_reg");
	}
	if (mb_strwidth($Form['Fkey']) - ($cfg_reg['Mkey'] * 2) > 0) {
		$num = mb_strwidth($Form['Fkey']) - ($cfg_reg['Mkey'] * 2);
        $msg      = '<b>キーワード</b>は全角<b>' . $cfg_reg['Mkey'] . '</b>文字以内でご記入ください。';
        $msgTitle = '文字数オーバー(半角換算で' . $num . '文字分)';
        mes($msg, $msgTitle, 'back_reg');
	}
    $targetValues = array('・', '、', '　', ',');
    $Form['Fkey'] = str_replace($targetValues, ' ', $Form['Fkey']);
    $Form['Fkey'] = preg_replace('/\r|\r\n|\n/', ' ', $Form['Fkey']);
    $Form['Fkey'] = preg_replace('/\s\s+/', ' ', $Form['Fkey']);
    $Form['Fkey'] = trim($Form['Fkey']);
	// 追加して欲しいカテゴリ
	if($Form['mode'] != "act_mente" && $Form['changer'] != "admin") {
		if($cfg_reg['Fadd_kt'] && !$Form['Fadd_kt']) {
			mes("<b>追加して欲しいカテゴリ</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		}
		if(mb_strwidth($Form['Fadd_kt']) - ($cfg_reg['Madd_kt'] * 2) > 0) {
			$num = mb_strwidth($Form['Fadd_kt']) - ($cfg_reg['Madd_kt'] * 2);
			mes("<b>追加して欲しいカテゴリ</b>は全角<b>{$cfg_reg["Madd_kt"]}</b>文字以内でご記入ください", "文字数オーバー(半角換算で{$num}文字分)", "back_reg");
		}
		$Form['Fadd_kt'] = str_replace(array("\r\n", "\r", "\n"), "<br>", $Form['Fadd_kt']);
	}
	// 相互リンクの有無
	$sougo[1] = "する";
	$sougo[0] = "しない";
	if(!isset($Form['Fsougo'])) {
		$Form['Fsougo'] = 0;
	}
	if($Form['Fsougo'] != '1') {
		$Form['Fsougo'] = 0;
	}
	// 管理人へのメッセージ
	if($Form['mode'] != "act_mente" && $Form['changer'] != "admin") {
		if($cfg_reg['Fto_admin'] && !$Form['Fto_admin']) {
			mes("<b>管理人へのメッセージ</b>は<font color=\"#FF0000\">記入必須項目</font>です", "記入ミス", "back_reg");
		}
		if(mb_strwidth($Form['Fto_admin']) - ($cfg_reg['Mto_admin'] * 2) > 0) {
			$num = mb_strwidth($Form['Fto_admin']) - ($cfg_reg['Mto_admin'] * 2);
			mes('<b>管理人へのメッセージ</b>は全角<b>'.$cfg_reg['Mto_admin'].'</b>文字以内でご記入ください', '文字数オーバー(半角換算で'.$num.'文字分)', 'back_reg');
		}
		if(!$cfg['syoukai_br']) {
			$Form['Fto_admin'] = str_replace(array("\r", "\n"), '', $Form['Fto_admin']);
		} else {
			$Form['Fto_admin'] = str_replace(array("\r\n", "\r", "\n"), '<br>', $Form['Fto_admin']);
		}
	}
}

// (f4)カテゴリを表示1(preview_category1)
function preview_category1() {
	global $cfg_reg, $Form;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		if(isset($Form['Fkt'.$kt_no])){
			$value = $Form['Fkt'.$kt_no];
			echo '<input type="hidden" name="Fkt'.$kt_no.'" value="'.$value.'">'."\n";
		}
	}
}

// (f5)カテゴリを表示2(preview_category2)
function preview_category2() {
	global $cfg_reg, $Form;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		if(isset($Form['Fkt'.$kt_no])){
			$value = $Form['Fkt'.$kt_no];
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
	// $Form['changer'] => 変更者(なし,admin)
	// ※登録内容変更の場合の変更前データは「$pre_log」に格納されている
	global $pre_log, $cfg, $cfg_reg, $Form;
	// 登録No(データID)(0)
	$log_data[0] = $id;
	// タイトル(1)
	$log_data[1] = $Form['Ftitle'];
	// URL(2)
	$log_data[2] = $Form['Furl'];
	// マークデータ(3)
	if($Form['changer'] == 'admin') { // 変更者が管理人
		$Form['Fmark'] = '';
		for($i = 1; $i <= 10; $i++) { // ←マーク数を増やすときは修正
			if(isset($Form['Fmark'.$i])) {
				$Form['Fmark'] .= '1_';
			} else {
				$Form['Fmark'] .= '0_';
			}
		}
		$Form['Fmark'] = substr($Form['Fmark'], 0, -1);
		$log_data[3] = $Form['Fmark'];
	} elseif($Form['mode'] == 'act_mente'){ // 登録者の変更
		$log_data[3] = $pre_log[3];
	} else { // 登録者の新規登録
		$log_data[3] = '0_0_0_0_0_0_0_0_0_0';
	}
	// 更新日(4)
	// 日時の取得
	$log_data[4] = get_time(0, 1);
	// パスワード(5)
	if(isset($Form['Fpass'])) {
		$log_data[5] = crypt($Form['Fpass'], 'ys');
	} else {
		$log_data[5] = '';
	}
	// 紹介文(6)
	$log_data[6] = $Form['Fsyoukai'];
	// 管理人コメント(7)
	if($Form['changer'] == 'admin'){ // 変更者が管理人
		$log_data[7] = $Form['Fkanricom'];
	} else { // 登録者の変更
		if(isset($pre_log[7])){$log_data[7] = $pre_log[7];}
		else{$log_data[7] = "";}
	}
	// お名前(8)
	$log_data[8] = $Form['Fname'];
	// E-mail(9)
	$log_data[9] = $Form['Femail'];
	// カテゴリ(10)
	if($cfg['user_change_kt'] && $Form['mode'] == 'act_mente' && $Form['changer'] != 'admin') { // 登録者の変更でカテゴリ変更禁止の場合
		$i = 0;
		$category = explode('&', $pre_log[10]);
		$log_data[10] = $pre_log[10];
		foreach($category as $tmp) {
			$Form['Fkt'.$i] = $tmp;
			$i++;
		}
	} else { // その他の場合
		$log_data[10] = '&';
		for($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
			if(isset($Form['Fkt'.$i])){$log_data[10] .= $Form['Fkt'.$i] . '&';}
		}
	}
	// time形式(11)新規or更新(13)
	$times = time();
	$log_data[11] = $times;
	if($Form['mode'] == 'act_mente') { // 内容変更時
		if(!$pre_log[13] && $times - $pre_log[11] < $cfg['new_time'] * 86400) {
			$log_data[13] = '0';
		} else {
			$log_data[13] = '1';
		}
	} else { // 新規登録時
		$log_data[13] = '0';
	}
	// バナーURL(12)
	$log_data[12] = $Form['Fbana_url'];
	// 最終アクセスIP(14)
	$log_data[14] = $_SERVER['REMOTE_ADDR'];
	// キーワード(15)
	$log_data[15] = $Form['Fkey'];
	// 仮登録モードの場合の設定
	if($cfg['user_check'] && $Form['changer'] != 'admin' && $Form['mode'] == 'act_regist') {
		$log_data[7] = implode('<1>', array($Form['Fsougo'], $Form['Fadd_kt'], $Form['Fto_admin']));
	}
	if($Form['changer'] == 'admin' && $pre_log) {
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
	global $cfg, $db, $Form;
	$i = 0;
	$log_data = array();
	$query = 'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM {prefix}log WHERE url=\''.$Form['Furl'].'\' LIMIT 1';
	$log_data = $db->single_num($query);
	if($log_data) {
		if($Form['Furl'] == $log_data[2]) {
			$i++;
			$pre_title = $log_data[1];
		}
		if(isset($Form['id'])) {
			if($Form['id'] == $log_data[0]) {
				$pre_log = $log_data;
			}
		}
		if($fl <= $i) {
			mes('そのURLはすでに登録されています<br><br>'.$log_data[1].' :<br>'.$log_data[2], '2重登録エラー', 'java');
		}
		if($fl == '2' && $i == '1' && $pre_log[2] != $Form['Furl']) {
			mes('そのURLはすでに登録されています<br><br>'.$pre_title.' :<br>'.$Form['Furl'], '2重登録エラー', 'java');
		}
	}
	if($Form['changer'] != 'admin' && $cfg['user_check'] && $Form['mode'] == 'act_regist' && $fl == 1) {
		// 仮登録モードでユーザの新規登録時
		$query = 'SELECT title FROM {prefix}log_temp WHERE url=\''.$Form['Furl'].'\' LIMIT 1';
		$log_data = $db->single_num($query);
		if($log_data) {
			mes('そのURLは現在登録申請中です<br><br>'.$log_data[0].' :<br>'.$Form['Furl'], '2重登録エラー', 'java');
		}
	}
}

// (f8)登録結果画面出力(PRend)
function PRend() {
	global $cfg,$script;
	require $script['lib']['regist_new_end'];
}

// (f9)マークForm管理者画面出力(PR_mark)
function PR_mark($data = '') {
	global $cfg, $Form;
	if ($Form['changer'] == 'admin') {
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
	global $Form;

	// 認証前の準備
	if(!isset($Form['changer'])) {
		$Form['changer'] = '';
	}
	if($Form['changer'] != 'admin') {
		$Form['certification_cord'] = htmlspecialchars($Form['certification_cord']);
		$Form['Fcertification_cord'] = htmlspecialchars($Form['Fcertification_cord']);
		// 認証実行
		if($Form['certification_cord'] != $Form['Fcertification_cord'] || empty($Form['Fcertification_cord'])) {
			mes('<b>認証コード</b>が不正です。', '記入ミス', 'back_reg');
		}
	}
}

// 自動リンクの生成(auto_link)
function auto_link($url) {
	return (preg_replace("/([^=^\"]|^)(http\:[\w\.\~\-\/\?\&\+\=\:\@\%\;\#]+)/","$1<a href=\"$2\">$2</a>", $url));
}
?>