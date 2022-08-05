<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP)modified 付属アクセスカウンタ	 						//
/*--------------------------------------------------------------------------*/

// 同一IPによる連続カウントアップ(する=0/しない=1)
$ip_check = 1;

// トップページに表示するカウンター値(HTMLタグを含む)を生成
$time = time();
$getdate = getdate($time);
$today_time = mktime(0, 0, 0, $getdate['mon'], $getdate['mday'], $getdate['year']);
$getdate['mday']--;
$yesterday_time = mktime(0, 0, 0, $getdate['mon'], $getdate['mday'], $getdate['year']);

$d = $db->db_pre;

$query = 'SELECT ip FROM '.$d.'counter_log ORDER BY time DESC LIMIT 1';
$ip = array();
$ip = $db->single_num($query);
if(!isset($ip[0])){$ip[0] = "";}

if(!$ip_check || $ip[0] != $_SERVER['REMOTE_ADDR']) {
	$query = 'INSERT INTO '.$d.'counter_log VALUES(\''.$_SERVER['REMOTE_ADDR'].'\', \''.$time.'\')';
	$db->query($query);
}
$query = 'SELECT count(ip) FROM '.$d.'counter_log WHERE time >= '.$today_time;
$today = $db->single_num($query);

$query = 'SELECT count(ip) FROM '.$d.'counter_log WHERE time < '.$today_time.' AND time >= '.$yesterday_time;

$yesterday = $db->single_num($query);

$query = 'SELECT count(ip) FROM '.$d.'counter_log WHERE time < '.$yesterday_time;
$total = $db->single_num($query);

if($total[0]) {
	$query = 'DELETE FROM '.$d.'counter_log WHERE time < '.$yesterday_time;
	$db->query($query);

	$query = 'UPDATE '.$d.'counter SET counter=counter+'.$total[0];
	$db->query($query);
}
$query = 'SELECT counter FROM '.$d.'counter';
$total = $db->single_num($query);
$total[0] += $today[0] + $yesterday[0];
$counter = '<div align="center">本日:'.$today[0].'&nbsp;&nbsp;昨日:'.$yesterday[0].'&nbsp;&nbsp;合計:'.$total[0].'</div>';
?>