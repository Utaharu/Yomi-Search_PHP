<?php
// デバッグモード設定
// 0：E_NOTICEを表示しない
// 1：E_NOTICEを表示する
$debugmode = 0;

if(!$debugmode) {
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(E_ALL);
}
?>