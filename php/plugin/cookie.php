<?php
class Cookie{

	//(c1)クッキーの書き込み(set_cookie)
	public static function set_cookie($data) {
		//[0]=パスワード(登録者用)/[1]=ID(登録者用)/[2]=変更者/[3]=管理者用パスワード
		//[4]=直接認証(1or0)/[5]=検索条件(,で区切る)/
		$cookie = implode(':', $data);
		$cookie = str_replace(' ', '', $cookie);
		$cookie = str_replace(';', '', $cookie);
		setcookie('ysp', $cookie, time() + 5184000);
	}

	//(c1.1)クッキーを初期化(set_fo_cookie)
	public static function set_fo_cookie() {
		setcookie('ysp', '', 0);
	}

	//(c2)クッキーの読み込み(get_cookie)
	public static function get_cookie() {
		if(isset($_COOKIE['ysp'])) {
			$cookie_data = explode(':', $_COOKIE['ysp']);
		} else {
			$cookie_data = array('','','','','','');
		}
		return $cookie_data;
	}
}
?>