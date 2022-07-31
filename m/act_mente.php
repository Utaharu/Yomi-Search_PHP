<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP) modified データ修正用プログラム 						//
// mobile版
/*--------------------------------------------------------------------------*/
include 'mobile_initial.php';
if(! isset($_REQUEST['mode'])) {
    require $cfg['temp_path'] . 'enter.html';
} else {
    require $cfg['sub_path'] . 'act_mente.php';
}
exit();
?>
