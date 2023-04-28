<?php
// ######################################### //
/*  PHP名：Yomi-Search PHP  */
$debug_mode = 1;
/* Error - Display Setting  */
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors',1);
// ######################################## //

/* ################## - System Setting - ################## */
$system_set = array();
$system_set['locale'] = "ja_JP";//Locale
$system_set['time_zone'] = "Asia/Tokyo";//Time Zone
$system_set['language'] = "ja";//Language
$system_set['charset'] = "UTF-8";//charset
$system_set['charlist'] = array("UTF-8","EUC-JP","JIS","Shift_JIS");

/* - Adapting System Settings - */
setlocale(LC_ALL, $system_set['locale']);
date_default_timezone_set($system_set['time_zone']);
mb_language($system_set['language']);
mb_internal_encoding($system_set['charset']);
mb_http_output($system_set['charset']);
mb_regex_encoding($system_set['charset']);
ob_start('mb_output_handler');

/* ################## - Database Setting - ################## */
$db_set['host'] = "localhost";// MySQLのホスト名
$db_set['user'] = "root";// MySQLのログインに使用するユーザ名
$db_set['password'] = "";// MySQLのログインに使用するパスワード
$db_set['name'] = "yomiyomi";// MySQLのデータベース名
$db_set['prefix'] = "zzz_";// テーブルの先頭に付加するプレフィックス
$db_set['charset'] = "utf8mb4";/* 文字コードの指定 (mysqli_set_charset) */

/* ##################  - Root Path Setting - ################## */
/* - # Root # - */
$path_set['root'] = __DIR__;

/* - # Library # - */
$path_set['lib']['root'] = "/php";

$path_set['lib']['db']['root'] = "/database";
$path_set['lib']['db']['expand'] = "/expand";

$path_set['lib']['setup'] = "/setup";
$path_set['lib']['registration'] = "/registration";
$path_set['lib']['ranking'] = "/ranking";
$path_set['lib']['search'] = "/search";
$path_set['lib']['metasearch'] = "/metasearch";
$path_set['lib']['help'] = "/help";
$path_set['lib']['sitemap'] = "/sitemap";
$path_set['lib']['admin'] = "/admin";
$path_set['lib']['top'] = "/top";
$path_set['lib']['category'] = "/category";
$path_set['lib']['report'] = "/report";
$path_set['lib']['mylink'] = "/mylink";
$path_set['lib']['plugin'] = "/plugin";

/* - # Template # - */
$path_set['template']['root'] = "/template";
$path_set['template']['pc'] = "/pc";
$path_set['template']['admin'] = "/admin";
$path_set['template']['setup'] = "/setup";
$path_set['template']['phone'] = "/phone";

/* - # RSS # - */
$path_set['rss'] = "/rss";

/* - # SmartPhone Page # - */
$path_set['phone'] = "/s";

$path_set['pc'] = "/";
/* - # Root Setting # - */
$path_set = FullPath_Settings::Directory($path_set);

/* ################## - Library Script Setting - ################## */

$script['lib']['function'] = "/functions.php";
$script['lib']['ads'] = "/ads.php";
$script['lib']['count_ys'] = "/count_ys.php";

$script['lib']['random'] = "/random.php";

/* - # DB # - */
$script['lib']['db']['main'] = "/db_connecter.php";
$script['lib']['db']['create'] = "/create_table.php";
$script['lib']['db']['get_setting'] = "/get_settings.php";

/* - - # DB - Expand # - - */
$script['lib']['db']['expand']['ipv4_v6'] = "/ipv4_v6.php";

/* - # Main Index # - */
$script['lib']['index'] = "/index.php";

/* - # Top # - */
$script['lib']['top']['index'] = "/index.php";

/* - # Setup # - */
$script['lib']['setup']['index'] = "/index.php";
$script['lib']['setup']['table'] = "/setup_table.php";
$script['lib']['setup']['initial'] = "/initial.php";

