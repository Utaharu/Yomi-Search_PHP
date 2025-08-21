<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class About_Administrator_Option extends Option_Inheritance_Class
{
	protected object $System;
	public Error_Manager $Error_Manager;
	protected string $Option_Group_Name = "about_administrator";
	
	function __construct(object &$Yomi_Search)
	{
		$this->Object_Config =
			array(
				'name' => '',// 管理人の名前
				'mail_address' => '',// 管理人のE-Mail
				'homepage_url' => '',// 管理人のホームページのURL
				'password' => '',// 管理用パスワード(WEB上で設定後は暗号化されます)   				
			);
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Option About Administrator Error");

		if(isset($this->Object_Config['password']) and $this->Object_Config['password'])
		{
			$this->Object_Config['password'] = password_hash($this->Object_Config['password'], $this->System::PASSWORD_HASH_ALGO);
		}
		$this->LoadOptionsValue();
	}

	public function Password(?string $Administrator_New_Password = null , string $Confirm_Password = "")
	{
		if(is_null($Administrator_New_Password))
		{
			return $this->Object_Config["password"];
		}
		elseif(!$Administrator_New_Password)
        {
            $this->Error_Manager->SetLog("管理者パスワードが入力されていません。");
        }
        elseif(strlen($Administrator_New_Password) < 5 or strlen($Administrator_New_Password) >32)
        {
            $this->Error_Manager->SetLog("管理者パスワードは、5～32文字で入力してください。");
        }
        elseif($Administrator_New_Password != $Confirm_Password)
        {
            $this->Error_Manager->SetLog("管理者パスワードが再入力欄と一致してません。");
        }
		else
		{
			$Administrator_New_Password = password_hash($Administrator_New_Password, $this->System::PASSWORD_HASH_ALGO);
			$this->Object_Config['password'] = $Administrator_New_Password;		
			return $this->ChangeOption("password", $Administrator_New_Password);
		}
	}

	public function Name(?string $Administrator_Name = null)
	{
		if(is_null($Administrator_Name))
		{
			return $this->Object_Config["name"];
		}
		elseif(!$Administrator_Name)
		{
			$this->Error_Manager->SetLog("管理者名が入力されていません。");	
		}
		elseif(mb_strlen($Administrator_Name) > 50)
		{
			$this->Error_Manager->SetLog("管理者名は、50文字以内で入力してください。");
		}
		else
		{
			$this->Object_Config['name'] = $Administrator_Name;
			return $this->ChangeOption("name", $Administrator_Name);
		}
	}

	public function MailAddress(?string $Administrator_Mail_Address = null)
	{
		if(is_null($Administrator_Mail_Address))
		{
			return $this->Object_Config["mail_address"];
		}
		elseif(!$Administrator_Mail_Address)
		{
			$this->Error_Manager->SetLog("メールアドレスが入力されていません。");
		}
		elseif(!filter_var($Administrator_Mail_Address, FILTER_VALIDATE_EMAIL))
		{
			$this->Error_Manager->SetLog("メールアドレスの入力形式が正しくありません。");
		}
		else
		{
			$this->Object_Config["mail_address"] = $Administrator_Mail_Address;
			return $this->ChangeOption("mail_address", $Administrator_Mail_Address);
		}
	}

	public function HomepageUrl(?string $Administrator_Homepage_Url)
	{
		if(is_null($Administrator_Homepage_Url))
		{
			return $this->Object_Config["homepage_url"];
		}
		elseif(!$Administrator_Homepage_Url)
		{
			$this->Error_Manager->SetLog("ホームページのURLが入力されていません。");
		}
		elseif(!filter_var($Administrator_Homepage_Url, FILTER_VALIDATE_URL) or !preg_match("/^https?:\/\/.+/", $Administrator_Homepage_Url))
		{
			$this->Error_Manager->SetLog("ホームページのURLの入力形式が正しくありません");
		}
		else
		{
			$this->Object_Config['homepage_url'] = $Administrator_Homepage_Url;
			return $this->ChangeOption("homepage_url", $Administrator_Homepage_Url);
		}
	}

}
