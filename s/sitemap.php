<?php
require 'initial.php';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>サイトマップ | <?php echo $cfg['sp_search_name'] ?></title>
<meta name="keywords" content="<?php echo $cfg['ver']; ?>,検索エンジン,<?php echo $cfg['ver']; ?>のサイトマップ" />
<meta name="description" content="<?php echo $cfg['ver']; ?>の検索エンジンのサイトマップです" />
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1994 16:00:00 GMT">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
<link href="./images/yomi_icon.jpg" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />

<link rel="stylesheet" href="./js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css" />
<script src="./js/jquery-2.2.4.min.js"></script>
<script src="./js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.js"></script>

<script type="text/javascript">
function doScroll() { if (window.pageYOffset === 0) { window.scrollTo(0,1); } }
window.onload = function() {
 setTimeout(doScroll, 100);
}
</script>
</head>

<body>
<!-- Start page -->
<div data-role="page"  data-cache="never">

    <!-- header -->
    <div data-role="header"  data-theme="b">
        <h1>ｻｲﾄﾏｯﾌﾟ</h1>
        <a href="<?php echo $cfg['sp_home']; ?>" data-icon="arrow-l" class="ui-btn-left"  rel="external" data-transition="slide">TOP</a>
        <a href="search.php" data-icon="arrow-r" class="ui-btn-right" data-transition="slide"  rel="external">検索</a>
    </div>
    <!-- /header -->


   <div data-role="content">
<!-- Menu Bar Output -->
        <div style="text-align:center;"><?php printTopMenuLink( $text['menu_bar'] ); ?></div>


<!-- Navigation Bar Output -->
<a href="<?php echo $cfg['sp_home']; ?>" rel="external">ホーム</a>&nbsp;&gt;&nbsp;<?php if(isset($navi)){echo $navi;} ?><strong>ｻｲﾄﾏｯﾌﾟ</strong>
<!-- /Navigation Bar Output -->

<?php
$query = 'SELECT up_id, title, path, comment FROM '.$db->db_pre.'category ORDER BY path;';
$top_category = $db->rowset_assoc($query);
$n=0;
foreach($top_category as $row){
    if(!$row['up_id']) { // 最上層カテゴリ
            if($n>0) echo '</ul>';
            echo '<ul data-role="listview" data-inset="true"  data-dividertheme="e">'."\n";
            echo '<li data-role="list-divider" data-theme="b"><a href="'.$cfg['sp_script'].'?mode=dir&amp;path='.$row['path'].'" style="color:white;">'.$row['title'].'</a></li>'."\n";
            $n++;
    } else {
//		echo str_repeat("&nbsp;", strlen($row['path']));

        echo '<li data-theme="a"><a href="'.$cfg['sp_script'].'?mode=dir&amp;path='.$row['path'].'">'.$row['title'];
        if($row['comment']) {
             echo '<p style="margin-top:4px;">'.$row['comment'].'</p>';
        }
        echo '</a></li>'."\n";
   }
}
?>
</ul>

<div align="center"><form><input type="button" value="&nbsp;&nbsp;&nbsp;&nbsp;戻る&nbsp;&nbsp;&nbsp;&nbsp;" onClick="history.back()"></form></div>


<!-- Footer Space Output -->
<?php if(isset($text['foot_sp'])) { echo $text['foot_sp']; } ?>
</div>
<!-- /content -->

<!-- /Footer Space Output -->
<?php cr(); ?>
</body>
</html>