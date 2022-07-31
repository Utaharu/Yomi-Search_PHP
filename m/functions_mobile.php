<?php
//mobile用著作権表示
function mcr() {
    global $cfg;
    $w = '<div style="background-color:'.HR_COLOR.';">
    <img src="./img/spacer.gif" width="1" height="1" /><br /></div>
    <div style="text-align:center; background-color:'.FOOTER_BACK_COLOR.'; color:'.FOOTER_STR_COLOR.'; font-size:x-small;" align="center">
    <a href="http://www.nkbt.net/yomi/" target="_blank"><span style="color:'.FOOTER_STR_COLOR.';">&copy;'.$cfg['ver'].'</span></a></div>
    <div style="background-color:'.HR_COLOR.';">
    <img src="./img/spacer.gif" width="1" height="1" /><br /></div>';
    
    echo $w;
}


//外部検索エンジンへのリンク一覧を表示(&PR_mata_page)
function PR_meta_page($location_list) {
    $w = '<div style="font-size:x-small;">▼該当するﾃﾞｰﾀは見つかりませんでした｡<br />下記の検索ｴﾝｼﾞﾝで再検索!!</div>';
	foreach($location_list as $list) {
		list($Dengine, $Durl) = explode('<>', $list);
		$w.= '<a href="'.$Durl.'"><font size="+1">'.$Dengine.'</font></a><br />';
	}
    echo $w;
    return true;
}



//キーワードをデータベースに記録(&set_word)
function set_word($db, $keyword) {
    $time = time();
    if(strlen($keyword) < 50) {
        $keyword = str_replace('　', ' ', $keyword);
        $keyword = mb_strtolower($keyword, 'UTF-8');
        $keyword = $db->escape_string($keyword);
        $keywords = explode(' ', $keyword);
        if(count($keywords) > 0) {
            foreach($keywords as $i) {
                if($i && $i != 'and' && $i != 'or' && $i != 'not') {
                    $i = str_replace("\n", '', $i);
                    $query = 'SELECT word FROM '.$db->db_pre.'key WHERE word=\''.$i.'\' AND ip=\''.$_SERVER['REMOTE_ADDR'].'\' AND time > '.($time - 24 * 3600);
                    $tmp = $db->single_num($query);
                    if(!$tmp) {
                        $query = 'INSERT INTO '.$db->db_pre.'key (word, time, ip) VALUES (\''.$i.'\', \''.$time.'\', \''.$_SERVER['REMOTE_ADDR'].'\')';
                        $db->query($query);
                    }
                }
            }
        }
    }
}
//)


/**
* 大カテゴリを配列に入れて返します
* 
* @param  object $db dbobject
* @return array  カテゴリ配列
*/
function getBigCategory($db) {
    $query = 'SELECT title, path FROM '.$db->db_pre.'category WHERE up_id=0 ORDER BY path;';
    return $db->rowset_assoc($query);
}


/**
* サブカテゴリを配列に入れて返します
* 
* @param  object $db   dbobject
* @param  string $path 大カテゴリ
* @return array  カテゴリ配列
*/
function getSubCategory($db, $top_path) {
    $query = 'SELECT title, path, top_list FROM '.$db->db_pre.'category WHERE path LIKE \''.$top_path.'_%\' ORDER BY path;';
    return  $db->rowset_assoc($query);
}


