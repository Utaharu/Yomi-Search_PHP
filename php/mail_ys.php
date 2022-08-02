<?php
/*--------------------------------------------------------------------------*/
// Yomi-Search(PHP) modified メール送信系ライブラリ 						//
/*--------------------------------------------------------------------------*/

// ※(1)～(4)は比較的簡単に修正可能です。
// -- 目次 -- //
// (1)登録メールの本文設定(&regist_mail)
// (2)登録内容変更メールの本文設定(&ch_mail)
// (3)仮登録メールの本文設定(&temp_mail)
// (4)パスワード再発行メールの本文設定(&pass_mail)
// (5)メール送信処理(&mail)

// (1)登録メールの本文設定(&regist_mail)
//【本文の内容変更の方法】
// ・[$]から始まる部分は[変数]です。
//   登録者のデータによって様々な値が入りますので修正する際にはそのまま移動させてください。
// ・通常の日本語部分は修正しても大丈夫です。
// ・修正できる部分は『print OUT<<"EOM";』の次の行から『EOM』の前の行までです。

function regist_mail($log) {
	global $cfg, $PRkt, $PRpass, $PRsougo, $PRadd_kt, $PRto_admin, $PRto_register, $admin_fl;
	$honbun = 'このたびは、' . $cfg['search_name'] . " へのご登録ありがとうございます。

登録内容は以下のとおりですので、ご確認ください。
*************************************************
・登録日時：".$log[4]."
・登録者のIPアドレス：".$_SERVER['REMOTE_ADDR'] . "
・登録者のホスト名：" . $_SERVER['REMOTE_HOST'] . "
・参照元：" . $_SERVER['HTTP_REFERER'] . "
*************************************************

■ID
$log[0]
■お名前
$log[8]
■Ｅメール
$log[9]
■タイトル
$log[1]
■登録したカテゴリ
$PRkt
■紹介文
$log[6]
■URL
$log[2]
";
	if($admin_fl != 'admin') {
		$honbun .= "■管理パスワード\n".$PRpass."\n";
	}
	if($PRsougo) {
		$honbun .= "■相互リンク\n".$PRsougo."\n";
	}
	if($PRadd_kt) {
		$honbun .= "■追加して欲しいカテゴリ\n".$PRadd_kt."\n";
	}
	if($PRto_admin) {
		$honbun .= "■管理人へのメッセージ\n".$PRto_admin."\n";
	}
	if($PRto_register) {
		$honbun .="■管理人から" . $log[8] . "さんへのメッセージ\n".$PRto_register."\n";
	}
	$honbun .= "
■登録内容変更用URL：
{$cfg['cgi_path_url']}regist_ys.php?mode=enter&id={$log[0]}

登録されてから{$cfg['new_time']}日間はNew!マークが付き、
新着情報のページで掲載されます。

登録内容が変更になった場合などには、
内容修正をしていただくと、UP!マークが
{$cfg['new_time']}日間付き、更新サイト情報のページに掲載されます。
";

	if($cfg['rev_fl']) {
		$honbun .= "

★現在アクセスランキングを開催しています
以下のURLにリンクを張っていただくとランキングに集計されます。
【あなた専用のURL】
{$cfg['cgi_path_url']}{$cfg['rank']}?mode=r_link&id={$log[0]}
あなたのサイトの訪問者の方が上記のURLへのリンクをクリックすると、
当サイトのトップページに転送され、アクセスランキングのポイントが加算されます。
※ただし、一定時間内の同じ訪問者の方からのアクセスは1ポイントとします
もしよろしければご参加ください♪
";
	}
	$honbun .= "

なお、今後登録内容の修正や削除する場合には、管理パスワード
にて全て行うことができますので、パスワードは大切に保管しておいて下さい。

これからもどうぞよろしくお願いします。

+-------------------------------------+
  {$cfg['search_name']} 管理人 {$cfg['admin_name']}
  HP: {$cfg['admin_hp']}
  E-Mail: {$cfg['admin_email']}
+-------------------------------------+
";
	return $honbun;
}

