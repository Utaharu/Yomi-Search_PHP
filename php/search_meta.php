<?php
// 外部リンク画面
if ($_GET['mode'] == 'meta') {
    if ($cfg['keyrank'] && $_GET['page'] == 1) { // キーワードランキング用のデータを取得
        set_word();
    }
    if(! function_exists('meta')) require $cfg['sub_path'] . 'meta_ys.php';
    header ('Content-type: text/html; charset=UTF-8');
    require $cfg['temp_path'] . 'search_meta.html';
}
?>