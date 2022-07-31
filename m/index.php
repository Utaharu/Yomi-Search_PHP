<?php
/**
* ガラケー版index.php
*/
include 'mobile_initial.php';

//データの読み込み＆下層カテゴリ表示
$check_mode_arr = array('dir', 'rank','new');

if (isset($_GET['mode']) && in_array($_GET['mode'], $check_mode_arr) && (is_numeric(str_replace('/', '', $_GET['path']))==true || $_GET['word'] != '' || $_GET['mode']=='rank' || $_GET['mode']=='new' )) {
    $word = '';
    $title = '';
    $guide = '';

    if($_GET['mode'] == 'new') {
        $title='新着ｻｲﾄ';
        $guide = '';
    } else if($_GET['mode'] == 'rank') {
        $title='人気ﾗﾝｷﾝｸﾞ';
        $guide = '';
    } else if($_GET['word'] != '') {
        if(! checkSQLWord($_GET['word'])) {
            $word = '';
            header('Location: ./index.php');
            exit();
        } else {
            $word = $_GET['word'];
        }
        $title = '検索ﾜｰﾄﾞ：'.$word;
        $guide = '検索結果';
    } else {
    
        //サブカテゴリデータの読み込み
        $query = 'SELECT title, regist, comment FROM '.$db_pre.'category WHERE path=\''.$_GET['path'].'\' LIMIT 1';
        $row = $db->single_assoc($query);
            
        $title = mb_convert_kana($row['title'],'ak','UTF-8');
        $guide = mb_convert_kana($row['comment'],'ak', 'UTF-8');
    }
    
    $log_lines = array(); //表示データリスト
    if (empty($_GET['page'])) $_GET['page'] = 1;
    $st_no = $cfg['hyouji'] * ($_GET['page'] - 1);
    $query =  'SELECT id,title,url,mark,last_time,passwd,message,comment,name,mail,category,stamp,banner,renew,ip,keywd FROM '.$db_pre.'log WHERE ';
    $where_query = '';
    $count_query = '';

    if($_GET['path'] != '') {
        $where_query .= 'category LIKE \'%&'.$_GET['path'].'&%\' ';
    } else {
        $where_query  .= ' keywd IS NOT NULL ';
    }

    //====================================//
    // 通常WORD検索
    //====================================//
    if($word != '') {
        //スペースで分割
        $word = str_replace('　', ' ', $word);
        $word = trim($word, ' ');
        $words = explode(' ', $word);
        //====================================//
        // ワード検索部分
        //====================================//
        if(count($words) >= 0) { // and検索
            foreach($words as $w) {
                $where_query .= ' AND (title LIKE \'%'.$w.'%\' OR message LIKE \'%'.$w.'%\' OR comment LIKE \'%'.$w.'%\' OR keywd LIKE \'%'.$w.'%\' OR url LIKE \'%'.$w.'%\')';
            }
        }
    } else if($_GET['mode'] == 'rank') {
        //====================================//
        // 人気ランキング表示
        //====================================//

        //前期間のランキングrank情報削除
        $cut = $time - $cfg['rank_kikan'] * 172800;
        $query = 'DELETE FROM '.$db->db_pre.'rank WHERE time<'.$cut;
        $db->query($query);

        //前期間のランキングrev情報削除
        $cut = $time - $cfg['rev_kikan'] * 172800;
        $query = 'DELETE FROM '.$db->db_pre.'rev WHERE time<'.$cut;
        $db->query($query);

        $db->query('set @rownum='.$st_no.';');
        $query = 'SELECT *,@rownum:=@rownum+1 as juni FROM '.$db_pre.'log AS l,'.$db_pre.'rank_counter AS t WHERE l.id=t.id AND t.rank >= '.$cfg['rank_min'].' ORDER BY t.rank DESC ';
        $where_query = '';
        $count_query = 'SELECT count(t.id) FROM '.$db_pre.'log AS l,'.$db_pre.'rank_counter AS t WHERE l.id=t.id AND t.rank >= '.$cfg['rank_min'].' ORDER BY t.rank DESC ';

    } else if($_GET['mode'] == 'new') {
        //====================================//
        // 新着サイト表示
        //====================================//
        $ntime = time() - $cfg['new_time'] * 24 * 3600;
        $query .= ' stamp > '.$ntime." AND renew = 0 ORDER BY char_length(replace(replace(mark,'_',''),'0','')) DESC, id DESC";
        $where_query = '';
        $count_query = 'SELECT count(id) FROM '.$db_pre.'log WHERE stamp > '.$ntime." AND renew = 0";
    }


    if($where_query != '') {
        $query .= $where_query.' ORDER BY id DESC';
    }
    
    $log_lines = $db->rowset_assoc_limit($query,$st_no,$cfg['hyouji']);

//    var_dump($log_lines);

    if($count_query != '') {
        $query = $count_query;
    } else {
        $query = 'SELECT COUNT(id) FROM '.$db_pre.'log WHERE '.$where_query;
    }
    $num = $db->single_num($query);
    
    //ナビゲーションバーを作成
    $navi = '';
    $navi_id = explode('/', substr($_GET['path'], 0, -1));
    array_pop($navi_id);
    $path = '';
    foreach ($navi_id as $tmp) {
        $path .= $tmp.'/';
        $query = 'SELECT title FROM '.$db_pre.'category WHERE path=\''.$path.'\'';
        $row = $db->single_assoc($query);
        $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
        $navi .= '<a href="index.php?mode=dir&amp;path='.$path.'">'.$row['title'].'</a> &gt; ';
    }
    
    
    
    
    //目次を作成
    $url = 'index.php';
    $bf_page = $_GET['page'] - 1;
    $af_page = $_GET['page'] + 1;
    $bf_url = $url.'?page=';
    $af_url = '&mode='.$_GET['mode'].'&path='.$_GET['path'];

    if($word != '') {
        $af_url .= '&word='.urlencode($word);
    }

    $end_no = $_GET['page'] * $cfg['hyouji'];
    $st_no = $end_no - $cfg['hyouji'] +1;
    if ($end_no >= $num[0]){
        $end_no=$num[0];
    }
    $max_page = (int)($num[0] / $cfg['hyouji']);
    if ($num[0] % $cfg['hyouji']){
        $max_page++;
    }
    $mokuji  = $st_no.'ﾍﾟｰｼﾞ中-'.$end_no.'ﾍﾟｰｼﾞ/全'.$num[0].'件';
    if ($num[0] > $cfg['hyouji']){ //目次作成
        $mokuji .= "[ ";
        if ($_GET['page'] > 1){
            $mokuji .= '<a href="'.$bf_url.$bf_page.$af_url.'">←前ﾍﾟｰｼﾞ</a> ';
        }
        $mokuji .= '/';
        
        $max_page_f = (int)($max_page / 10);
        if ($max_page % 10){
            $max_page_a = 1;
        } else {
            $max_page_a = 0;
        }
        $pre_page_f = (int)($_GET['page'] / 10);
        if ($_GET['page'] % 10){
            $pre_page_a = 1;
        } else {
            $pre_page_a = 0;
        }
        if ($max_page > 10 and $_GET['page'] > 10 and $pre_page_f > 0){
            $j = $pre_page_f * 10 - 19 + $pre_page_a * 10;
            $mokuji .= "<a href=\"$bf_url$j$af_url\">&lt;=</a> ";
        }
        if ($pre_page_a){
            $hyouji_page_st = $pre_page_f * 10 + 1;
        }
        else {
            $hyouji_page_st = $pre_page_f * 10 - 9;
        }
        $hyouji_page_end = $hyouji_page_st + 9;
        for ($i=1; $i <= $max_page; $i++){
            if ($hyouji_page_end < $i){
                break;
            }
            if ($hyouji_page_st <= $i){
                if ($i != $_GET['page']){
                    $j = $i;
                    $mokuji .= '<a href="'.$bf_url.$j.$af_url.'">'.$i.'</a> ';
                }
                else {
                    $mokuji .= '<b>'.$i.'</b> ';
                }
            }
        }
        #make =>
        if ($max_page_f - ($pre_page_f + $pre_page_a - 1) != 1 or $max_page_a){
            if ($max_page > 10 and $max_page > $_GET['page'] and $max_page_f > ($pre_page_f + $pre_page_a - 1)){
                $j = $pre_page_f * 10 + 1 + $pre_page_a * 10;
                $mokuji .= "<a href=\"$bf_url$j$af_url\">=&gt;</a> ";
            }
        }
        $mokuji .= '/';
        if ($_GET['page'] < $max_page){
            $mokuji .= "<a href=\"$bf_url$af_page$af_url\">次ﾍﾟｰｼﾞ→</a> ";
        }
        $mokuji .= ']';
    }
    ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $title; ?></title>
<style type="text/css">
<![CDATA[
a:link{color:<?php echo LINK_COLOR; ?>;}
a:focus{color:<?php echo ALINK_COLOR; ?>;}
a:visited{color:<?php echo VLINK_COLOR; ?>;}
]]>
</style>
</head>
<body><div style="font-size:x-small;">
<div style="text-align:center;" align="center">
<?php
require './google_ads.php';
?>
</div>
<a name=top></a>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /></div>
<div style="text-align:center; background-color:<?php echo ONE_BACK_COLOR; ?>; color:<?php echo ONE_STR_COLOR; ?>;" align="center">
<?php echo $cfg['search_name']; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<div style="text-align:left; background-color:<?php echo TWO_BACK_COLOR; ?>; color:<?php echo TWO_STR_COLOR; ?>;" align="left">
    <a href="index.php">ﾎｰﾑ</a> &gt; <?php echo $navi; ?> <?php echo $title; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<?php if($guide != '') { ?>
<div style="text-align:center; background-color:<?php echo THREE_BACK_COLOR; ?>; color:<?php echo THREE_STR_COLOR; ?>;" align="center">
<?php echo $guide; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<?php
}
?>

<?php
    //下層カテゴリ
    if($_GET['path']){
        $count_flag = 1;
        $query = 'SELECT id FROM '.$db_pre.'category WHERE path=\''.$_GET['path'].'\'';
        $id = $db->single_num($query);
        $query = 'SELECT title, path FROM '.$db_pre.'category WHERE up_id=\''.$id[0].'\' ORDER BY path';
        $rowset = $db->rowset_assoc($query);
        $z=0;
        foreach ($rowset as $row) {
            $query = 'SELECT COUNT(id) FROM '.$db_pre.'log WHERE category LIKE \'%&'.$row['path'].'%\'';
            $num = $db->single_num($query);
            $query = 'SELECT id FROM '.$db_pre.'category WHERE path LIKE \''.$row['path'].'_%\'';
            $sub = $db->single_num($query);
            $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
            
            $w = '';
            $w .= '<div style="background-color:';
            $w .= ($z%2)? BACK_COLOR1:BACK_COLOR2;
            $w .= ';"> ';
            $w .= $EMOJI_NUM_ARRAY[$i];
            $w .= '<a href="index.php?mode=dir&amp;path='.$row['path'].'" '.$ACCESSKEY_ARRAY[$z].' >'.$row['title'];
            echo $w;
            if($sub[0]) echo '*';
            echo '</a>';
            if($count_flag) echo '('.$num[0].')';
            //区切り線
            echo '</div><div style="background-color:'.HR_COLOR2.';"><img src="./img/spacer.gif" width="1" height="1" /><br /></div>';
            $z++;
        }
        $query = 'SELECT title, path FROM '.$db_pre.'category WHERE reffer LIKE \'%&'.$id[0].'&%\' ORDER BY path';
        $rowset = $db->rowset_assoc($query);
        foreach ($rowset as $row) {
            $id = substr($row['path'], 0, -1);
            $query = 'SELECT COUNT(id) FROM '.$db_pre.'log WHERE category LIKE \'%&'.$id.'%\'';
            $num = $db->single_num($query);
            $query = 'SELECT id FROM '.$db_pre.'category WHERE path LIKE \''.$id.'_%\'';
            $sub = $db->single_num($query);
            $row['title'] = mb_convert_kana($row['title'], "ak", "UTF-8");
            
            $w = '';
            $w .= '<div style="background-color:';
            $w .= ($z%2)? BACK_COLOR1:BACK_COLOR2;
            $w .= ';"> ';
            $w .= $EMOJI_NUM_ARRAY[$z].'<a href="index.php?mode=dir&amp;path='.$row['path'].'"  '.$ACCESSKEY_ARRAY[$z].'>'.$row['title'].'@</a>';
            if($count_flag) $w.='('.$num[0].')';
            
            //区切り線
            $w.= '</div><div style="background-color:'.HR_COLOR2.';"><img src="./img/spacer.gif" width="1" height="1" /><br /></div>';
            echo $w;
            $z++;
        }
    }
    $z=0;

    //====================================//
    // そのカテゴリに属するサイト表示
    //====================================//
    if($log_lines){

//目次を表示します
?>
<div style="text-align:left; background-color:<?php echo ONE_BACK_COLOR; ?>; color:<?php echo ONE_STR_COLOR; ?>; font-size:x-small;" align="left">
<?php echo $mokuji; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>

<?php
        
        foreach ($log_lines as $log_data){
            $log_data['title'] = mb_convert_kana($log_data['title'], 'ak', 'UTF-8');
            $log_data['message'] = mb_convert_kana($log_data['message'], 'ak', 'UTF-8');
            $jump_url = $log_data['url'];
            if($cfg['rank_fl']){
                $jump_url = urlencode($jump_url);
                $jump_url = $cfg['rank'].'?mode=link&id='.$log_data['id'].'&url='.$jump_url;
            }
            ?>
<?php if(isset($log_data['juni'])) echo $log_data['juni'].'位:'; ?><a href="<?php echo $jump_url; ?>"><?php echo str_replace('&amp;amp;', '&', $log_data['title']); ?></a><br />
<div style="font-size:x-small; color:#555555;"><?php if(isset($log_data['rank'])) echo '('.$log_data['rank'].'Pt)&nbsp;'; ?><?php echo $log_data['message']; ?></div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<?php
        }
?>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<div style="text-align:left; background-color:<?php echo ONE_BACK_COLOR; ?>; color:<?php echo ONE_STR_COLOR; ?>; font-size:x-small;" align="left">
<?php echo $mokuji; ?>
</div>
<?php
    } else if($word != '') {
       PR_meta_page(meta('meta_page', 'on'));
    }
?>

<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<div style="text-align:left; background-color:<?php echo TWO_BACK_COLOR; ?>; color:<?php echo TWO_STR_COLOR; ?>;" align="left">
    <a href="index.php">ﾎｰﾑ</a> &gt; <?php echo $navi; ?> <?php echo $title; ?>
</div>
<?php
    echo mcr().'</div></body></html>';
    $db->close();
    exit;
}