//その他のカテゴリ表示(other_category)
function other_category($path = '') {
	global $cfg, $db;
	$row_num = 4;	// 表示列数
	$td_flag = 0;
	$start   = 1;
        $w = '';
	$query  = 'SELECT title, path FROM '.$db->db_pre.'category WHERE etc_list=\'1\'';
	$rowset = $db->rowset_assoc($query);
	$w = '<table width="100%" class="table_other_category">'."\n";
	$w .= '<tr>'."\n";
	$w .= '<td><a name="other"></a>【その他のカテゴリ】</td>'."\n";
	$w .= '</tr>'."\n";
	$w .= '</table>'."\n";
	foreach ($rowset as $row) {
		if ($start <= 1) {
			$w .= '<table width="100%" align="center" class="table_other_category2">'."\n";
			$start++;
		}
		if ($td_flag <= 0) {
			$w .= '<tr>'."\n";
		}
		$w .= '<td><img src="'.$cfg['img_path_url'].'folder.gif" border="0" alt="'.$row['title'].'" title="'.$row['title'].'">&nbsp;<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'</a></td>'."\n";
		$td_flag++;
		if ($td_flag >= $row_num) {
        		$w .= '</tr>'."\n";
			$td_flag = 0;
		}
	}
	if ($td_flag > 0) {
		$w .= '</tr>'."\n";
	}
	if ($start > 1) {
		$w .= '</table>'."\n";
	}
	$w .= '<hr align="center">';
        echo $w;
        return true;
}

// サブカテゴリ以下の総登録数(subcategory)
function subcategory($path) {
	global $db;
	$count = 0;
	$check = 0;
	$query = 'SELECT COUNT(id) FROM '.$db->db_pre.'log WHERE category LIKE \'%&'.$path.'%\'';
	$num = $db->single_num($query);
	$query = 'SELECT id FROM '.$db->db_pre.'category WHERE path LIKE \''.$path.'_%\'';
	$sub = $db->single_num($query);
	return (array($num[0], $sub[0]));
}

// 直下カテゴリを表示(print_subcategory)
function print_subcategory($category, $num, $column) {
	global $cfg, $db;
        $db_pre = $db->db_pre;
        $w = '';
	$w= '<table width="100%" class="table_sub_category">'."\n";
	$w .= '<tr>'."\n";
	$w .= '<td><a name="other"></a>【直下カテゴリ】</td>'."\n";
	$w .= '</tr>'."\n";
	$w .= '</table>'."\n";
	$td_flag=0;
	$tr_flag=1;
	$query = 'SELECT id FROM '.$db_pre.'category WHERE path=\''.$category.'\'';
	$id = $db->single_num($query);
	$query = 'SELECT title, path FROM '.$db_pre.'category WHERE up_id=\''.$id[0].'\' ORDER BY path';
	$rowset = $db->rowset_assoc($query);

        if($rowset[0] != "") {
            $w .= "\n".'<table width="100%" class="table_sub_category2">';
            foreach ($rowset as $row) {
                    list($count, $check) = subcategory($row['path']);
                    if($tr_flag == 0) {
                            $w .= '</td></tr>';
                            $tr_flag = 1;
                    }
                    if($tr_flag == 1) {
                            $w .= "\n".'<tr><td>';
                            $td_flag = 1;
                    } else {
                            $w .=  "</td>\n<td>";
                    }
                    $w .= '<img src="'.$cfg['img_path_url'].'folder.gif" border="0" title="'.$row['title'].'" alt="'.$row['title'].'">&nbsp;<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'];
                    if($check) {
                            $w .= '*';
                    }
                    $w .= '</a>';
                    if($num) {
                            $w .=' ('.$count.')';
                    }
                    if($tr_flag < $column) {
                            $tr_flag++;
                    } else {
                            $tr_flag = 0;
                    }
            }
            $query = 'SELECT title, path FROM '.$db_pre.'category WHERE reffer LIKE \'%&'.$id[0].'&%\' ORDER BY path';
            $rowset = $db->rowset_assoc($query);
            foreach($rowset as $row) {
                    $id = substr($row['path'], 0, -1);
                    list($count, $check) = subcategory($id);
                    if($tr_flag == 0) {
                            $w .=  '</td></tr>';
                            $tr_flag = 1;
                    }
                    if($tr_flag == 1) {
                            $w .=  "\n".'<tr><td>';
                            $td_flag = 1;
                    } else {
                            $w .=  "</td>\n<td>";
                    }
                    $w .=  '<img src="'.$cfg['img_path_url'].'folder.gif" border="0" title="'.$row['title'].'" alt="'.$row['title'].'">&nbsp;<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'@</a>';
                    if($num) {
                            $w .= ' ('.$count.')';
                    }
                    if($tr_flag < $column) {
                            $tr_flag++;
                    } else {
                            $tr_flag = 0;
                    }
            }
            $w .=  '</td></tr>'."\n";
            $w .=  '</table>'."\n";
        }
        echo $w;
        return true;
}

