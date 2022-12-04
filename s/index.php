<?php
/***********************************************/
/* yomi-search PHP modified1.8.5n  iphone版
/* 2010-10-24 nkbt make
/*
/***********************************************/
require 'initial.php';
if($cfg['count']) {
	require $cfg['sp_sub_path'] . "count_ys.php";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $cfg['sp_search_name'] ?></title>
<meta name="keywords" content="<?php echo $cfg['ver']; ?>,検索エンジン" />
<meta name="description" content="<?php echo $cfg['ver']; ?>の検索エンジンのトップページ" />
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="index,follow" name="robots" />
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Cache-Control" content="no-cache">
<meta http-equiv="Expires" content="Thu, 01 Dec 1994 16:00:00 GMT">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />

<link rel="stylesheet" href="<?php echo $cfg['sp_path_url']; ?>js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.css" />
<script src="<?php echo $cfg['sp_path_url']; ?>js/jquery-2.2.4.min.js"></script>
<script src="<?php echo $cfg['sp_path_url']; ?>js/jquery.mobile-1.4.5/jquery.mobile-1.4.5.min.js"></script>
<style>
.clearfix:after {
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}
.clearfix {
	display: inline-block;
}
/* Hides from IE-mac \*/
* html .clearfix {
	height: 1%;
}
.clearfix {
	display: block;
}
/* End hide from IE-mac */

.block {
    margin-bottom:-13px;
}

.block div {
    margin-right: 1px; 
	float: left; 
}
</style>


<script type="text/javascript">
$(document).bind("mobileinit", function(){
  $.extend(  $.mobile , {
    ajaxLinksEnabled : false
  });
});
function doScroll() { if (window.pageYOffset === 0) { window.scrollTo(0,1); } }
window.onload = function() { setTimeout(doScroll, 100); }

</script>
</head>

<body>
<!-- Start page -->
<div data-role="page" data-cache="never">

        <!-- header -->
	<div data-role="header"  data-theme="b">
		<h1><?php echo $cfg['sp_search_name']; ?></h1>
		<a href="./search.php"  rel="external" data-icon="arrow-r" class="ui-btn-right" data-transition="slide">検索</a>
	</div>
        <!-- /header -->

	<div data-role="content">
		<?php echo '<span style="font-size:10px;">'.$counter.'</span>'; ?>

		<ul data-role="listview" data-inset="true" data-theme="b" data-dividertheme="a">
			<li data-role="list-divider">メニュー</li>
            <li><a href="index.php?mode=new" rel="external">新着サイト<span class="arrow"></span></a></li>
            <li><a href="index.php?mode=renew"  rel="external">更新サイト<span class="arrow"></span></a></li>
            <li><a href="rank.php"  rel="external">人気ランキング<span class="arrow"></span></a></li>
            <li><a href="rank.php?mode=keyrank"  rel="external">キーワードランキング<span class="arrow"></span></a></li>
            <li><a href="rank.php?mode=m1"  rel="external">おすすめサイト<span class="arrow"></span></a></li>
		</ul>
		
		
		<ul data-role="listview" data-inset="true" data-theme="b" data-dividertheme="a">
			<li data-role="list-divider">カテゴリ</li>
            <?php
            //1番目の行フラグ
            $i = 0;

            //最上層カテゴリ
            $top_category = getBigCategory($db);
            foreach($top_category as $row){
    //            $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
                echo '<li>';
            ?>
                <a href="index.php?mode=dir&amp;path=<?php echo trim($row['path'], '/'); ?>" rel="external"><?php echo $row['title']; ?>
                <p style="margin-top:4px;">
            <?php
                //サブカテゴリ
                $top_path = $row['path'];
                $sublist_flag = 0;
                $sub_category = getSubCategory($db, $top_path);
                $w = '';
                foreach ($sub_category as $row){
                    if($row['top_list']){
                        $sublist_flag = 1;
    //                    $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
                        $w .= $row['title'].',';
                    }
                }
                $w = trim($w, ',');
                if($sublist_flag) {
                    $w .= '‥';
                }
                $w .= '</p></a></li>'."\n";
                $i++;
                echo $w;
            }
            ?>
		</ul>
		
		
	<ul data-role="listview" data-inset="true" data-theme="b" data-dividertheme="a">
            <li data-role="list-divider">その他</li>
            <li><a href="regist.php" rel="external">サイト登録<span class="arrow"></span></a></li>
            <li><a href="help.php"  rel="external">ヘルプ<span class="arrow"></span></a></li>
            <li><a href="sitemap.php"  rel="external">サイトマップ<span class="arrow"></span></a></li>
        </ul>
		
		
		
	</div><!-- /content -->
        <?php cr();?>
</div><!-- /page -->
</body>

</html>


