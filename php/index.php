<?php
$start_time1 = microtime(true);

/*--------------------------------------------------------------------------*/
/* - # Yomi-Search PHP - # */
/*--------------------------------------------------------------------------*/
/*
	Yomi-Search				: http://yomi.pekori.to/
	Yomi-Search(PHP)			: http://sql.s28.xrea.com:8080/
	Yomi-Search(PHP)modified	: http://yomiphp-mod.sweet82.com/
	Yomi-Search(PHP)modified ver1.5.8.n2 : http://www.nkbt.net/yomi/
	Yomi-Search PHP Ver:8　～
*/
/*--------------------------------------------------------------------------*/

$mobile_flg = 0;
$strHostName = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
if ( strstr($strHostName, 'docomo.ne.jp') !== false) {
    $mobile_flg=1;
} elseif (preg_match('/\.[dhtckrnsq]\.vodafone\.ne\.jp/i', $strHostName) || preg_match('/\.softbank\.ne\.jp/i', $strHostName)) {
    $mobile_flg=3;
} elseif ( strstr($strHostName, 'ezweb.ne.jp') !== false ) {
    $mobile_flg=2;
}


//DB - Connect
require $script['lib']['db']['main'];
$db = new DB_Connecter($db_set['host'],$db_set['user'],$db_set['password'],$db_set['name'],$db_set['prefix'],$db_set['charset']);

//Load Script Settings
require_once $script['lib']['db']['get_setting'];
$Settings = new Get_Settings($db);
$cfg = array();
$cfg = $Settings->Common;
$cfg_reg = array();
$cfg_reg = $Settings->Register;
$text = array();
$text = $Settings->Description;

require $script['lib']['function'];
require_once $script['lib']['plugin']['ad'];
require_once $script['lib']['plugin']['navigation'];

// ページ設定
if(isset($Form['page']) && is_numeric($Form['page'])) {
 	if($Form['page'] < 1 or $Form['page'] > 1000) {
		$Form['page'] = 1;
	}
} else if(isset($Form['page'])){
	$Form['page'] = preg_replace('/\D/', '', $Form['page']);
	if($Form['page'] < 1 or $Form['page'] > 1000) {
		$Form['page'] = 1;
	}
} else {
    $Form['page'] = 1;
}


//-----------------//
//各モードへ分岐
//-----------------//
if(isset($Form['mode'])) {
	switch($Form['mode']){
		/*
			case "signup":
			case "new":
				require $script['lib']['registration']['index'];
			break;
			case "enter":
			case "edit":
				require $script['lib
			break;
			case 'ranking':
				require $script['lib']['ranking']['index'];
			break;
		*/
		case 'dir':
		//case 'category':
			require $script['lib']['category']['index'];
		break;		
		case 'random':
			require $script['lib']['random'];
		break;
		case 'mylink':
			require $script['lib']['mylink']['index'];
		break;
		case 'help':
			require $script['lib']['help']['index'];
		break;
		case 'sitemap':
			require $script['lib']['sitemap']['index'];
		break;
		default:
			require $script['lib']['category']['index'];
		break;
	}
} else {
    require $script['lib']['top']['index'];
}


if($debug_mode) {
	$fms = memory_get_peak_usage();
	$unit_text = "B";
	$unit_list = array('KB','MB','GB','TB');
	foreach($unit_list as $unit){
		if($fms >= 1000){$fms = $fms / 1024; $unit_text = $unit;}
		else{continue;}
	}
    $end_time1 = microtime(true);
    echo '<div>処理時間：';
    echo $end_time1 - $start_time1;
	echo "</div>\n";
    echo "<div>MEMORY:" . round($fms,2) . " " . $unit_text . "</div>\n";
}

exit;

?>