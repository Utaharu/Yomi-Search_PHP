<?php
// エラーレポート設定
require 'config4debug.php';
if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}

// 言語設定
mb_internal_encoding('UTF-8');
mb_language('ja');

// インクルード
require 'class/db.php';
require 'functions.php';
require 'ads.php';

// dbクラスをインスタンス化
// コンストラクタでデータベースに接続
$db = new db();

//local変数に
$db_pre = $db->db_pre;

// [SQL-SET-NAMES]設定
$db->sql_setnames();

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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1994 16:00:00 GMT">
<title>サイトマップ | <?php echo $cfg['search_name']; ?></title>
<meta name="keywords" content="<?php echo $title; ?>,検索エンジン,サイトマップ" />
<meta name="description" content="<?php echo $title; ?>の検索エンジンサイトマップ表示です" />



<link media="screen" rel="stylesheet" href="css/main.css" type="text/css">
</head>
<body><a name="top"></a>
<!-- Menu Bar Output -->
<table width="100%" class="table_menu_bar">
    <tr>
        <td style="text-align:center;">
        <?php printHeaderAd(); ?>
        </td>
    </tr>
    <tr>
    <td><?php echo $text['menu_bar']; ?></td>
  </tr>
</table>
<!-- /Menu Bar Output -->
<!-- Header Space Output -->
<?php echo $text['head_sp']; ?>
<!-- /Header Space Output -->
<!-- Navigation Bar Output -->
<table width="100%" class="table_navigation_bar">
  <tr>
    <td><a href="<?php echo $cfg['home']; ?>">ホーム</a>&nbsp;&gt;&nbsp;<?php echo $navi; ?><strong>サイトマップ</strong></td>
  </tr>
</table>
<!-- /Navigation Bar Output -->
<!-- ページ中段の検索フォーム -->
<table width="100%" class="table_searchform">
  <form action="<?php echo $cfg['search']; ?>" method="get" name="form1">
  <tr>
    <td align="center">
	<input type="hidden" name="mode" value="search">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="sort" value="<?php echo $_POST['sort']; ?>">
	<input type="hidden" name="open_type" value="0">
	<input type="hidden" name="hyouji" value="30">
	<input type="text" name="word" class="form" value="" size="20">
	<input type="submit" class="form" value="検索">
	&nbsp;
	<select name="method" class="form">
		<option value="and" selected>AND</option>
		<option value="or">OR</option>
	</select>
    </td>
  </tr>
  </form>
</table>
<ul>
    <?php if(getRightAdFlg()) { ?>
    <li style="float:right;margin-right:20%;list-style-type:none;">
     <?php printRightAd(); ?>
    </li>
    <?php } ?>

<table cellpadding="3" border="0">
<?php
$query = 'SELECT up_id, title, path, comment FROM '.$db->db_pre.'category ORDER BY path;';
$top_category = $db->rowset_assoc($query);
foreach($top_category as $row){
	echo "<tr valign=\"bottom\" nowrap>\n";
	echo "  <td nowrap>\n";
	if(!$row['up_id']) { // 最上層カテゴリ
		echo '<br><br>'."\n";
		echo "&nbsp;&nbsp;<font size=\"+1\">■</font><a href=\"{$cfg['script']}?mode=dir&amp;path={$row['path']}\"><font size=\"+1\"><b>{$row['title']}</b></font></a>\n";
	} else {
		echo str_repeat("&nbsp;", strlen($row['path']));
		echo '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'</a>'."\n";
	}
	echo '</td><td nowrap>'.$row['comment'].'</td>'."\n";
	echo "</tr>\n";
}
?>
</table>
</ul>
<hr>
<!-- Footer Space Output -->
<?php if(isset($text['foot_sp'])) { echo $text['foot_sp']; } ?>
<!-- /Footer Space Output -->

<!-- Copy Right Output -->
<div align="center">
<?php cr(); ?>
<br />
<?php printFooterAd(); ?>
</div>
<!-- /Copy Right Output -->

</body>
</html>