/* - # Registration # - */
$script['lib']['registration']['index'] = "/index.php";
$script['lib']['registration']['register'] = "/register.php";
$script['lib']['registration']['delete'] = "/delete.php";
$script['lib']['registration']['edit'] = "/edit.php";
$script['lib']['registration']['reissue'] = "/reissue.php";
$script['lib']['registration']['confirm_form'] = "/confirm_form.php";
$script['lib']['registration']['user_form'] = "/user_form.php";
$script['lib']['registration']['admin_form'] = "/admin_form.php";
$script['lib']['registration']['common'] = "/common.php";



/* - # Category # - */
$script['lib']['category']['index'] = "/index.php";

/* - # Search # - */
$script['lib']['search']['index'] = "/index.php";
$script['lib']['search']['form'] = "/form.php";
$script['lib']['search']['result'] = "/result.php";
$script['lib']['search']['advanced'] = "/advanced.php";

/* - # MetaSearch # - */
$script['lib']['metasearch']['index'] = "/index.php";
$script['lib']['metasearch']['result'] = "/result.php";

/* - # Ranking # - */
$script['lib']['ranking']['index'] = "/index.php";
$script['lib']['ranking']['access'] = "/access.php";
$script['lib']['ranking']['keyword'] = "/keyword.php";

/* - # Help # - */
$script['lib']['help']['index'] = "/index.php";

/* - # Sitemap # - */
$script['lib']['sitemap']['index'] = "/index.php";

/* - # Admin # - */
$script['lib']['admin']['index'] = "/index.php";
$script['lib']['admin']['listbox'] = "/listbox.php";

/* - # Report # - */
$script['lib']['report']['index'] = "/index.php";

/* - # MyLink # - */
$script['lib']['mylink']['index'] = "/index.php";



/* - # Plugin # - */
$script['lib']['plugin']['counter'] = "/counter.php";
$script['lib']['plugin']['random'] = "/random.php";
$script['lib']['plugin']['mailer'] = "/mailer.php";
$script['lib']['plugin']['cookie'] = "/cookie.php";
$script['lib']['plugin']['navigation'] = "/navigation.php";
$script['lib']['plugin']['ad'] = "/ad.php";

/* - # File Root Setting # - */
$script['lib'] = FullPath_Settings::File($script['lib'],$path_set['lib']);

/* ################## - Page Script Setting - ################## */
/* - # PC Page # - */
$script['rss'] = "./rss/rss.php";
$script['admin'] = "./admin.php";


$script['pc']['index'] = "./index.php";
$script['pc']['rank'] = "./rank.php";
$script['pc']['regist'] = "./regist_ys.php";
$script['pc']['search'] = "./search.php";
$script['pc']['sitemap'] = "./sitemap.php";
$script['pc']['help'] = "./help.php";

/* - # SmartPhone Page # - */
$script['phone']['index'] = "./index.php";
$script['phone']['rank'] = "./rank.php";
$script['phone']['regist'] = "./regist.php";
$script['phone']['search'] = "./search.php";
$script['phone']['search_ex'] = "./search_ex.php";
$script['phone']['sitemap'] = "./sitemap.php";
$script['phone']['help'] = "./help.php";

$css['pc']['main'] = "css/main.css";
$css['pc']['admin'] = "css/admin.css";

/* ################## - Jquery - ######################## */
$jquery['phone']['mobile'] = "../template/phone/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js";
$jquery['phone']['main'] = "../template/phone/js/jquery-2.2.4.min.js";
$jquery['phone']['css'] = "../template/phone/js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css";


// #########################################################
/* #  - Setup Config - # */
// #########################################################
$Setup_Config = array();

$Setup_Config['script'] = array(
	'index'=>"index.php"
);
// ---- Setup Template File Name ---- //
$Setup_Config['template'] = array(
	'start'=>"/start.html",
	'install'=>"/install.html",
	'settings'=>"/settings.html",
	'compleate'=>"/compleate.html",
	'error'=>"/error.html",
);

/* - # Path Root Setting # - */
$Setup_Config['template'] = FullPath_Settings::File($Setup_Config['template'],$path_set['template']['setup']);

$Setup_Config['template']['css'] = "../css/setup_style.css";

