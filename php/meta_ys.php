<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP) modified メタ検索処理用ファイル 						//
/*--------------------------------------------------------------------------*/

// -- 目次 -- //
// (1)メタ検索処理(&meta)
// (2)メタ検索結果画面表示(&meta_page)

// (1)メタ検索処理(&meta)
// 第一引数=>モード選択(選択表示=select/メタサーチ画面表示=meta_page)
// ※使用する検索エンジンは$_GET['検索エンジン名']は'on'を格納
// ※使用する検索エンジンは$_GET['engine']は使用検索エンジンがひとつの場合の検索エンジン名
if(!isset($_GET['hyouji'])){$_GET['hyouji'] = "";}
if(!isset($_GET['method'])){$_GET['method'] = "";}
function meta($T_mode, $cut = '') {
	// $arg1=検索モード(select | meta_page)
	$T_word = $_GET['word'];

	// %engine------サーチエンジンのプログラムの場所（http://を取り除く)
	// %engine_top--サーチエンジンのトップページ
	//  %engine_name--サーチエンジンの名称
	//  %keyword-----検索キーワードの変数名及びキーワード
	//  %option------検索オプション
	//  ----------------------------初期設定----------------------------- // 
	$K_pp = $_GET['word'];
	$K_pp2 = $_GET['word'];
	$K_pp3 = $_GET['word'];
	$K_euc = $_GET['word'];
	$K_book = $_GET['word'];
	$K_plus = $_GET['word'];
	$WORD_url = $_GET['word'];
	$Sjis = mb_convert_encoding($_GET['word'], 'SJIS', 'UTF-8');
	$K_book = str_replace('-', '', $K_book);
	// メソッド等の設定=>$methodに格納
	if($_GET['method'] == 'and') {
		$Myahoo = 's';
		$Minfoseek = '0';
		$Mgoo = 'MC';
		$K_pp = str_replace(' ', '+and+', $K_pp);
		$K_pp2 = str_replace(' ', '+', $K_pp2);
		$K_pp3 = str_replace(' ', ' and ', $K_pp3);
	} else {
		$Myahoo = 'w';
		$Minfoseek = '1';
		$Mgoo = 'SC';
		$K_pp = str_replace(' ', '+or+', $K_pp);
		$K_pp2 = str_replace(' ', '+or+', $K_pp2);
		$K_pp3 = str_replace(' ', ' or ', $K_pp3);
	}
	// その他の設定
	$K_plus = str_replace(' ', '+', $K_plus);
	$WORD_url = str_replace('http://', '', $WORD_url);
	if(isset($_GET['www'])) {
		$WORD_url = 'www.' . $WORD_url;
	}

	// URLエンコード
	$Sjis = urlencode($Sjis);
	$K_plus = urlencode($K_plus);
	$_GET['word'] = urlencode($_GET['word']);
	$K_pp = urlencode($K_pp);
	$K_pp2 = urlencode($K_pp2);
	$K_pp3 = urlencode($K_pp3);
	$K_euc = urlencode($K_euc);

	$name = array(
		'yahoo',
		'infoseek',
		'google',
                'bing',
		'goo',
		'excite',
		'FRESHEYE',
		'vector',
		'rakuten',
		'hmv_a',
		'hmv_t',
		'bk1',
		'com',
		'cojp',
                'jp',
                'net',
                'info',
                'org',
                'movi',

	);

	$engine = array(
		'yahoo'		=>	'search.yahoo.co.jp/bin/search',
		'infoseek'	=>	'www.infoseek.co.jp/Titles',
		'google'	=>	'www.google.co.jp/search',
                'bing'          =>      'www.bing.com/search',
		'goo'		=>	'search.goo.ne.jp/web.jsp',
		'excite'	=>	'www.excite.co.jp/search.gw',
		'FRESHEYE'	=>	'search.fresheye.com/',
		'vector'	=>	'search.vector.co.jp/search',
		'rakuten'	=>	'esearch.rakuten.co.jp/rms/sd/esearch/vc',
		'hmv_a'		=>	'www.hmv.co.jp/search/artists.asp',
		'hmv_t'		=>	'www.hmv.co.jp/search/title.asp',
		'bk1'		=>	'www.bk1.co.jp/search/search.asp',
		'com'		=>	'www.google.co.jp/search',
		'cojp'		=>	'www.google.co.jp/search',
                'jp'            =>      'www.google.co.jp/search',
                'net'            =>      'www.google.co.jp/search',
                'info'            =>      'www.google.co.jp/search',
                'org'            =>      'www.google.co.jp/search',
                'movi'            =>     'www.google.co.jp/search'
	);

	$engine_top = array(
		'yahoo'		=>	'www.yahoo.co.jp/',
		'infoseek'	=>	'www.infoseek.co.jp/',
		'google'	=>	'www.google.co.jp/intl/ja/',
                'bing'          =>      'www.bing.com/',
		'goo'		=>	'www.goo.ne.jp/',
		'excite'	=>	'www.excite.co.jp/',
		'FRESHEYE'	=>	'www.fresheye.com/index.html',
		'vector'	=>	'www.vector.co.jp/',
		'rakuten'	=>	'www.rakuten.co.jp/',
		'hmv_a'		=>	'www.hmv.co.jp/mu/',
		'hmv_t'		=>	'www.hmv.co.jp/mu/',
		'bk1'		=>	'www.bk1.co.jp/',
		'com'		=>	'www.google.com/intl/ja/',
		'cojp'		=>	'www.google.com/intl/ja/',
                'jp'            =>      'www.google.com/intl/ja/',
                'net'           =>      'www.google.com/intl/ja/',
                'info'          =>      'www.google.com/intl/ja/',
                'org'           =>      'www.google.com/intl/ja/',
                'movi'          =>      'www.google.com/intl/ja/'
	);

	$engine_name = array(
		'yahoo'		=>	'YAHOO! JAPAN',
		'infoseek'	=>	'Infoseek',
		'google'	=>	'Google',
		'bing'          =>	'bing',
		'goo'		=>	'goo',
		'excite'	=>	'Excite Japan',
		'FRESHEYE'	=>	'フレッシュアイ',
		'vector'	=>	'Vector',
		'rakuten'	=>	'楽天市場',
		'hmv_a'		=>	'HMV(アーティスト名検索)',
		'hmv_t'		=>	'HMV(タイトル名検索)',
		'bk1'		=>	'bk1',
		'com'		=>	'.com',
		'cojp'		=>	'.co.jp',
                'jp'            =>      '.jp',
                'net'           =>      '.net',
                'info'          =>      '.info',
                'org'           =>      '.org',
                'movi'          =>      '.movi'
	);

	$keyword = array(
		'yahoo'		=>	'p='.$K_plus,
		'infoseek'	=>	'qt='.$K_plus,
		'google'	=>	'q='.$K_pp2,
		'bing'  	=>	'q='.$K_pp2,
		'goo'		=>	'MT='.$K_euc,
		'excite'	=>	's='.$K_plus,
		'FRESHEYE'	=>	'kw='.$K_pp,
		'vector'	=>	'query='.$_GET['word'],
		'rakuten'	=>	'sitem='.$_GET['word'],
		'hmv_a'		=>	'keyword='.$Sjis,
		'hmv_t'		=>	'keyword='.$Sjis,
		'bk1'		=>	'kywd='.$_GET['word'].'&srch=1&Sort=za&submit.x=0&submit.y=0',
		'com'		=>	'q='.$K_pp2.'.com',
		'cojp'		=>	'q='.$K_pp2.'.co.jp',
		'jp'		=>	'q='.$K_pp2.'.jp',
		'net'		=>	'q='.$K_pp2.'.net',
		'info'		=>	'q='.$K_pp2.'.info',
		'org'		=>	'q='.$K_pp2.'.org',
		'movi'		=>	'q='.$K_pp2.'.movi',
	);

	$option = array(
		'yahoo'		=>	'n='.$_GET['hyouji'].'&w='.$Myahoo,
		'infoseek'	=>	'sv=JP&lk=noframes&rt=JG&qp='.$_GET['method'].'&nh='.$_GET['hyouji'],
		'google'	=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
                'bing'          =>      '',
		'goo'		=>	'SM='.($_GET['method'] == 'and'?'MC':'SC').'&DC='.$_GET['hyouji'],
		'excite'	=>	'lk=excite_jp&c=japan',
		'FRESHEYE'	=>	'term=monthly',
		'vector'	=>	'',
		'rakuten'	=>	'',
		'hmv_a'		=>	'',
		'hmv_t'		=>	'',
		'bk1'		=>	'',
		'com'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'cojp'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'jp'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'net'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'info'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'org'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
		'movi'		=>	'num='.$_GET['hyouji'].'&hl=ja&ie=UTF-8&oe=UTF-8&btnG=Google+検索',
	);

	// --------------------------- メインルーチン ---------------------- //

	if($T_mode == 'select') {
		$tmp = $_GET['engine'];
		if(!$_GET['word']) {
			location('http://'.$engine_top[$tmp]);
                        exit();
		} else {
			if($_GET['engine'] == 'com' || $_GET['engine'] == 'cojp') { // ?&をカット
				location('http://'.$engine[$tmp]);
                                exit();
			} else {
				location('http://'.$engine[$tmp].'?'.$keyword[$tmp].'&'.$option[$tmp]);
                                exit();
			}
		}
	} else {
		$location_list = array();
		foreach($name as $tmp) {
			if(isset($_GET[$tmp])) {
				if($_GET[$tmp] == 'on' || $T_mode == 'meta_page') {
					if(!isset($_GET['word'])) {
						array_push($location_list, $engine_name[$tmp].'<>http://'.$engine_top[$tmp].'<>'."\n");
					} else {
						array_push($location_list, $engine_name[$tmp].'<>http://'.$engine[$tmp].'?'.$keyword[$tmp].'&'.$option[$tmp].'<>'."\n");
					}
				}
			} else {
				if($T_mode == 'meta_page') {
					if(!isset($_GET['word'])) {
						array_push($location_list, $engine_name[$tmp].'<>http://'.$engine_top[$tmp].'<>'."\n");
					} else {
                                                if(strstr($engine_name[$tmp],'.') == false) {
        						array_push($location_list, $engine_name[$tmp].'<>http://'.$engine[$tmp].'?'.$keyword[$tmp].'&'.$option[$tmp].'<>'."\n");
                                              } else {
        						array_push($location_list,urldecode( $_GET['word']).$engine_name[$tmp].'<>http://'.$engine[$tmp].'?'.$keyword[$tmp].'&'.$option[$tmp].'<>'."\n");
                                               }
					}
				}
			}
		}
	}

	if($cut == 'on') {
		return $location_list;
	}
	if($T_mode == 'meta_page') {
		meta_page($T_word, $location_list);
	}

	exit();
}

// (2)メタ検索結果画面表示(&meta_page)
function meta_page($T_word, $location_list) {
	$_GET['word'] = $T_word;
	PR_meta_page($location_list);
	exit();
}
?>