// 目次作成(mokuji)
// page, ログ数, 表示数, "&mode={$_GET["mode"]}&path={$_GET["path"]}", url
// 1 - 10 ( 133 件中 )　 [ / 1 2  3  4  5  6  7  8  9  10  => / 次ページ→ ]
function mokuji($arg) {
	global $cfg, $up_id;
	$url = $arg[4];
	$bf_page = $arg[0] - 1;
	$af_page = $arg[0] + 1;
	$bf_url = $url.'?page=';
	$md_url = '';
	$af_url = $arg[3];
	$end_no = $arg[0] * $arg[2];
	$st_no = $end_no - $arg[2] +1;
	if($end_no >= $arg[1]) {
		$end_no=$arg[1];
	}
	$max_page = (int)($arg[1] / $arg[2]);
	if($arg[1] % $arg[2]) {
		$max_page++;
	}
	$mokuji  ='　 '.$st_no.' - '.$end_no.' ( '.$arg[1].' 件中 )　 ';
	if($arg[1] > $arg[2]) { //目次作成
		$mokuji .= '[ ';
		if($arg[0] > 1) {
			$mokuji .= '<a href="'.$bf_url.$md_url.$bf_page.$af_url.'">←前ページ</a> ';
		}
		$mokuji .= '/ ';
		//make <=
		$max_page_f = (int)($max_page / 10);
		if($max_page % 10) {
			$max_page_a = 1;
		} else {
			$max_page_a = 0;
		}
		$pre_page_f = (int)($arg[0] / 10);
		if($arg[0] % 10) {
			$pre_page_a = 1;
		} else {
			$pre_page_a = 0;
		}
		if($max_page > 10 && $arg[0] > 10 && $pre_page_f > 0) {
			$md_url = '';
			$j = $pre_page_f * 10 - 19 + $pre_page_a * 10;
			$mokuji .= '<a href="'.$bf_url.$md_url.$j.$af_url.'">&lt;=</a> ';
		}
		if($pre_page_a) {
			$hyouji_page_st = $pre_page_f * 10 + 1;
		} else {
			$hyouji_page_st = $pre_page_f * 10 - 9;
		}
		$hyouji_page_end = $hyouji_page_st + 9;
		for($i=1; $i <= $max_page; $i++) {
			if($hyouji_page_end < $i) {
				break;
			}
			if($hyouji_page_st <= $i) {
				if($i != $arg[0]) {
					$md_url = '';
					$j = $i;
					$mokuji .= '<a href="'.$bf_url.$md_url.$j.$af_url.'">'.$i.'</a>&nbsp;&nbsp;';
				} else {
					$mokuji .= '<b>'.$i.'</b>&nbsp;&nbsp;';
				}
			}
		}
		//make =>
		if($max_page_f - ($pre_page_f + $pre_page_a - 1) != 1 || $max_page_a) {
			if($max_page > 10 && $max_page > $arg[0] && $max_page_f > ($pre_page_f + $pre_page_a - 1)) {
				$md_url = '';
				$j = $pre_page_f * 10 + 1 + $pre_page_a * 10;
				$mokuji .= '<a href="'.$bf_url.$md_url.$j.$af_url.'">=&gt;</a> ';
			}
		}
		$mokuji .= '/ ';
		if($arg[0] < $max_page) {
			if($cfg['html'] && isset($up_id[$_GET['id']])) {
				$md_url = $_GET['id'] . 'p';
			}
			$mokuji .= '<a href="'.$bf_url.$md_url.$af_page.$af_url.'">次ページ→</a> ';
		}
		$mokuji .=']';
	}
	return $mokuji;
}

