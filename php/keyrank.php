<?php
// 最終更新時刻を取得
$time = time();
$last_mod = date('Y/m/d(D) H:i', $time);
function PR_keyrank_data($keyrank_fl) {
	global $time, $cfg, $db;
	$jyuni = 1;
	$jyuni_z = 1;
	if($keyrank_fl == 'bf') {
		$start = $time - $cfg['keyrank_kikan'] * 86400 * 2;
		$end = $time - $cfg['keyrank_kikan'] * 86400 - 1;
	} elseif($keyrank_fl == 'pre') {
		$start = $time - $cfg['keyrank_kikan'] * 86400;
		$end = $time;
	} else {
		$start = 0;
		$end = $time;
	}
	$query = 'SELECT count(k.word) pt,k.word wd,r.view_word vwd FROM '.$db->db_pre.'key AS k LEFT JOIN '.$db->db_pre.'key_rank AS r ON k.word=r.word WHERE (k.time BETWEEN \''.$start.'\' AND \''.$end.'\') AND r.open_key=\'1\' GROUP BY k.word ORDER BY pt DESC';
	$rowset = $db->rowset_assoc($query);
	$bf_c = "";
	foreach($rowset as $row) {
		if($row['pt'] >= $cfg['keyrank_cut']) {
			$row['wd'] = str_replace("’", "'", $row["wd"]);
			$en_keyword = urlencode($row['wd']);
			if($row['vwd']) {
				$row['wd'] = $row['vwd'];
			}
			if($bf_c != $row['pt']) {
				$jyuni = $jyuni_z;
			}
			echo '<tr>';
			echo '<th>'.$jyuni.'</th>'."\n";
			echo '<td><a href="'.$cfg['search'].'?mode=search&word='.$en_keyword.'">'.$row['wd'].'</a> -> '.$row['pt'].'pts.</td>'."\n";
			echo '<th><a href="'.$cfg['search'].'?mode=search&word='.$en_keyword.'" target="_blank">■</a></th>';
			echo '</tr>'."\n";
			$jyuni_z++;
			$bf_c = $row['pt'];
			if($jyuni_z > $cfg['keyrank_hyouji']) {
				break;
			}
		}
	}
	while($jyuni_z <= $cfg['keyrank_hyouji']) {
		echo '<tr><th>-</th><th>-</th><th>-</th></tr>';
		$jyuni_z++;
	}
}
require $cfg['temp_path'] . 'keyrank.html';
?>