// ---- 一般の共通設定 ---- //
$Setup_Config['common'] = array(
             'search_name'  => '', // サーチエンジンの名称
	         'admin_name'   => '',                   // 管理人の名前
	         'admin_email'  => '',                         // 管理人のE-Mail
	         'admin_hp'     => '',                  // 管理人のホームページのURL
             'pass'         => '',                    // 管理用パスワード(WEB上で設定後は暗号化されます)
             'home'         => '',                 // サーチエンジンのトップへのパスorURL
             'script'       => 'index.php',                 // yomi-search本体のファイル名
             'search'       => 'search.php',               // 検索用のPHPファイル名
             'rank'         => 'rank.php',                 // ランキング用のPHPファイル名
             'admin'        => 'admin.php',                // 管理処理用のPHPファイル名
             'login_ip'     => '',                         // 管理メニューを実行できるIPアドレスの設定リスト
             'hyouji'       => '20',                       // 1ページに表示するログ件数
             'new_time'     => '14',                       // 新着・更新期間の日数
             'count'        => '1',                        // アクセスカウンタを(使用しない=0/使用する=1)
             're_pass_fl'   => '1',                        // パスワード再発行を(しない=0/する=1)
             'syoukai_br'   => '1',                        // 紹介文の改行(無効=0/有効=1)
             'cfg_kanri_fl' => '0',                        // CookIEによる直接認証(有効にする=1/無効にする=0)

             // 標準のログ表示順(mark/id_new/id_old/time_new/time_old/ac_new/ac_old)
             'defo_hyouji' => 'mark',

             // ジャンプ処理
             'location' => '1', // (Locationを使う=1/メタタグを使う=0)

             'html' => '0', // カテゴリ表示(CGI=0/HTML=1)
             'top'  => '0', // サーチエンジンのトップ(CGI=0/HTML=1)

             // ユーザ権限設定
             'user_change_kt' => '0', // カテゴリ変更(可能=0/不可=1)
             'user_check'     => '0', // 仮登録モードに(しない=0/する=1)

             // ディレクトリ設定
             'log_path'  => 'log/',      // ログディレクトリのパス(URLは不可)
             'temp_path' => 'template/', // テンプレートディレクトリのパス(URLは不可)
             'sub_path'  => 'php/',      // 各種phpスクリプトファイル保存ディレクトリ
			 
             // URL設定
             'cgi_path_url'  => '',      // CGIファイルを置くディレクトリのURL
             'img_path_url'  => 'img/',  // 画像ファイルを置くディレクトリのURL

			 //スマホ版の設定(sディレクトリの位置から)
			 'sp_search_name'=> '',//　サーチエンジンの名称
			 'sp_path_url' => './',	//　スマホ用のディレクトリ
			 'sp_sub_path' =>'../php/',//各種phpスクリプトファイル保存ディレクトリ(スマホ用ディレクトリからのルート)
			 'sp_temp_path' =>'./template/',//テンプレートディレクトリ
			 'sp_script' => 'index.php',//yomi-search本体のファイル名
			 'sp_home' => '',//サーチエンジンのトップへのパスorURL
			 'sp_img_path' => "../img/",// 画像ファイルを置くディレクトリのURL

             // 名称設定
             'name_new'      => '新着サイト',
             'name_renew'    => '更新サイト',
             'name_rank'     => '人気ランキング',
             'name_rank_bf'  => '前回のランキング',
             'name_rank_rui' => '累計人気ランキング',
             'name_rev'      => 'アクセスランキング',
             'name_rev_bf'   => '前回のアクセスランキング',
             'name_rev_rui'  => '累計アクセスランキング',

             // メールの設定
             'mail_to_admin'    => '0', // 管理人にメールを送信(しない=0/する=1)
             'mail_to_register' => '0', // 登録者にメールを送信(しない=0/する=1)
             'mail_new'         => '0', // 新規登録完了メールを送信(しない=0/する=1)
             'mail_ch'          => '0', // 登録内容変更完了メールを送信(しない=0/する=1)
             'mail_temp'        => '0', // 仮登録完了メールを送信(しない=0/する=1)
             'mail_pass'        => '1', // パスワード変更メールを送信(しない=0/する=1)

             // キーワードランキングの設定
             'keyrank'        => '1',  // (実施しない=0/実施する=1)
             'keyrank_min'    => '1',  // 管理室で表示する最低数
             'keyrank_kikan'  => '14', // 集計期間(日数)
             'keyrank_hyouji' => '30', // 表示数
             'keyrank_cut'    => '1',  // 一日ごとに指定数以下のデータは削除

             // 人気(OUT)ランキングの設定
             'rank_fl'    => '1',   // 実施しない=0/実施する=1
             'rank_min'   => '1',   // ランクインさせる最低アクセス数
             'rank_kikan' => '14',  // 集計期間(日数)
             'rank_time'  => '6',   // ランキング更新頻度(時間)
             'rank_best'  => '100', // ランキングデータの最大保持件数
             'rank_ref'   => '',    // 集計対象のURL(rank.cgiやyomi.cgiを置くディレクトリのURL)の一部(指定しない場合は未記入)

             // アクセス(IN)ランキングの設定
             'rev_fl'    => '1',       // (実施しない=0/実施する=1)
             'rev_min'   => '1',       // ランクインさせる最低アクセス数
             'rev_kikan' => '14',      // 集計期間(日数)
             'rev_best'  => '100',     // ランキングデータの最大保持件数
             'rev_url'   => 'http://', // アクセスランキング時のリンクジャンプ先URL

             // マイリンクの設定
             'mylink_fl' => '1', // 使用しない=0/使用する=1

             // 管理人への通知フォームの設定
             'no_link_min' => '0', // 報告する最低値
             'no_link_ip'  => '',  // 通知を拒否するIPアドレス(の一部)リスト

             // 特殊カテゴリ設定
             'name_m1'  => 'おすすめサイト',
             'm1_ys'    => '',
             'm1_fl'    => '1',
             'name_m2'  => '相互リンクサイト',
             'm2_ys'    => '',
             'm2_fl'    => '1',
             'name_m3'  => '特殊3',
             'm3_ys'    => '',
             'm3_fl'    => '1',
             'name_m4'  => '特殊4',
             'm4_ys'    => '',
             'm4_fl'    => '1',
             'name_m5'  => '特殊5',
             'm5_ys'    => '',
             'm5_fl'    => '1',
             'name_m6'  => '特殊6',
             'm6_ys'    => '',
             'm6_fl'    => '1',
             'name_m7'  => '特殊7',
             'm7_ys'    => '',
             'm7_fl'    => '1',
             'name_m8'  => '特殊8',
             'm8_ys'    => '',
             'm8_fl'    => '1',
             'name_m9'  => '特殊9',
             'm9_ys'    => '',
             'm9_fl'    => '1',
             'name_m10' => '特殊10',
             'm10_ys'   => '',
             'm10_fl'   => '1'
       );