/********************************************************************************/
/* ↓携帯版TOP＋サブカテゴリ一覧表示
/********************************************************************************/
?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $cfg['search_name']; ?></title>
<style type="text/css">
<![CDATA[
a:link{color:<?php echo LINK_COLOR; ?>;}
a:focus{color:<?php echo ALINK_COLOR; ?>;}
a:visited{color:<?php echo VLINK_COLOR; ?>;}
]]>
</style>
</head>
<body><div style="font-size:x-small;">
<div style="text-align:center;" align="center">
<?php
require './google_ads.php';
?>
</div>
    <a name=top></a>

<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /></div>
<div style="text-align:center; background-color:<?php echo ONE_BACK_COLOR; ?>; color:<?php echo ONE_STR_COLOR; ?>;" align="center">
<?php echo $cfg['search_name']; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>
<div style="text-align:center; background-color:<?php echo TWO_BACK_COLOR; ?>; color:<?php echo TWO_STR_COLOR; ?>;" align="center">
 ﾎｰﾑ<br />
<?php echo $counter; ?>
</div>
<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>

<!--上部メニュー-->
<form method="GET" action="index.php">
<div style="text-align:center; background-color:<?php echo THREE_BACK_COLOR; ?>; color:<?php echo THREE_STR_COLOR; ?>;" align="center">
[<a href="index.php?mode=new">新着ｻｲﾄ</a>|<a href="index.php?mode=rank">ﾗﾝｷﾝｸﾞ</a>|<a href="regist.php">ｻｲﾄ登録</a>]<br />
<input type="text" name="word" size="15" style="width:160px;" value="" accesskey="1" /> <input type="submit" value="GO" />
<input type="hidden" name="mode" value="dir" />

