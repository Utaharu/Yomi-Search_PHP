<?php
// クッキーの処理
$cookie_data = get_cookie();
$ck_lines = explode(',', $cookie_data[5]);

// [0]
$ck_0_0 = ' checked';
$ck_0_1 = '';
if($ck_lines[0] == 'o') {
	$ck_0_0 = '';
	$ck_0_1 = ' checked';
}

// [1]
if(isset($ck_lines[1])) {
	$ck_1 = ' checked';
} else {
	$ck_1 = '';
}

// [2]&[3]
if(isset($ck_lines[2]) && isset($ck_lines[3])) {
	$ck_2_3 = '<option value="'.$ck_lines[2].'" selected>'.$ck_lines[3].'</option>';
} else {
	$ck_2_3 = '';
}

// [4]
if(isset($ck_lines[4])) {
	$ck_4 = '';
} else {
	$ck_4 = ' checked';
}

// [5]&[6]
if(isset($ck_lines[5]) && isset($ck_lines[6])) {
	$ck_5_6 = '<option value="'.$ck_lines[5].'" selected>'.$ck_lines[6].'</option>';
} else {
	$ck_5_6 = '';
}

// [7]
if(isset($ck_lines[7])) {
	$ck_7_0 = '';
	$ck_7_1 = ' checked';
} else {
	$ck_7_0 = ' checked';
	$ck_7_1 = '';
}

// [8]&[9]
if(isset($ck_lines[8]) && isset($ck_lines[9])) {
	$ck_8_9 = '<option value="'.$ck_lines[8].'" selected>'.$ck_lines[9].'</option>';
} else {
	$ck_8_9 = '';
}

// [10]
if(isset($ck_lines[10])) {
	$ck_10 = $ck_lines[10];
} else {
	$ck_10 = '';
}

// [11]
if(isset($ck_lines[11])) {
	$ck_11_0 = '';
	$ck_11_1 = ' checked';
} else {
	$ck_11_0 = ' checked';
	$ck_11_1 = '';
}

if($cookie_data[5]) {
	$ck_ck = ' checked';
}

if(isset($_GET['window'])) {
	if($_GET['window'] == '_blank') {
		$PR_open_type_select_0 = '';
		$PR_open_type_select_1 = ' selected';
	} else {
		$PR_open_type_select_0 = ' selected';
		$PR_open_type_select_1 = '';
	}
} else {
	$PR_open_type_select_0 = ' selected';
	$PR_open_type_select_1 = '';
}

require $cfg['temp_path'] . 'search_ex.html';
?>