// ---- カテゴリ設定 ---- //
/* ##################
 'カテゴリ番号'=>array(カテゴリ名,カテゴリ名の振り仮名(ソート用),カテゴリ説明,関連カテゴリ設定,登録拒否,top表示,他として出力),

 - 関連カテゴリ設定 -
  ([例]'Aのカテゴリ番号'=>'Bのカテゴリ番号&Cのカテゴリ番号')
  の場合にはAのカテゴリを表示した際にBとCのカテゴリが関連カテゴリとして表示される
 - 登録拒否 -
  訪問者が登録不可のカテゴリ
	ON = 1, OFF = 0
 - top表示 -
  トップページに表示する(サブカテゴリの場合のみ)
	ON = 1, OFF = 0
 - 他として出力 -
  カテゴリページにおいて、[その他のカテゴリ]の部分に表示する
	ON = 1, OFF = 0
###################### */
$Setup_Config['category'] = array(
	'01'=>array('ホームページ作成','ほーむぺーじさくせい','ホームページ作成に関するサイト','03&03_01','','1','1'),
	'01_01'=>array('CGI','CGI','CGIを配布しているサイト','','','1',''),
	'01_01_01'=>array('CGIデータベース','CGIでーたべーす','','','','',''),
	'01_01_02'=>array('CGIの解説','CGIのかいせつ','','','','',''),
	'01_03'=>array('HTML','HTML','','','','',''),
	'01_04'=>array('JavaScript','JavaScript','','','','',''),
	'01_05'=>array('アクセス向上','あくせすこうじょう','','','','1',''),
	'01_05_01'=>array('宣伝できるサイト','せんでんできるさいと','','','','',''),
	'01_06'=>array('フリー素材','ふりーそざい','','','','1',''),
	'01_07'=>array('ホームページ作成関連リンク集','ほーむぺーじさくせいかんれん','','','','',''),
	
	'02'=>array('サーチエンジン','さーちえんじん','サーチエンジンに関するサイト','','','1','1'),
	'02_01'=>array('総合','01_そうごう','','','1','1'),
	'02_02'=>array('Yomi-Search系','Yomi-Searchけい','','','','',''),
	'02_03'=>array('エンターテイメント','えんたーていめんと','','','','',''),
	'02_04'=>array('音楽','おんがく','','','','',''),
	'02_05'=>array('ゲーム','げーむ','','','','',''),
	'02_06'=>array('ショッピング','しょっぴんぐ','','','','',''),
	'02_07'=>array('辞書・地図','じしょ・ちず','','','','',''),
	'02_08'=>array('地域情報','ちいきじょうほう','','','','1',''),
	'02_09'=>array('その他','ソノタ','','','','1',''),
	
	'03'=>array('無料サービス','むりょうさーびす','','','1','','1'),
	'03_01'=>array('アクセスカウンタ','あくせすかうんた','','','','',''),
	'03_02'=>array('掲示板','けいじばん','','','','1',''),
	'03_03'=>array('チャット','ちゃっと','','','','',''),
	'03_04'=>array('プロバイダ','ぷろばいだ','','','','',''),
	'03_05'=>array('ホームページ','ほーむぺーじ','','','','1',''),
	'03_05_01'=>array('ホームページ(有料)','ほーむぺーじ(ゆうりょう)','','','','',''),
	'03_06'=>array('メール','めーる','','','','1',''),
	'03_07'=>array('その他の無料サービス','ソノタノムリョウサービス','','','','',''),
	
	'04'=>array('コンピュータ','こんぴゅーた','','','1','1','1'),
	'04_01'=>array('ソフトウェア','そふとうぇあ','','','','1',''),
	'04_02'=>array('ハードウェア','はーどうぇあ','','','','',''),
	'04_02_01'=>array('Macintosh','Macintosh','','','','1',''),
	'04_02_02'=>array('Windows','Windows','','','','1',''),
	'04_02_03'=>array('その他','ソノタ','','','','',''),

	'05'=>array('ライフスタイル','らいふすたいる','','','','1','1'),
	'05_01'=>array('占い','うらない','','','','1',''),
	'05_02'=>array('グルメ','ぐるめ','','','','',''),
	'05_02_01'=>array('レストラン','れすとらん','','','','',''),
	'05_03'=>array('掲示板','けいじばん','','','','',''),
	'05_04'=>array('懸賞','けんしょう','','','','1',''),
	'05_05'=>array('個人','こじん','','','','',''),
	'05_06'=>array('チャット','ちゃっと','','','','',''),
	'05_07'=>array('出会い','であい','','','','1',''),
	'05_08'=>array('その他','ソノタ','','','','',''),

	'06'=>array('エンターテイメント','えんたーていめんと','','','','1','1'),
	'06_01'=>array('アート・ＣＧ','あーと・CG','','','','',''),
	'06_02'=>array('アニメ','あにめ','','','','',''),
	'06_03'=>array('映画','えいが','','','','1',''),
	'06_04'=>array('オンライン小説','おんらいんしょうせつ','','','','',''),
	'06_05'=>array('ギャンブル','ぎゃんぶる','','','','',''),
	'06_05_01'=>array('競馬','けいば','','','','',''),
	'06_06'=>array('芸能人・タレント','げいのうじん・たれんと','','','','1',''),
	'06_07'=>array('ユーモア','ゆーもあ','','','','1',''),
	'06_08'=>array('その他','ソノタ','','','','',''),

	'07'=>array('ゲーム','げーむ','','','','1','1'),
	'07_01'=>array('総合情報','01_そうごうじょうほう','','','','1',''),
	'07_02'=>array('TVゲーム','TVげーむ','','','','1',''),
	'07_03'=>array('アーケードゲーム','あーけいどげーむ','','','','',''),
	'07_04'=>array('オンラインゲーム','おんらいんげーむ','','','','',''),
	'07_05'=>array('パソコンゲーム','ぱそこんげーむ','','','','1',''),
	'07_06'=>array('その他','ソノタ','','','','',''),

	'08'=>array('音楽','おんがく','','10_03&02_03','','1','1'),
	'08_01'=>array('総合情報','01_そうごうじょうほう','','','','1',''),
	'08_02'=>array('MIDI','MIDI','','','','',''),
	'08_03'=>array('アーティスト','あーてぃすと','','','','1',''),
	'08_04'=>array('音楽配信','おんがくはいしん','','','','1',''),
	'08_05'=>array('その他','ソノタ','','','','',''),

	'09'=>array('スポーツ','すぽーつ','','','','1','1'),
	'09_01'=>array('総合情報','01_そうごうじょうほう','','','','1',''),
	'09_02'=>array('サッカー','さっかー','','','','1',''),
	'09_03'=>array('野球','やきゅう','','','','1',''),
	'09_04'=>array('その他','ソノタ','','','','',''),

	'10'=>array('ショッピング','しょっぴんぐ','','','1','1','1'),
	'10_01'=>array('ショッピングモール','01_しょっぴんぐもーる','','','','1',''),
	'10_02'=>array('オークション','おーくしょん','','','','1',''),
	'10_03'=>array('家電製品','かでんせいひん','','','','',''),
	'10_04'=>array('雑貨','ざっか','','','','',''),
	'10_05'=>array('フード・ドリンク','ふーど・どりんく','','','','',''),
	'10_06'=>array('ファッション','ふぁっしょん','','','','',''),
	'10_07'=>array('フリーマーケット','ふりーまーけっと','','','','1',''),
	'10_08'=>array('ホビー','ほびー','','','','',''),
	'10_09'=>array('その他','ソノタ','','','','',''),
	'10_10'=>array('本・CD・ビデオ','本・CD・びでお','','','','',''),

	'11'=>array('ニュース・マスコミ','にゅーす・ますこみ','','','','1','1'),
	'11_01'=>array('TV','TV','','','','1',''),
	'11_02'=>array('インターネット','いんたーねっと','','','','1',''),
	'11_03'=>array('ラジオ','らじお','','','','1',''),
	'11_04'=>array('その他','ソノタ','','','','',''),

	'12'=>array('健康・医療','けんこう・いりょう','','','1','1','1'),
	'12_01'=>array('総合情報','01_そうごうじょうほう','','','','1',''),
	'12_02'=>array('治療法','ちりょうほう','','','','1',''),
	'12_03'=>array('メンタルヘルス','めんたるへるす','','','','1',''),
	'12_04'=>array('その他','ソノタ','','','','',''),

	'13'=>array('旅行・地域情報','りょこう・ちいきじょうほう','','','','1','1'),
	'13_01'=>array('海外','01_かいがい','','','','1',''),
	'13_02'=>array('北海道','02_ほっかいどう','','','','1',''),
	'13_03'=>array('東北','03_とうほく','','','','1',''),
	'13_04'=>array('信越','04_しんえつ','','','','',''),
	'13_05'=>array('関東','05_かんとう','','','','',''),
	'13_06'=>array('東海','06_とうかい','','','','',''),
	'13_07'=>array('北陸','07_ほくりく','','','','',''),
	'13_08'=>array('近畿','08_きんき','','','','',''),
	'13_09'=>array('中国','09_ちゅうごく','','','','',''),
	'13_10'=>array('四国','10_しこく','','','','',''),
	'13_11'=>array('九州','11_きゅうしゅう','','','','',''),
	'13_12'=>array('沖縄','12_おきなわ','','','','',''),
	
	'14'=>array('教育','きょういく','','','','1','1'),
	'14_01'=>array('総合情報','01_そうごうじょうほう','','','','1',''),
	'14_02'=>array('学校','がっこう','','','','1',''),
	'14_03'=>array('資格','しかく','','','','1',''),
	'14_04'=>array('その他','ソノタ','','','','','')
);

