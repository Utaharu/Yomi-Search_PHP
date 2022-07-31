<?php
/**
 * sitemap mobile版
 */
require 'mobile_initial.php';
?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1994 16:00:00 GMT">
<title>サイトマップ | <?php echo $cfg['search_name']; ?></title>
<meta name="keywords" content="<?php echo $title; ?>,検索エンジン,サイトマップ" />
<meta name="description" content="<?php echo $title; ?>の検索エンジンサイトマップ表示です" />
<style type="text/css">
<![CDATA[
a:link{color:<?php echo LINK_COLOR; ?>;}
a:focus{color:<?php echo ALINK_COLOR; ?>;}
a:visited{color:<?php echo VLINK_COLOR; ?>;}
]]>
</style>
<body><div style="font-size:x-small;">


<div style="background-color:<?php echo HR_COLOR; ?>">
<img src="./img/spacer.gif" width="1" height="1" /><br /></div>
<div style="text-align:center; background-color:<?php echo ONE_BACK_COLOR; ?>; color:<?php echo ONE_STR_COLOR; ?>;" align="center">
<?php echo $cfg['search_name']; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<div style="text-align:center; background-color:<?php echo TWO_BACK_COLOR; ?>; color:<?php echo TWO_STR_COLOR; ?>;" align="center">
[<a href="index.php?mode=new">新着ｻｲﾄ</a>|<a href="index.php?mode=rank">ﾗﾝｷﾝｸﾞ</a>|<a href="regist.php">ｻｲﾄ登録</a>]<br />
</div>
<div style="background-color:<?php echo HR_COLOR; ?>">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>

<!--上部メニュー-->
<div style="text-align:center; background-color:<?php echo THREE_BACK_COLOR; ?>; color:<?php echo THREE_STR_COLOR; ?>;" align="center">
<a href="<?php echo $cfg['home']; ?>">ﾎｰﾑ</a>&nbsp;&gt;<?php if($navi != '') echo $navi.'<br />'; ?>ｻｲﾄﾏｯﾌﾟ
<div style="background-color:<?php echo HR_COLOR; ?>">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
</div>
<div style="font-size:x-small;">
  <form action="<?php echo $cfg['search']; ?>" method="get" name="form1">
	<input type="hidden" name="mode" value="search">
	<input type="hidden" name="page" value="1">
	<input type="hidden" name="sort" value="<?php echo $_POST['sort']; ?>">
	<input type="hidden" name="open_type" value="0">
	<input type="hidden" name="hyouji" value="30">
	<input type="text" name="word" class="form" value="" size="20">
	&nbsp;
	<select name="method" class="form">
		<option value="and" selected>AND</option>
		<option value="or">OR</option>
	</select>
	<input type="submit" class="form" value="検索">
  </form>
<?php
$query = 'SELECT up_id, title, path, comment FROM '.$db->db_pre.'category ORDER BY path;';
$top_category = $db->rowset_assoc($query);
$str = '';
foreach($top_category as $row){
        $row['title'] = mb_convert_kana($row['title'],'ka', 'UTF-8');
	if(!$row['up_id']) { // 最上層カテゴリ
                if($str != '') $str .= '<br />';
		$str .= '■<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'</a><br />'."\n";
	} else {
		$str .= '<a href="'.$cfg['script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'].'</a>|'."\n";
	}
}
echo $str;
?>
</div>

<!-- Footer Space Output -->
<?php if(isset($text['foot_sp'])) { echo $text['foot_sp']; } ?>
<!-- /Footer Space Output -->

<!-- Copy Right Output -->
<div style="text-align:center; background-color:<?php echo THREE_BACK_COLOR; ?>; color:<?php echo THREE_STR_COLOR; ?>;" align="center">
<a href="<?php echo $cfg['home']; ?>">ﾎｰﾑ</a>&nbsp;&gt;<?php if($navi != '') echo $navi.'<br />'; ?>ｻｲﾄﾏｯﾌﾟ
<div style="background-color:<?php echo HR_COLOR; ?>">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
</div>
<div style="text-align:center;">
<?php mcr(); ?>
<br />
</div>

</div>
<!-- /Copy Right Output -->

</body>
</html>