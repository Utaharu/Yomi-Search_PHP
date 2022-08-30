<?php
//著作権表示(削除・変更をしないでください。ただし、中寄せ・左寄せは可)
function cr(){
	global $cfg;
	echo '- <a href="http://yomi.pekori.to/" target="_blank">Yomi-Search</a> - ';
	echo '<a href="http://sql.s28.xrea.com/">Yomi-Search(PHP)</a>&nbsp;/&nbsp;<a href="http://yomiphp-mod.sweet82.com/" target="_blank">Yomi-Search(PHP)modified ver1.5.8</a> - ';
	echo '<a href="http://www.nkbt.net/yomi/" target="_blank">'.$cfg['ver'].'</a> - ';
}

//(c1)クッキーの書き込み(set_cookie)
function set_cookie($data) {
	//[0]=パスワード(登録者用)/[1]=ID(登録者用)/[2]=変更者/[3]=管理者用パスワード
	//[4]=直接認証(1or0)/[5]=検索条件(,で区切る)/
	$cookie = implode(':', $data);
	$cookie = str_replace(' ', '', $cookie);
	$cookie = str_replace(';', '', $cookie);
	setcookie('ysp', $cookie, time() + 5184000);
}

//(c1.1)クッキーを初期化(set_fo_cookie)
function set_fo_cookie() {
	setcookie('ysp', '', 0);
}

//(c2)クッキーの読み込み(get_cookie)
function get_cookie() {
	if(isset($_COOKIE['ysp'])) {
		$cookie_data = explode(':', $_COOKIE['ysp']);
	} else {
		$cookie_data = array('','','','','','');
	}
	return $cookie_data;
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
    $w=<<<EOF
             <div class="ui-bar ui-bar-a">
                    <h1>その他のカテゴリ</h1>
            </div>
            <div class="ui-body ui-body-c">
                <select id="other_category" onChange="location.href=$('#other_category').val();">
EOF;
	foreach ($rowset as $row) {
            $w .= '<option value="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'</option>'."\n";
	}
        $w .= '</select></div>';
        echo $w;
        return true;
}

/**
 * スマートフォン版にあわせてメニューHTMLを整える
 *
 * @param string $str PC版の元のリンク
 */
function printTopMenuLink($str, $color='') {
    $tmp = explode(" -\n", $str);
    $t = count($tmp);
    $w = '<style>a.ui-link:link{color:#7199C0;} a.ui-link:visited{color:#444782;} a.ui-link:hover{color:#19C5D9;}</style>';
    $w .= '|';
    for($z=0; $z<$t; $z++) {
        if($z==4) $w = trim($w, '|')."<br />\n";
//        $tmp[$z] = str_replace('サイト', '', $tmp[$z]);
        $tmp[$z] = str_replace('ランキング', 'ランク', $tmp[$z]);
        $w .= mb_convert_kana($tmp[$z], 'ka', 'UTF-8').'|';
    }
    if($color != '') {
        $w = str_replace('href=', ' style="color:'.$color.';" rel="external" href=', $w);
    } else {
        $w = str_replace('href=', ' rel="external" href=', $w);
    }
    $w = str_replace('href=?', 'href=index.php?', $w);
    echo str_replace('yomi.php', SMARTPHONE_INDEX, trim($w, '|'));
}

/**
 *　サブカテゴリ以下の総登録数(subcategory)
 *
 * @global object $db
 * @param  string $path
 * @return array  サブカテゴリ以下の登録数、更に下の階層のカテゴリがあるかのidを返す（なければfalse)を返す
 */
function subcategory($path) {
	global $db;
	$count = 0;
	$check = 0;
	$num = "";
	$sub = "";
	$query = 'SELECT COUNT(id) FROM '.$db->db_pre.'log WHERE category LIKE \'%&'.$path.'%\'';
	$num = $db->single_num($query);
	$query = 'SELECT id FROM '.$db->db_pre.'category WHERE path LIKE \''.$path.'_%\'';
	$sub = $db->single_num($query);
	if(isset($num[0])){$num = $num[0];}
	if(isset($sub[0])){$sub = $sub[0];}
	return (array($num, $sub));
}

/**
 *  直下カテゴリをHTMLタグ表示
 *  smartphone版はプルダウンで一覧表示させて飛ぶようにします。。
 *
 * @global array $cfg 設定配列
 * @global object $db　DBオブジェクト
 * @param string $category  カテゴリ
 * @param int $num       1=登録数表示 0=登録数非表示
 * @param int $column    4=カテゴリの表示列数＞スマートフォン版は関係ない。。
 * @return boolean true  HTML出力して終了
 */