// ---- 登録処理関係の設定 ----//
/* ##################### 
 * 記入必須はFxxx=☆/制限文字数はMxxx=文字数
 * カテゴリ選択上限=kt_max/選択下限=kt_min/二重URL登録=nijyu_url(可能=0/不可=1)
 * 禁止ワード=kt_no_word/管理人のみが登録できるモード=no_regist(ON=1/OFF=0)
 * 管理人のみが修正・削除できるモード=no_mente(ON=1/OFF=0)
 * バナーURL登録項目=bana_url(ON=1/OFF=0)
 * 追加希望カテゴリ項目=add_kt(ON=1/OFF=0)/管理人へのコメント項目=to_admin(ON=1/OFF=0)
 * 新規登録時の相互リンク連絡項目=sougo(ON=1/OFF=0)
 * 登録者のメッセージ=look_mes(見る=1/見ない=0)
######################### */
$Setup_Config['register'] = array(
	'Fadd_kt'    => '',
	 'Fbana_url'  => '',
	 'Femail'     => '☆',
	 'Fkey'       => '',
	 'Fname'      => '☆',
	 'Fsyoukai'   => '☆',
	 'Ftitle'     => '☆',
	 'Fto_admin'  => '',
	 'Furl'       => '☆',
	 'Madd_kt'    => '50',
	 'Mbana_h'    => '31',
	 'Mbana_url'  => '130',
	 'Mbana_w'    => '88',
	 'Memail'     => '40',
	 'Mkey'       => '30',
	 'Mname'      => '25',
	 'Msyoukai'   => '100',
	 'Mtitle'     => '25',
	 'Mto_admin'  => '200',
	 'Murl'       => '130',
	 'add_kt'     => '1',
	 'bana_url'   => '1',
	 'kt_max'     => '2',
	 'kt_min'     => '1',
	 'kt_select_mode'=>'multiple',
	 'kt_no_word' => '',
	 'look_mes'   => '30m',
	 'nijyu_url'  => '1',
	 'no_mente'   => '0',
	 'no_regist'  => '0',
	 'sougo'      => '0',
	 'to_admin'   => '1'
);


