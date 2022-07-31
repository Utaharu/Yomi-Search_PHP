<?php
/**
* モバイル版　設定ファイル
* 絵文字は画像は
* http://start.typepad.jp/typecast/
* を使用しています
*/

//区切り線の色
define('HR_COLOR', '#D60000');

//区切り線の色2
define('HR_COLOR2', '#CA82FA');


//タイトルの文字色
define('ONE_STR_COLOR', '#FEF4D6');

//タイトルの背景色
define('ONE_BACK_COLOR', '#FF0066');

//現在位置の文字色(ホーム＞xxxの部分)
define('TWO_STR_COLOR', '#FD0FE9');

//現在位置の背景色(ホーム＞xxxの部分)
define('TWO_BACK_COLOR', '#FFC3FA');

//3段目文字色
define('THREE_STR_COLOR', '#FF1F78');

//3段目背景
define('THREE_BACK_COLOR', '#FAE0FE');

//フッター文字色
define('FOOTER_STR_COLOR', '#FFFFFF');

//フッター背景
define('FOOTER_BACK_COLOR', '#FF0066');

//mobile版テンプレート置き場
define('MOBILE_TEMPLATE', './template/');

//リンク文字色カラーその1 link
define('LINK_COLOR', '#FF0000');

//リンク文字色カラーその2 alink
define('ALINK_COLOR', '#FF9A61');

//リンク文字色カラーその3 vlink
define('VLINK_COLOR', '#E80AE4');

//背景色1
define('BACK_COLOR1', '#F6E9FF');

//背景色2
define('BACK_COLOR2', '#F5F5FF');


//yomi-search PHP置き場(PC版のPHPファイルを使用します)
define('MOBILE_PHP_DIR', '../php/');

//SITE名
define('MOBILE_SITE_NAME', 'Yomi-search Mobile');

//画像置き場
define('MOBILE_IMG_PATH', './img/');
define('PC_IMG_PATH', '../img/');
define('EMOJI_IMG_PATH', MOBILE_IMG_PATH.'emoticons/');

//ホーム
define('MOBILE_HOME', 'index.php');

//絵文字設定
define('ONE', '<img src="'.EMOJI_IMG_PATH.'one.gif" width="16" height="16">');
define('TWO', '<img src="'.EMOJI_IMG_PATH.'two.gif" width="16" height="16">');
define('THREE', '<img src="'.EMOJI_IMG_PATH.'three.gif" width="16" height="16">');
define('FOUR', '<img src="'.EMOJI_IMG_PATH.'four.gif" width="16" height="16">');
define('FIVE', '<img src="'.EMOJI_IMG_PATH.'five.gif" width="16" height="16">');
define('SIX', '<img src="'.EMOJI_IMG_PATH.'six.gif" width="16" height="16">');
define('SEVEN', '<img src="'.EMOJI_IMG_PATH.'seven.gif" width="16" height="16">');
define('EIGHT', '<img src="'.EMOJI_IMG_PATH.'eight.gif" width="16" height="16">');
define('NINE', '<img src="'.EMOJI_IMG_PATH.'nine.gif" width="16" height="16">');
define('ZERO', '<img src="'.EMOJI_IMG_PATH.'zero.gif" width="16" height="16">');
define('SHARP', '<img src="'.EMOJI_IMG_PATH.'sharp.gif" width="16" height="16">');
define('KOUMOKU', '<img src="'.EMOJI_IMG_PATH.'koumoku.gif" width="16" height="16">');

//数字絵文字配列20個0-9,#,項目
$EMOJI_NUM_ARRAY = array(ONE, TWO, THREE, FOUR,FIVE,SIX, SEVEN, EIGHT, NINE, ZERO, SHARP, KOUMOKU, KOUMOKU, KOUMOKU,  KOUMOKU, KOUMOKU, KOUMOKU,  KOUMOKU, KOUMOKU, KOUMOKU);

//アクセスキー配列
$ACCESSKEY_ARRAY = array('accesskey="1"', 'accesskey="2"', 'accesskey="3"', 'accesskey="4"', 'accesskey="5"', 'accesskey="6"', 'accesskey="7"', 'accesskey="8"', 'accesskey="9"', 'accesskey="0"', 'accesskey="#"', '','','','','','','','','','','','','','','');

?>