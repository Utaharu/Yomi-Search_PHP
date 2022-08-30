<?php
/***********************************************/
/* yomi-search PHP modified1.8.5n  iphone版
/* 2010-10-24 nkbt make
/*
/***********************************************/
require 'initial.php';
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

<link rel="stylesheet" href="jquery.mobile-1.0a2/jquery.mobile-1.0a2.min.css" />
<script src="js/jquery-1.4.4.min.js"></script>
<script src="jquery.mobile-1.0a2/jquery.mobile-1.0a2.min.js"></script>




<script type="text/javascript">
function doScroll() { if (window.pageYOffset === 0) { window.scrollTo(0,1); } }
window.onload = function() { setTimeout(doScroll, 100); }
</script>
</head>

<body>

<!-- Start of first page -->
<div data-role="page" data-theme="b">

	<div data-role="header" data-theme="b">
        <a href="<?php echo $cfg['home']; ?>" rel="external" data-icon="arrow-l" class="ui-btn-left" data-transition="slide">TOP</a>
		<h1><?php echo 'サイト検索'; ?></h1>
	</div><!-- /header -->
	<div data-role="content"  data-theme="c">	
	<form method="GET" action="./search.php" rel="external">
            <input type="hidden" name="mode" value="search">
		<p>サイト名を入れて検索してください。</p>
        <input  type="search" name="word" id="name" value="<?php if(isset($word)){echo $word;} ?>"  /><br />
        <input type="submit" data-role="button" data-theme="b" data-inline="true" value="サイト検索">
	</form>
	<h5>詳細検索は<a href="./search_ex.php" rel="external">コチラ</a></h5>
	</div><!-- /content -->

        <?php echo SMARTPHONE_FOOTER; ?>
</div><!-- /page -->

</html>