// カテゴリ別の説明表示$KTEX
$Setup_Config['description'] = array(
              'new_ys'   => '14日以内に登録されたサイト',
              'rank'     => '人気ランキングをベスト100位まで紹介しています',
              'rank_bf'  => '前回の人気ランキングをベスト100位まで紹介しています',
              'rank_rui' => '累計人気ランキングをベスト100位まで紹介しています',
              'renew_ys' => '14日以内に更新されたサイト',
              'rev'      => 'アクセスランキングをベスト100位まで紹介しています',
              'rev_bf'   => '前回のアクセスランキングをベスト100位まで紹介しています',
              'rev_rui'  => '累計アクセスランキングをベスト100位まで紹介しています'
        );


// ---- 検索フォームの設定 $serch_form ----//
$Setup_Config['search_form'] = <<< EOD
<option value="pre" selected>{$Setup_Config['common']['search_name']}</option>
<option value="yahoo">Yahoo!</option>
<option value="google">Google</option>
<option value="bing">bing</option>
<option value="infoseek">Infoseek</option>
<option value="goo">goo</option>
<option value="excite">Excite</option>
<option value="FRESHEYE">フレッシュアイ</option>
<option value="">-----------------</option>
<option value="vector">Vector</option>
<option value="">-----------------</option>
<option value="rakuten">楽天市場</option>
<option value="hmv_a">HMV(アーティスト名検索)</option>
<option value="hmv_t">HMV(タイトル名検索)</option>
<option value="bk1">bk1</option>
<option value="">-----------------</option>
<option value="com">.com で</option>
<option value="cojp">.co.jp で</option>
<option value="jp">.jp で</option>
<option value="net">.netで</option>
<option value="info">.infoで</option>
<option value="org">.orgで</option>
<option value="movi">.moviで</option>
EOD;


