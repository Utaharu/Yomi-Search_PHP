<?php
/**
* smartphone版　設定ファイル
* 絵文字は画像は
* http://start.typepad.jp/typecast/
* を使用しています
*/

//SMARTPHONE版名前
define('SMARTPHONE_SITE_NAME', 'Yomi-Search');

//SMARTPHONE版テンプレート置き場
define('SMARTPHONE_TEMPLATE', './template/');

//SMARTPHONE版PHP置き場
//(基本的にPC版のPHPを使ってテンプレートをスマートフォン版に置き換えるスタイルです)
define('SMARTPHONE_PHP_DIR', '../php/');

define('SMARTPHONE_IMG_PATH', '../img/');
define('SMARTPHONE_EMOJI_IMG_PATH', '../m/img/emoticons/');

//SMARTPHONE版のINDEXのPATHを設定
define('SMARTPHONE_HOME', '');

//HOMEのファイル名
define('SMARTPHONE_INDEX', 'index.php');

//FOOTER部分のHTMLです
define('SMARTPHONE_FOOTER', '<div data-role="footer" data-theme="b"><h4 onClick="location.href=\'http://www.nkbt.net/yomi/\'" style="cursor: pointer; width:100%; text-algin:center; margin-left:0px;">Yomi-Search(PHP)modifiedver1.5.8.n</h4></div><!-- /header -->');

//FOOTER部分のHTMLです(FORM画面でjquery mobile js使うとフォームがうまく飛ばないのでしかたなく)
define('SMARTPHONE_FOOTER_NOJS', '<div data-theme="b" data-role="footer" class="ui-bar-b ui-footer" role="contentinfo"><h4 style="cursor: pointer; width: 100%; margin-left: 0px;" onclick="location.href=\'http://www.nkbt.net/yomi/\'" class="ui-title" tabindex="0" role="heading" aria-level="1">Yomi-Search(PHP)modifiedver1.5.8.n</h4></div>');




//絵文字設定
define('ONE', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'one.gif" width="16" height="16" border="0" class="title_img">');
define('TWO', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'two.gif" width="16" height="16" border="0" class="title_img">');
define('THREE', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'three.gif" width="16" height="16" border="0" class="title_img">');
define('FOUR', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'four.gif" width="16" height="16" border="0" class="title_img">');
define('FIVE', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'five.gif" width="16" height="16" border="0" class="title_img">');
define('SIX', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'six.gif" width="16" height="16" border="0" class="title_img">');
define('SEVEN', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'seven.gif" width="16" height="16" border="0" class="title_img">');
define('EIGHT', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'eight.gif" width="16" height="16" border="0" class="title_img">');
define('NINE', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'nine.gif" width="16" height="16" border="0" class="title_img">');
define('ZERO', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'zero.gif" width="16" height="16" border="0" class="title_img">');
define('SHARP', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'sharp.gif" width="16" height="16" border="0" class="title_img">');
define('KOUMOKU', '<img src="'.SMARTPHONE_EMOJI_IMG_PATH.'koumoku.gif" width="16" height="16" border="0" class="title_img">');

//数字絵文字配列20個0-9,#,項目
$EMOJI_NUM_ARRAY = array(ONE, TWO, THREE, FOUR,FIVE,SIX, SEVEN, EIGHT, NINE, ZERO, SHARP, KOUMOKU, KOUMOKU, KOUMOKU,  KOUMOKU, KOUMOKU, KOUMOKU,  KOUMOKU, KOUMOKU, KOUMOKU);

//アクセスキー配列
$ACCESSKEY_ARRAY = array('accesskey="1"', 'accesskey="2"', 'accesskey="3"', 'accesskey="4"', 'accesskey="5"', 'accesskey="6"', 'accesskey="7"', 'accesskey="8"', 'accesskey="9"', 'accesskey="0"', 'accesskey="#"', '','','','','','','','','','','','','','','');

?>