// アイコン付加(put_icon)
function put_icon() {
	global $log_data, $cfg;
	$times = time();
	$mark = explode('_', $log_data['mark']);
        $writeStr = '';
	if($log_data['renew'] == 0 && ($times - $log_data['stamp']) < 86400 * $cfg['new_time']) {
		//新着マーク
		$writeStr = '<img src="'.$cfg['img_path_url'].'new.gif" alt="'.$cfg['name_new'].'\" title="'.$cfg['name_new'].'" align="absbottom"> ';
	} elseif(($times - $log_data["stamp"]) < 86400 * $cfg["new_time"]) {
		//更新マーク
		$writeStr = '<img src="'.$cfg['img_path_url'].'renew.gif" alt="'.$cfg['name_renew'].'\" title="'.$cfg['name_renew'].'" align="absbottom"> ';
        }

        //m1マーク(デフォルト：おすすめ)
	//m2マーク(デフォルト：相互リンク)
        for($z=0; $z<10; $z++) {
            if($mark[$z]) {
                $zz=$z+1;
                $n = 'name_'.$zz;
		$writeStr .= '<img src="'.$cfg['img_path_url'].'m'.$zz.'.gif" alt="'.$cfg[$n].'" title="'.$cfg[$n].'" align="absbottom"> ';
            }
        }
        echo $writeStr;
        return;
}

// フルカテゴリ名を整形(full_category)
function full_category($id){
	global $db;
	$navi_id = explode('/', substr($id, 0, -1));
	$path = '';
	$navi = '';
	foreach($navi_id as $tmp) {
		$path .= $tmp . '/';
		$query = 'SELECT title FROM '.$db->db_pre.'category WHERE path=\''.$path.'\'';
		$row = $db->single_assoc($query);
		$navi .= $row['title'] . ':';
	}
	return substr($navi, 0, -1);
}

// ナビゲーションバーを表示(navi_bar)
function navi_bar($id) {
	global $cfg, $db;
	$navi_id = explode('/', substr($id, 0, -1));
	array_pop($navi_id);
	$path = '';
	$navi = '';
	foreach($navi_id as $tmp) {
		$path .= $tmp . '/';
		$query = 'SELECT title FROM '.$db->db_pre.'category WHERE path=\''.$path.'\'';
		$row = $db->single_assoc($query);
		$navi .= '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$path.'">'.$row['title'].'</a> &gt; ';
	}
	return $navi;
}

// Location 処理(&location)
function location($url) {
	global $cfg;
	if(!$url) {
		mes('リンク先が見つかりません','エラー','java');
	}
	if($cfg['location']) {
		header('Location: '.$url);
                exit;
	} else {
            echo '<html><head><title></title><meta http-equiv="refresh" content="0;url='.$url.'"></head><body></body></html>'."\n";
            exit;
	}
}

function get_time($time = '', $time_fl = '') {
	if(!$time) {
		$time = time();
	}
	if(!$time_fl) {
		return date('Y/m/d', $time);
	} else {
		return date('Y/m/d(D) H:i', $time);
	}
}

// パスワードチェック(pass_check)
function pass_check(){
	//IP/ホスト名の制限がある場合
	global $cfg;
	if($cfg['login_ip']) {
		$ip_list = array();
		$fl = 0;
		if(!$_SERVER['REMOTE_HOST']) {
			$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}
		$ip_list = explode(',', $cfg['login_ip']);
		foreach($ip_list as $ip) {
			if(strstr($_SERVER['REMOTE_ADDR'], $ip)) {
				$fl = 1;
				break;
			} elseif(strstr($_SERVER['REMOTE_HOST'], $ip)) {
				$fl=1;
				break;
			}
		}
		if(!$fl) {
			mes('指定したIPアドレス/ホストアドレス以外からの管理認証は禁止されています','エラー','java');
		}
	}
	$cr_pass = crypt($_POST['pass'], $cfg['pass']);
	if($cfg['pass'] != $cr_pass) { //パスワードが不一致
		if(!$_SERVER['REMOTE_HOST']) {
			$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}
		$date = date('Y/m/d H:i');
		mes('パスワードの認証に失敗しました。<br />IP:'.$_SERVER['REMOTE_ADDR'].'<br />HOST:'.$_SERVER['REMOTE_HOST'].'<br />PASS:'.$_POST['pass'].'<br />DATE:'.$date, 'パスワード認証エラー', 'java');
	}
}

