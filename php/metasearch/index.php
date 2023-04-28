<?php
// 外部リンク画面
if(isset($Form['mode']){
	switch($Form['mode']){
		case 'meta':
			if ($cfg['keyrank'] && $Form['page'] == 1) { // キーワードランキング用のデータを取得
				set_word();
			}
			if(! function_exists('meta')) require $script['lib']['metasearch']['result'];
			require $path_set['template']['root'] . '/search_meta.html';
		break;
	}
}
?>