// ---- メニューバーの設定 ----//
$Setup_Config['menu_bar'] = '
<a href="$index?mode=new">新着サイト</a> -
<a href="$index?mode=renew">更新サイト</a> -
<a href="$rank">人気ランキング</a> -
<a href="$rank?mode=keyrank">キーワードランキング</a> -
<a href="$index?mode=m1">おすすめサイト</a> -
<a href="$index?mode=m2">相互リンクサイト</a> -
<a href="$index?mode=mylink">マイリンク</a>
';


/* ######################################################## */

$Form = Form::Get_Data();
if(isset($Form['path']) and preg_match("/([^0-9\/]+)/",$Form['path'])){mes('選択したカテゴリが不明です','エラー','java');}

/* Page Type (Caria) */
$Page_Type = "";
$caller_file = debug_backtrace();
$Page_Type = Get_Caller($caller_file);

switch($Page_Type){
	case "pc":
		$path_set['template']['root'] = $path_set['template']['pc'];
		$script = array_merge($script,$script['pc']);
	break;
	case "phone":
		$path_set['template']['root'] = $path_set['template']['phone'];
		$script = array_merge($script,$script['phone']);
	break;
}

//
function Get_Caller($Caller){
	global $path_set;
	$return = "";
	$caller_dir = "";
	if(is_array($Caller) and isset($Caller[0]['file'])){
		$caller_dir = dirname($Caller[0]['file']);
	}
	$caller_dir = str_replace("\\","/",$caller_dir);
	if($caller_dir == str_replace("\\","/",$path_set['phone'])){
		$return = "phone";
	}else{
		$return = "pc";
	}
	return $return;
}

