<?php
/*
	Class - Initial_Setup
		# Yomi-Search PHP - Initial Setup
		# -- Call
		# --- new Initial_Setup($DataBase_Object);
		# -- functions
*/

class Initial_Setup{
	var $DB;
	
	function __construct($DataBase_Object){
		$this->DB = $DataBase_Object;
	}
	
//#### Set ####//
	function Set_EngineName(){
		//Search Engine Name (検索エンジンの名称を更新)
		// - PC
		$Form["search_name"] = $this->DB->escape_string(trim($Form["search_name"]));
		$sql = "UPDATE {prefix}cfg SET value='{$Form["search_name"]}' WHERE name='search_name'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		//　- Smart Phone
		$sql = "UPDATE {prefix}cfg SET value='{$Form["search_name"]}' WHERE name='sp_search_name'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
	}
	
	function Set_EngineUrl(){
		// 検索エンジンのURLを更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["home"]}' WHERE name='home'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		// トップディレクトリのURLを更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["cgi_path_url"]}' WHERE name='cgi_path_url'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		// - スマホ
		$sql = "UPDATE {prefix}cfg SET value='{$Form["cgi_path_url"]}/s/' WHERE name='sp_path_url'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
	}
	
	function Set_Password(){
		// パスワードを暗号化して更新
		$password = Password_Encryption($Form['pass1']);
		$sql = "UPDATE {prefix}cfg SET value='{$password}' WHERE name='pass'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
	}
	
	function Set_Admin(){
		// ホームページのURLを更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["admin_hp"]}' WHERE name='admin_hp'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);

		// 管理者名を更新
		$Form["admin_name"] = $this->DB->escape_string(trim($Form["admin_name"]));
		$sql = "UPDATE {prefix}cfg SET value='{$Form["admin_name"]}' WHERE name='admin_name'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		
		// メールアドレスを更新
		$Form["admin_email"] = $this->DB->escape_string(trim($Form["admin_email"]));
		$sql = "UPDATE {prefix}cfg SET value='{$Form["admin_email"]}' WHERE name='admin_email'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
	}
	
	function Set_Ranking(){
		// 人気(OUT)ランキングの実施を更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["rank_fl"]}' WHERE name='rank_fl'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		// アクセス(IN)ランキングの実施を更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["rev_fl"]}' WHERE name='rev_fl'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
		// アクセス(IN)ランキング用URLの転送先を更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["rev_url"]}' WHERE name='rev_url'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__);
	}
	
	function Set_Mylink(){
		// マイリンク機能の使用を更新
		$sql = "UPDATE {prefix}cfg SET value='{$Form["mylink_fl"]}' WHERE name='mylink_fl'";
		$result = $this->DB->query($sql) or $this->DB->error('Query failed '.$sql.__FILE__.__LINE__); 
	}
	
//#### Check ####//	
	//入力値のチェック
	function Check_Value(){
		$message = "";
		// パスワード未入力チェック
		if(empty($Form['pass1']) || empty($Form['pass2'])) {
			$message .= '<div>パスワードは必ず設定してください。</div>'."\n";
		}

		// パスワード確認チェック
		if($Form['pass1'] != $Form['pass2']) {
			$message .= '<div>パスワードが確認用と一致していません。</div>'."\n";
		}

		// 管理者名未入力チェック
		if(empty($Form['admin_name'])) {
			$message .= '<div>管理者名が未入力です。</div>'."\n";
		}

		// 検索エンジン名称未入力チェック
		if(empty($Form['search_name'])) {
			$message .= '<div>検索エンジンの名称が未入力です。</div>'."\n";
		}

		// メールアドレス未入力チェック
		if(empty($Form['admin_email'])) {
			$message .= '<div>メールアドレスが未入力です。</div>'."\n";
		}

		// メールアドレス形式チェック
		if(!Check_Mail($Form['admin_email'])) {
			$message .= '<div>メールアドレスの形式が不正です。</div>'."\n";
		}

		// 検索エンジンのURL未入力チェック
		if(empty($Form['home'])) {
			$message .= '<div>検索エンジンのURLが未入力です。</div>'."\n";
		}

		// ホームページのURL未入力チェック
		if(empty($Form['admin_hp'])) {
			$message .= '<div>ホームページのURLが未入力です。</div>'."\n";
		}

		// トップディレクトリのURL未入力チェック
		if(empty($Form['cgi_path_url'])) {
			$message .= '<div>トップディレクトリのURLが未入力です。</div>'."\n";
		}

		// トアクセス(IN)ランキング用URLの転送先未入力チェック
		if(empty($Form['rev_url'])) {
			$message .= '<div>アクセス(IN)ランキング用URLの転送先が未入力です。</div>'."\n";
		}
		// </入力チェック>
		return $message;
	}
	
	//Cehck - MailAdress Pattern
	function Check_Mail( $str ) {
		if(preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+$/', $str)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>