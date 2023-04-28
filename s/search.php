<?php
/*-------------------------------------------------------------------------
 Yomi-Search PHP - SmartPhone
 Search Page
--------------------------------------------------------------------------*/
require "../config.php";

// mode値で処理分岐
if(isset($Form['mode'])){
	require $script['lib']['search']['index'];
}else{
	require $script['lib']['search']['form'];
}
exit;
?>