/* # - Form Post Data - # */
class Form{
	
	public static function Get_Data(){
		global $system_set;
		$queries = "";
		$Form = array();
		if($_GET){$queries = $_GET;}
		if($_POST){
			if(!is_array($queries)){$queries = $_POST;}
			else{$queries = array_merge($queries,$_POST);}
		}
	//	if(is_array($queries)){mb_convert_variables($code_set['system'],$code_set['list'],$queries);}
		if($queries){$Form = Form::Decode($queries,$system_set['charset'],$system_set['charlist']);}
			
		return $Form;
	}

	protected static function Decode($Array,$Charset,$Char_List){
		$return = array();
		foreach ($Array as $key => $value) {
			if (is_array($value)) {
				$value = Form::Decode($value,$Charset,$Char_List);
			} else {
				$value = mb_convert_encoding($value,$Charset,$Char_List);
				$value = htmlspecialchars($value,ENT_XHTML and ENT_QUOTES,$Charset);
			}
			$return[$key] = $value;
		}
		return $return;
	}
}

/* # - Setting Control -# */
class FullPath_Settings{
	
	public static function File($File_List,$Root){
		$return = array();
		if(is_array($File_List)){
			foreach($File_List as $key=>$value){
				if(is_array($value)){
					if(is_array($Root)){
						if(isset($Root[$key])){
							$value = FullPath_Settings::File($value,$Root[$key]);
						}elseif(isset($Root['root'])){
							$value = FullPath_Settings::File($value,$Root['root']);
						}
					}else{
						$value = FullPath_Settings::File($value,$Root);
					}
				}else{
					if(is_array($Root)){
						if(isset($Root[$key])){
							$value = preg_replace("/(.+)/",$Root[$key]."$1",$value);
						}elseif(isset($Root['root'])){
							$value = preg_replace("/(.+)/",$Root['root']."$1",$value);
						}
					}else{
						$value = $Root . $value;
					}
				}
				$return[$key] = $value;
			}
		}
		return $return;
	}

	/* - Path Root Setting - */
	public static function Directory($Path_List){
		$return = array();
		if(is_array($Path_List)){
			foreach($Path_List as $key=>$value){
				if(is_array($value)){
					if(isset($value['root'])){$value['root'] = $Path_List['root'] . $value['root'];}
					$value = FullPath_Settings::Directory($value);
				}else{
					if($key != "root"){
						if(isset($Path_List['root'])){$value =  $Path_List['root']. $value;}
					}
				}
				$return[$key] = $value;
			}
		}
		return $return;
	}
}

//var_dump($script);

?>