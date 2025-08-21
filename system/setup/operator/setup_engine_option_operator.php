<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Setup\Operator;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

/*
	Class - Initial_Setup
		# Yomi-Search PHP - Initial Setup
		# -- Call
		# --- new Initial_Setup($DataBase_Object);
		# -- functions
*/
use IntlCalendar;
use DateTimeZone;

class Setup_Engine_Option_Operator
{
	private object $System;
	private Error_Manager $Error_Manager;
	
	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Setup Update Engine Option Error");
	}

	public function UpdateEngineOptions()
	{
		$this->System->Config->Options["about_aministrator"]->Language($this->System->User->HttpQueries("language"));
		$this->System->Config->Options["about_aministrator"]->TimeZone($this->System->User->HttpQueries("time_zone"));
		$this->System->Config->Options["about_aministrator"]->CharacterEncoding($this->System->User->HttpQueries("character_encoding"));
		$this->System->Config->Options["about_aministrator"]->Password($this->System->User->HttpQueries("administrator_password"), $this->System->User->HttpQueries("admministrator_password_confirm"));
		$this->System->Config->Options["about_aministrator"]->Name($this->System->User->HttpQueries("administrator_name"));
		$this->System->Config->Options["about_aministrator"]->MailAddress($this->System->User->HttpQueries("administrator_mail_address"));
		$this->System->Config->Options["about_aministrator"]->HomePageUrl($this->System->User->HttpQueries("administrator_homepage_url"));

		$this->VerifySearchEngineTitle($this->System->User->HttpQueries("search_engine_title"));
		$this->VerifyAccessRankingIsEnabled($this->System->User->HttpQueries("in_access_ranking_is_enabled"));
		$this->VerifyInAccessRankingTrasferDestinationUrl($this->System->User->HttpQueries("in_access_ranking_is_enabled"), $this->System->User->HttpQueries("transfer_destination_url"));
		$this->VerifyAccessRankingIsEnabled($this->System->User->HttpQueries("out_access_ranking_is_enabled"));
		$this->VerifyMylinkIsEnabled($this->System->User->HttpQueries("mylink_is_enabled"));
	}

	private function VerifySearchEngineTitle(string $Search_Engine_Title)
	{
		//Search Engine Name (検索エンジンの名称を更新)
		// - PC
		if(!$Search_Engine_Title)
		{
			$this->Error_Manager->SetLog("検索エンジンの名称が入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->System->Config->Options["computer_page"]->SearchEngineTitle($Search_Engine_Title);
			$this->System->Config->Options["smartphone_page"]->SearchEngineTitle($Search_Engine_Title);
		}
	}



	public function VerifyAdministratorPassword(string $Administrator_Password, string $Confirm_Password)
	{
		if(!$Administrator_Password)
		{
			$this->Error_Manager->SetLog("管理者パスワードが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(strlen($Administrator_Password) < 5 or strlen($Administrator_Password) >32)
		{
			$this->Error_Manager->SetLog("管理者パスワードは、5～32文字で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($Administrator_Password != $Confirm_Password)
		{
			$this->Error_Manager->SetLog("管理者パスワードが再入力欄と一致してません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->System->Config->Options["about_administrator"]->Password($Administrator_Password);
		}
	}

	public function VeryfiAdministratorName(string $Administrator_Name)
	{
		if(!$Administrator_Name)
		{
			$this->Error_Manager->SetLog("管理者名が入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(mb_strlen($Administrator_Name) > 50)
		{
			$this->Error_Manager->SetLog("管理者名は、50文字以内で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->System->Config->Options["about_administrator"]->Name($Administrator_Name);
		}
	}

	public function VerifyAdministratorMailAddress(string $Administrator_Mail_Address)
	{
		if(!$Administrator_Mail_Address)
		{
			$this->Error_Manager->SetLog("メールアドレスが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!filter_var($Administrator_Mail_Address, FILTER_VALIDATE_EMAIL))
		{
			$this->Error_Manager->SetLog("メールアドレスの入力形式が正しくありません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->System->Config->Options["about_administrator"]->MailAddress($Administrator_Mail_Address);
		}
	}
    
	public function VerifyAdministratorHomepageUrl(string $Administrator_Homepage_Url)
	{
		if(!$Administrator_Homepage_Url)
		{
			$this->Error_Manager->SetLog("ホームページのURLが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!filter_var($Administrator_Homepage_Url, FILTER_VALIDATE_URL) or !preg_match("/^https?:\/\/.+/", $Administrator_Homepage_Url))
		{
			$this->Error_Manager->SetLog("ホームページのURLの入力形式が正しくありません");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		{
			$this->System->Config->Options["about_administrator"]->HomePageUrl($Administrator_Homepage_Url);
		}
	}

	private function VerifyAccessRankingIsEnabled(string|int|bool $Access_Ranking_Is_Enabled)
	{
		$this->System->Config->Options["in_access_ranking"]->IsEnabled($Access_Ranking_Is_Enabled);
		$this->System->Config->Options["out_access_ranking"]->IsEnabled($Access_Ranking_Is_Enabled);
	}

	public function VerifyInAccessRankingTrasferDestinationUrl(string|int|bool $In_Access_Ranking_Is_Enabled, string $Transfer_Destinatipn_Url)
	{
		$In_Access_Ranking_Is_Enabled = boolval($In_Access_Ranking_Is_Enabled);
		if($In_Access_Ranking_Is_Enabled)
		{
			if(!$Transfer_Destinatipn_Url)
			{
				$this->Error_Manager->SetLog("INアクセスランキングの転送先URLが入力されていません。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif(!filter_var($Transfer_Destinatipn_Url, FILTER_VALIDATE_URL) or !preg_match("/^https?:\/\/.+/", $Transfer_Destinatipn_Url))
			{
				$this->Error_Manager->SetLog("INアクセスランキングの転送先URLの入力形式が正しくありません");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$this->System->Config->Options["in_access_ranking"]->TrasferDestinationUrl($Transfer_Destinatipn_Url);
			}
		}
	}


	private function VerifyMylinkIsEnabled(string|int|bool $Mylink_Is_Enabled)
	{
		$Mylink_Is_Enabled = boolval($Mylink_Is_Enabled);
		$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'is_enabled' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'mylink')", -1, "", $Mylink_Is_Enabled);
	}
}
