<?php
/***********************************************/
/* yomi-search PHP modified1.8.5n  iphone版
/* 2010-10-24 nkbt make
/*
/***********************************************/
require 'initial.php';
if($cfg['count']) {
	require $cfg['sub_path'] . "count_ys.php";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo stripcslashes($cfg['search_name']); ?></title>
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
<link href="./images/yomi_icon.jpg" rel="apple-touch-icon" />
<meta content="minimum-scale=1.0, width=device-width, maximum-scale=0.6667, user-scalable=no" name="viewport" />

<link rel="stylesheet" href="<?php echo SMARTPHONE_HOME; ?>jquery.mobile-1.0a2/jquery.mobile-1.0a2.min.css" />
<script src="<?php echo SMARTPHONE_HOME; ?>js/jquery-1.4.4.min.js"></script>
<script src="<?php echo SMARTPHONE_HOME; ?>jquery.mobile-1.0a2/jquery.mobile-1.0a2.min.js"></script>
<script src="./js/jquery.socialbutton-1.7.1.js" type="text/javascript"></script>
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

$(function() {

	$('#hatena').socialbutton('hatena');

	$('#gree').socialbutton('gree_sf', {
		button: 0
	});

	$('#twitter').socialbutton('twitter', {
		button: 'horizontal',
        text: 'Yomi-search スマートフォン版',
        via: 'NKBT'
	});

	$('#facebook_like').socialbutton('facebook_like', {
		button: 'button_count',
	});

	$('#facebook_share').socialbutton('facebook_share', {
        text: 'ｼｪｱ'
    });

});


</script>
</head>

<body>
<!-- Start page -->
<div data-role="page" data-cache="never">

        <!-- header -->
	<div data-role="header"  data-theme="b">
		<h1><?php echo $cfg['search_name']; ?></h1>
		<a href="./search.php"  rel="external" data-icon="arrow-r" class="ui-btn-right" data-transition="slide">検索</a>
	</div>
        <!-- /header -->

	<div data-role="content">
		<?php echo '<span style="font-size:10px;">'.$counter.'</span>'; ?>
<div class="block clearfix">
			<div id="hatena"></div>
        	<div id="gree"></div>
        	<div id="facebook_share"></div>
        	<div id="facebook_like"></div>
</div>

		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="a">
			<li data-role="list-divider">メニュー</li>
            <li><a href="index.php?mode=new" rel="external">新着サイト<span class="arrow"></span></a></li>
            <li><a href="index.php?mode=renew"  rel="external">更新サイト<span class="arrow"></span></a></li>
            <li><a href="rank.php"  rel="external">人気ランキング<span class="arrow"></span></a></li>
            <li><a href="rank.php?mode=keyrank"  rel="external">キーワードランキング<span class="arrow"></span></a></li>
            <li><a href="rank.php?mode=m1"  rel="external">おすすめサイト<span class="arrow"></span></a></li>
		</ul>
		
		
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="a">
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
		
		
	<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="a">
            <li data-role="list-divider">その他</li>
            <li><a href="regist.php" rel="external">サイト登録<span class="arrow"></span></a></li>
            <li><a href="help.php"  rel="external">ヘルプ<span class="arrow"></span></a></li>
            <li><a href="sitemap.php"  rel="external">サイトマップ<span class="arrow"></span></a></li>
        </ul>
		
		
		
	</div><!-- /content -->
        <?php echo SMARTPHONE_FOOTER; ?>
</div><!-- /page -->
</body>

</html>