// 登録されたURLにアンパーサント記号(&)が含まれる場合に、IEでは対象サイトへ
// ジャンプできなくなる不都合を解消するための関数
function unhtmlentities($string) {
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	return strtr($string, $trans_tbl);
}


function checkSQLWord($word) {
	if(preg_match("/delete.*from|select.*from|insert\sinto|1=1|'[0-9a-z]{1}'='[0-9a-z]{1}'|drop.*table|update.*set|truncate|or.*='/i", $val) == true) {
	    return false;
	} else {
            return true;
        }
}

//----------------------------------------------------------------------------
// (f1)登録するカテゴリを表示(print_category)
function print_category($category = '') {
	global $cfg, $cfg_reg, $db, $_POST;
	if(!isset($_POST['changer'])) {
		$_POST['changer'] = '';
	}
	$category_list = explode('&', $category);
    
	if($cfg_reg['kt_min'] != $cfg_reg['kt_max']) {
		echo '※'.$cfg_reg['kt_min'].'-'.$cfg_reg['kt_max'].'個まで選択できます<br />';
	} else {
		echo '※'.$cfg_reg['kt_max'].'個選択してください<br />';
	}
	echo '※各カテゴリの詳細は「<a href="sitemap.php">ｶﾃｺﾞﾘ一覧</a>」を参考にしてください<br />'."\n";

	for($category_no = 1; $category_no <= $cfg_reg['kt_max']; $category_no++) {
	    
	    if($_POST['Fkt'.$category_no] == '') $select = ' selected';
                if(defined('SMARTPHONE_SITE_NAME')) echo '<div data-role="fieldcontain"><label for="Fkt'.$category_no.'" class="select"></label>';
		echo '<select name="Fkt'.$category_no.'" size="7"   ';
                if(defined('SMARTPHONE_SITE_NAME')) echo 'id="Fkt'.$category_no.'" ';
                echo '>';
		if(isset($category_list[$category_no]) && $category_list[$category_no] != '') {
		    echo '<option value="'. $category_list[$category_no] . '"'.$select.'>' . mb_convert_kana( full_category($category_list[$category_no]), 'ka' ) . "</option>\n";
			$select='';
		}
		echo '<option value="" '.$select.'>--指定しない--</option>';
		$query = 'SELECT path, regist FROM '.$db->db_pre.'category ORDER BY path';
		$rowset = $db->rowset_assoc($query);
		foreach($rowset as $row) {
			if($_POST['changer'] == 'admin' || !$row['regist']) {
			    echo '<option value="'.$row['path'].'" ';
			    if($_POST['Fkt'.$category_no] == $row['path']) echo 'selected';
			    echo '>' . mb_convert_kana(full_category($row['path']), 'ka') . "</option>\n";
			}
		}
		echo '</select>';
                if(defined('SMARTPHONE_SITE_NAME')) echo '</div> ';
                echo '<br /><br />'."\n";
	}
	echo '<br />'."\n";
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
		$_POST['Fsyoukai'] = str_replace('<br />', "\n", $_POST['Fsyoukai']);
		$back_url .= '<form action="regist.php" method="post">' . "\n";
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
            $back_url .= '<input type="hidden" name="Fkt' . $i . '" value="' . $_POST['Fkt'.$i] . '">' . "\n";
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
	if(get_magic_quotes_gpc()) {
		$_POST = array_map('stripslashes', $_POST);
	}
	$_POST = array_map('htmlspecialchars', $_POST);
	if($cfg_reg['kt_no_word']) {
		// ワードチェック対象の項目
		$check_str = implode(' ', array($_POST['Fname'], $_POST['Femail'], $_POST['Furl'], $_POST['Fbana_url'], $_POST['Ftitle'], $_POST['Fsyoukai'], $_POST['Fkey']));
		$no_words = explode(' ', $cfg_reg['kt_no_word']);
		foreach($no_words as $word) {
			if(stristr($check_str, $word)) {
				mes("登録データの中にが禁止されている言葉が入っています。<br />登録しようとしているデータのジャンルをこのサーチエンジンが禁止している可能性があります。", "ワードチェックエラー", "back_reg");
			}
		}
		if(!$_SERVER["REMOTE_HOST"]) {
			$_SERVER["REMOTE_HOST"] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
		}
		$addr_host = $_SERVER["REMOTE_ADDR"] . " " . $_SERVER["REMOTE_HOST"];
		foreach($no_words as $word) {
			if(stristr($addr_host, $word)) {
				mes("このIP又はホスト名からの登録は禁止されている可能性があります。<br />{$_SERVER["REMOTE_ADDR"]}/{$_SERVER["REMOTE_HOST"]}<br />", "IP/HOSTチェックエラー", "back_reg");
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
		$_POST['Fsyoukai'] = str_replace(array("\r\n", "\r", "\n"), "<br />", $_POST['Fsyoukai']);
	}
	// 管理人コメント
	if(@$_POST['Fkanricom']) {
		$_POST['Fkanricom'] = str_replace(array("\r\n", "\r", "\n"), "<br />", $_POST['Fkanricom']);
	}
	// カテゴリ
	$kt_fl = array();
	for($i = 1; $i <= $cfg_reg['kt_max']; $i++) {
		if(!$_POST["Fkt{$i}"]) {
			$_POST["Fkt{$i}"] = 0;
		}
		$query = "SELECT id, regist FROM {$db->db_pre}category WHERE path='{$_POST["Fkt{$i}"]}'";
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
		$_POST['Fadd_kt'] = str_replace(array("\r\n", "\r", "\n"), "<br />", $_POST['Fadd_kt']);
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
			$_POST['Fto_admin'] = str_replace(array("\r\n", "\r", "\n"), '<br />', $_POST['Fto_admin']);
		}
	}
}

// (f4)カテゴリを表示1(preview_category1)
function preview_category1() {
	global $cfg_reg;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		$value = $_POST['Fkt'.$kt_no];
		echo '<input type="hidden" name="Fkt'.$kt_no.'" value="'.$value.'">'."\n";
	}
}