// (2)登録内容変更メールの本文設定(&ch_mail)
// 【本文の内容変更の方法】
// 『(1)登録メールの本文設定』と同じです。
function ch_mail($log) {
	global $cfg, $PRkt;
		$honbun = "登録変更内容は以下のとおりですので、ご確認ください。
*************************************************
・登録日時：" . $log[4] . "
・登録者のIPアドレス：" . $_SERVER["REMOTE_ADDR"] . "
・登録者のホスト名：" . $_SERVER["REMOTE_HOST"] . "
*************************************************

■ID
$log[0]
■Ｅメール
$log[9]
■タイトル
$log[1]
■登録したカテゴリ
$PRkt
■紹介文
$log[6]
■URL
$log[2]
■登録内容変更用URL
{$cfg['cgi_path_url']}regist_ys.php?mode=enter&id={$log[0]}

+-------------------------------------+
  {$cfg['search_name']} 管理人 {$cfg['admin_name']}
  HP: {$cfg['admin_hp']}
  E-Mail: {$cfg['admin_email']}
+-------------------------------------+
";
	return $honbun;
}

// (3)仮登録メールの本文設定(&temp_mail)
// 【本文の内容変更の方法】
// 『(1)登録メールの本文設定』と同じです。
function temp_mail($log) {
	global $cfg, $PRkt, $PRpass, $PRsougo, $PRadd_kt, $PRto_admin, $PRto_register, $admin_fl;
	$honbun = 'このたびは、' . $cfg['search_name'] . " への仮登録ありがとうございます。
数日中に登録いたしますのでしばらくお待ちください。
*************************************************
・登録日時：" . $log[4] . "
・登録者のIPアドレス：" . $_SERVER['REMOTE_ADDR'] . "
・登録者のホスト名：" . $_SERVER['REMOTE_HOST'] . "
・参照元：" . $_SERVER['HTTP_REFERER'] . "
*************************************************

■お名前
$log[8]
■Ｅメール
$log[9]
■タイトル
$log[1]
■登録したカテゴリ
$PRkt
■紹介文
$log[6]
■URL
$log[2]
";
	if($admin_fl != 'admin') {
		$honbun .= '■管理パスワード'."\n".$PRpass."\n";
	}
	if($PRsougo) {
		$honbun .= '■相互リンク'."\n".$PRsougo."\n";
	}
	if($PRadd_kt) {
		$honbun .= '■追加して欲しいカテゴリ'."\n".$PRadd_kt."\n";
	}
	if($PRto_admin) {
		$honbun .= '■管理人へのメッセージ'."\n".$PRto_admin."\n";
	}

	$honbun .= "

なお、今後登録内容の修正や削除する場合には、管理パスワード
にて全て行うことができますので、パスワードは大切に保管しておいて下さい。

+-------------------------------------+
  {$cfg['search_name']} 管理人 {$cfg['admin_name']}
  HP: {$cfg['admin_hp']}
  E-Mail: {$cfg['admin_email']}
+-------------------------------------+
";
	return $honbun;
}

// (4)パスワード再発行メールの本文設定(&pass_mail)
// 【本文の内容変更の方法】
// 『(1)登録メールの本文設定』と同じです。
function pass_mail($log) {
	global $cfg, $new_pass;
	$honbun = "このメールは、パスワード再発行の通知メールです。
新しいパスワード： {$new_pass}

*************************************************
・変更者のIPアドレス：" . $_SERVER['REMOTE_ADDR'] . "
・変更者のホスト名：" . $_SERVER['REMOTE_HOST'] . "
*************************************************

■お名前
$log[8]
■タイトル
$log[9]
■URL
$log[2]
■登録内容変更用URL：
{$cfg["cgi_path_url"]}regist_ys.php?mode=enter&id={$log[0]}

+-------------------------------------+
  {$cfg['search_name']} 管理人 {$cfg['admin_name']}
  HP: {$cfg['admin_hp']}
  E-Mail: {$cfg['admin_email']}
+-------------------------------------+
";
	return $honbun;
}

