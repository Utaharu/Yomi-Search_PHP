<?php
$time = time();

// 総ログ数(登録サイト数)取得
//============================
$total_num = $db->log_count($db->db_pre.'log');

$RssFeed  = '';
$RssFeed .= '<a href="' . $cfg['cgi_path_url'] . 'rss/rss.php?mode=new" target="_blank">';
$RssFeed .= '<img src="' . $cfg['cgi_path_url'] . 'img/rss_new.gif" border="0" alt="新着サイト" title="新着サイト">';
$RssFeed .= '</a>';
$RssFeed .= ' ';
$RssFeed .= '<a href="' . $cfg['cgi_path_url'] . 'rss/rss.php?mode=update" target="_blank">';
$RssFeed .= '<img src="' . $cfg['cgi_path_url'] . 'img/rss_update.gif" border="0" alt="更新サイト" title="更新サイト">';
$RssFeed .= '</a>';

// おすすめ検索ワード
//====================
if ($cfg['keyrank']) {
	$i      = 1;
	$start  = $time - $cfg['keyrank_kikan'] * 86400;
	$end    = $time;

	$query  = '';
    $query  = 'SELECT '
            .     'COUNT(r.word) AS pt, k.word AS wd, r.view_word AS vwd '
            . 'FROM '
            .     $db->db_pre . 'key AS k '
            . 'LEFT JOIN '
            .     $db->db_pre . 'key_rank AS r ON k.word = r.word '
            . 'WHERE '
            .     "(k.time BETWEEN '" . $start . "' AND '" . $end . "') AND r.open_key = '1' "
            . 'GROUP BY '
            .     'k.word '
            . 'ORDER BY '
            .     'pt DESC '
            . 'LIMIT '
            .     '4';

	$rowset = $db->rowset_assoc($query);

    if ($rowset) {
        $search_words = '<br>【 <a href="'.$cfg["rank"].'?mode=keyrank">おすすめ検索ワード</a> =&gt; ';
        foreach ($rowset as $row) {
            $word = rtrim($row['wd']);
            $word_en = urlencode($word);
            if ($row['vwd']) {
                $word = $row['vwd'];
            }
            $search_words .= '<input type="checkbox" name="words[]" value="' . $word . '">' . "\n"
                          .  '<a href="'.$cfg['search'].'?mode=search&word='.$word_en.'&sort='.$cfg['defo_hyouji'].'&engine=pre&method=and">'
                          .  $word
                          .  '</a>'."\n";
            $i++;
        }
        $search_words .= '】<input type="hidden" name="kn" value="'.$i.'">';
    }
}

require $cfg['temp_path'] . 'top.html';
?>