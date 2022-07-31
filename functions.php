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
                $n = 'name_m'.$zz;
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
	if(preg_match("/delete.*from|select.*from|insert\sinto|1=1|'[0-9a-z]{1}'='[0-9a-z]{1}'|drop.*table|update.*set|truncate|or.*='/i", $val) == true) {
	    return false;
	} else {
            return true;
        }
}



?>