// (5)メール送信処理(&mail)
// $arg1=>受信先メールアドレス
// $arg2=>送信先メールアドレス
// $arg3=>件名
// $arg4=>送信モード設定(新規登録=new/登録内容変更=mente/仮登録=temp/パスワード再発行=pass/汎用メール=any)
// $arg5=>管理人=admin/その他=省略
// $arg6=>データ配列の型グロブ
// $arg7=>相互リンクの有無
// $arg8=>新設希望カテゴリ
// $arg9=>管理人へのメッセージ
// $arg10=>管理人から登録者へのメッセージ
// $arg11=>汎用メールの場合の本文
function sendmail($mailto, $from_mail, $kenmei, $mail_mode, $admin, $log, $arg6 = '', $add_kt = '', $to_admin = '', $to_register = '', $PRhonbun = '') {
	// $honbun, // 本文
	global $Eref, $admin_fl,
	$PRkt, // カテゴリ名
	$PRpass, // パスワード
	$PRsougo, // 相互リンク
	$PRadd_kt, // 新設希望カテゴリ
	$PRto_admin, // 管理人へのメッセージ
	$PRto_register; // 管理人から登録者へのメッセージ

	//グローバル変数に格納
	$PRadd_kt      = $add_kt;      // 新設希望カテゴリ
	$PRto_admin    = $to_admin;    // 管理人へのメッセージ
	$PRto_register = $to_register; // 管理人から登録者へのメッセージ

	// その他の整形
	// referer
	$_SERVER['HTTP_REFERER'] = $Eref;
	$admin_fl = $admin;
	$PRkt = '';
	
	// カテゴリ
	$kt = explode('&', $log[10]);
	foreach($kt as $tmp) {
		$PRkt .= full_category($tmp) . "\n";
	}
	// パスワード
	if(!isset($_POST['Fpass'])) {
		$PRpass = '(登録時に記入したパスワードです)';
	} elseif($admin_fl != 'admin') {
		$PRpass = $_POST['Fpass'];
	} else {
		$PRpass = '(管理人は見ることができません)';
	}
	// 相互リンク
	if($arg6) {
		$PRsougo = 'する';
	} else {
		$PRsougo = '';
	}
	// $ENV['REMOTE_HOST']
	if(!isset($_SERVER['REMOTE_HOST'])) {
		$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	}
	if($_POST['mode'] == 'temp_to_regist_act') { // 仮登録→本登録の場合
		$_SERVER['REMOTE_HOST'] = $_SERVER['REMOTE_ADDR'] = '(管理人登録)';
	}
	// 管理人へのメッセージの改行変換
	$PRto_admin = str_replace('<br>', "\n", $PRto_admin);
	$PRto_admin = str_replace('&lt;br&gt;', "\n", $PRto_admin);

	if($mail_mode == 'new') {
		$honbun = regist_mail($log);
	} elseif($mail_mode == 'mente') {
		$honbun = ch_mail($log);
	} elseif($mail_mode == 'temp') {
		$honbun = temp_mail($log);
	} elseif($mail_mode == 'pass') {
		$honbun = pass_mail($log);
	} elseif($mail_mode == 'any') {
		$honbun = $PRhonbun;
	}
	
	// メール送信処理
	$from_mail = mb_convert_encoding($from_mail, 'JIS', 'UTF-8');
	$heady  = "From:<{$from_mail}>\n";
	$heady .= "Reply-To: {$from_mail}\n";
	$heady .= "X-Mailer: PHP/" . phpversion();
	$heady .= "MIME-version: 1.0\n";
	$heady .= "Content-Type: text/plain; charset=\"iso-2022-jp\"\n";
	$heady .= "Content-Transfer-Encoding: 7bit\n";
	$heady  = mime_enc($heady);
	$kenmei = mb_convert_encoding($kenmei, 'iso-2022-jp', 'UTF-8');
	if($kenmei != '') {
		$kenmei = mime_enc($kenmei, 1);
	}
	$honbun = mb_convert_encoding($honbun, 'JIS', 'UTF-8');
	$honbun = wordwrap($honbun, 60);
	mail($mailto, $kenmei, $honbun, $heady);
}

// エンコード関数
function mime_enc($str, $mime = 0) {
	$str = mb_convert_encoding($str, 'JIS', 'UTF-8');
	if($mime) {
		$encode = "=?iso-2022-jp?B?" . base64_encode($str) . "?=";
	} else {
		$encode = $str;
	}
	return $encode;
}
?>