function print_subcategory($category, $num, $column) {
	global $cfg, $db;
        $db_pre = $db->db_pre;

	$query = 'SELECT id FROM '.$db_pre.'category WHERE path=\''.$category.'\'';
	$id = $db->single_num($query);
	$query = 'SELECT title, path FROM '.$db_pre.'category WHERE up_id=\''.$id[0].'\' ORDER BY path';
	$rowset = $db->rowset_assoc($query);

        $w = '';
        $w=<<<EOF
                 <div class="ui-bar ui-bar-a">
                        <h1>直下カテゴリ</h1>
                </div>
                <div class="ui-body ui-body-c">
                    <select id="chokka_category" onChange="location.href=$('#chokka_category').val();">
EOF;

        if($rowset[0] != "") {
            foreach ($rowset as $row) {
                    list($count, $check) = subcategory($row['path']);
                    $w .= '<option value="'.$cfg['script'].'?mode=dir&amp;path='.trim($row['path'],'/').'">'.$row['title'];
                    if($check) {
                            $w .= '*';
                    }
                    if($num) {
                            $w .=' ('.$count.')';
                    }
                    $w .= '</option>';
            }
            $query = 'SELECT title, path FROM '.$db_pre.'category WHERE reffer LIKE \'%&'.$id[0].'&%\' ORDER BY path';
            $rowset = $db->rowset_assoc($query);

			$tr_flag = 0;
            foreach($rowset as $row) {
                    $id = substr($row['path'], 0, -1);
                    list($count, $check) = subcategory($id);

                    $w .=  '<option value="'.$cfg['script'].'?mode=dir&amp;path='.trim($row['path'],'/').'">'.$row['title'].'@';
                    if($num) {
                            $w .= ' ('.$count.')';
                    }
                    if($tr_flag < $column) {
                            $tr_flag++;
                    } else {
                            $tr_flag = 0;
                    }
            }
            $w .=  '</value>'."\n";
        }
        $w .= '</select></div>'."\n";
        echo $w;
        return true;
}


/**
 * スマートフォン版下部分のカテゴリメニューを表示します。
 * プルダウンメニューにします
 *
 * @param string $mode mode
 * @param string $path パス
 * @return none  カテゴリメニューHTMLタグを出力して終わり
 */
function printCategoryMenu($mode, $get_path) {
    global $cfg,$cfg_reg;
	$regist = "";
    $w=<<<EOF
             <div class="ui-bar ui-bar-a">
                    <h1>カテゴリメニュー</h1>
            </div>
            <div class="ui-body ui-body-c">
                <select id="select_category_menu" onChange="location.href=$('#select_category_menu').val();">
EOF;
	if($mode == 'dir' && ($cfg['rank_fl'] || $cfg['rev_fl'])) {
		if($cfg['rank_fl']) {
			$w .= '<option value="'.$cfg['rank'].'?path='.$get_path.'">人気ﾗﾝｷﾝｸﾞ(ｶﾃｺﾞﾘ別)</option>';
		}
		if($cfg['rev_fl']) {
			$w .= '<option value="'.$cfg['rank'].'?mode=rev&path='.$get_path. '">ｱｸｾｽﾗﾝｷﾝｸﾞ(ｶﾃｺﾞﾘ別)</option>';
		}
	}
        if(!$regist && $_GET['mode'] == 'dir' && !$cfg_reg['no_regist']) {
                $w .= '<option value="regist_ys.php?mode=regist&path=' . $get_path . '">このｶﾃｺﾞﾘに新規登録</option>';
        }
        $w .= '</select></div>';
        echo $w;
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
		$writeStr = '<img src="'.SMARTPHONE_IMG_PATH.'new.gif" alt="'.$cfg['name_new'].'\" title="'.$cfg['name_new'].'" align="absbottom"> ';
	} elseif(($times - $log_data["stamp"]) < 86400 * $cfg["new_time"]) {
		//更新マーク
		$writeStr = '<img src="'.SMARTPHONE_IMG_PATH.'renew.gif" alt="'.$cfg['name_renew'].'\" title="'.$cfg['name_renew'].'" align="absbottom"> ';
        }

        //m1マーク(デフォルト：おすすめ)
	//m2マーク(デフォルト：相互リンク)
        for($z=0; $z<10; $z++) {
            if($mark[$z]) {
                $zz=$z+1;
                $n = 'name_m'.$zz;
		$writeStr .= '<img src="'.SMARTPHONE_IMG_PATH.'m'.$zz.'.gif" alt="'.$cfg[$n].'" title="'.$cfg[$n].'" align="absbottom"> ';
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
	global $cfg, $db, $navi_h1str, $title;
	$navi_id = array();
	$path = '';
	$navi = '';
	
	if(strstr($id, '/') !== false) {
		$navi_id = explode('/', substr($id, 0, -1));
		array_pop($navi_id);
	} else {
		$navi_id[0] = $id;
	}
	foreach($navi_id as $tmp) {
		$path .= $tmp . '/';
		$query = 'SELECT title FROM '.$db->db_pre.'category WHERE path=\''.$path.'\'';
		$row = $db->single_assoc($query);
		$navi .= '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$path.'">'.$row['title'].'</a> &gt; ';
		$navi_h1str .= $row['title'].'-';
	}
	if($navi_h1str != '') {
		$navi_h1str = trim($navi_h1str, '-');
		if($title != '') $navi_h1str .= '<br />'.$title;
	} else {
		if($title != '') $navi_h1str .= $title;
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
		mes('パスワードの認証に失敗しました。<br>IP:'.$_SERVER['REMOTE_ADDR'].'<br>HOST:'.$_SERVER['REMOTE_HOST'].'<br>PASS:'.$_POST['pass'].'<br>DATE:'.$date, 'パスワード認証エラー', 'java');
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
	if(preg_match("/delete.*from|select.*from|insert\sinto|1=1|'[0-9a-z]{1}'='[0-9a-z]{1}'|drop.*table|update.*set|truncate|or.*='/i", $word) == true) {
	    return false;
	} else {
            return true;
        }
}


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



?>