// (f5)カテゴリを表示2(preview_category2)
function preview_category2() {
	global $cfg_reg;
	for($kt_no = 1; $kt_no <= $cfg_reg['kt_max']; $kt_no++) {
		$value = $_POST['Fkt'.$kt_no];
		echo full_category($value);
		echo '<input type="hidden" name="Fkt'.$kt_no.'" value="'.$value.'"><br />'."\n";
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
		$log_data[7] = $pre_log[7];
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
			$log_data[10] .= $_POST['Fkt'.$i] . '&';
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
			mes('そのURLはすでに登録されています<br /><br />'.$log_data[1].' :<br />'.$log_data[2], '2重登録エラー', 'java');
		}
		if($fl == '2' && $i == '1' && $pre_log[2] != $_POST['Furl']) {
			mes('そのURLはすでに登録されています<br /><br />'.$pre_title.' :<br />'.$_POST['Furl'], '2重登録エラー', 'java');
		}
	}
	if($_POST['changer'] != 'admin' && $cfg['user_check'] && $_POST['mode'] == 'act_regist' && $fl == 1) {
		// 仮登録モードでユーザの新規登録時
		$query = 'SELECT title FROM '.$db->db_pre.'log_temp WHERE url=\''.$_POST['Furl'].'\' LIMIT 1';
		$log_data = $db->single_num($query);
		if($log_data) {
			mes('そのURLは現在登録申請中です<br /><br />'.$log_data[0].' :<br />'.$_POST['Furl'], '2重登録エラー', 'java');
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
            $w .= '</ul><br />' . "\n";
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
		if(get_magic_quotes_gpc()) {
			$_POST['certification_cord'] = stripslashes($_POST['certification_cord']);
			$_POST['Fcertification_cord'] = stripslashes($_POST['Fcertification_cord']);
		}
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