<div style="background-color:<?php echo HR_COLOR; ?>;">
<img src="./img/spacer.gif" width="1" height="1" /><br />
</div>


</div>
</form>
<!--検索窓END-->


<!--上部メニューEND-->

<?php
//1番目の行フラグ
$i = 0;

//最上層カテゴリ
$query = 'SELECT title, path FROM '.$db_pre.'category WHERE up_id=0 ORDER BY path;';
$top_category = $db->rowset_assoc($query);
$w = '';
foreach($top_category as $row){
    $w = '';
    $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
    $w .= '<div style="background-color:';
    $w .= ($i%2)? BACK_COLOR1:BACK_COLOR2;
    $w .= ';">';
    $w .= $EMOJI_NUM_ARRAY[$i];
    echo $w;
    ?>
    <a href="index.php?mode=dir&amp;path=<?php echo $row['path']; ?>" <?php echo $ACCESSKEY_ARRAY[$i]; ?>><?php echo $row['title']; ?></a><br>
<?php
    //サブカテゴリ
    $top_path = $row['path'];
    $sublist_flag = 0;
    $query = 'SELECT title, path, top_list FROM '.$db_pre.'category WHERE path LIKE \''.$top_path.'_%\' ORDER BY path;';
    $sub_category = $db->rowset_assoc($query);
    foreach ($sub_category as $row){
        if($row['top_list']){
            $sublist_flag = 1;
            $row['title'] = mb_convert_kana($row['title'], 'ak', 'UTF-8');
            echo $row['title'].',';
        }
    }
    if($sublist_flag) {
        echo '‥';
    }
    echo '</div><div style="background-color:'.HR_COLOR2.';"><img src="./img/spacer.gif" width="1" height="1" /><br /></div>';
    $i++;
}
//echo '<br />';
?>

<?php echo KOUMOKU; ?>&nbsp;<a href="sitemap.php">サイトマップ</a><br />
<?php echo KOUMOKU; ?>&nbsp;<a href="help.php">ヘルプ</a><br />
<?php echo KOUMOKU; ?>&nbsp;<a href="act_mente.php">登録ｻｲﾄ修正/削除</a><br />

<?php echo mcr(); ?>
</div></body></html>
<?php
$db->close();
exit;
?>