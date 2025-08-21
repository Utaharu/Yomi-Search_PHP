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
		$this->VerifyLocaleLanguage($this->System->User->HttpQueries("language"));
		$this->VerifyTimeZone($this->System->User->HttpQueries("time_zone"));
		$this->VerifyCharacterEncoding($this->System->User->HttpQueries("character_encoding"));		
		$this->VerifyAdministratorPassword($this->System->User->HttpQueries("administrator_password"), $this->System->User->HttpQueries("admministrator_password_confirm"));
		$this->VeryfiAdministratorName($this->System->User->HttpQueries("administrator_name"));
		$this->VerifySearchEngineTitle($this->System->User->HttpQueries("search_engine_title"));
		$this->VerifyAdministratorMailAddress($this->System->User->HttpQueries("administrator_mail_address"));
		$this->VerifyAdministratorHomepageUrl($this->System->User->HttpQueries("administrator_homepage_url"));
		$this->VerifyInAccessRankingIsEnabled($this->System->User->HttpQueries("in_access_ranking_is_enabled"));
		$this->VerifyInAccessRankingTrasferDestinationUrl($this->System->User->HttpQueries("in_access_ranking_is_enabled"), $this->System->User->HttpQueries("transfer_destination_url"));
		$this->VerifyOutAccessRankingIsEnabled($this->System->User->HttpQueries("out_access_ranking_is_enabled"));
		$this->VerifyMylinkIsEnabled($this->System->User->HttpQueries("mylink_is_enabled"));
	}
	
	private function VerifyLocaleLanguage(string $Language)
	{	
		$language_list = IntlCalendar::getAvailableLocales();
		$language_key = array_search($Language, $language_list);
		if($language_key === false)
		{
			$this->Error_Manager->SetLog("ロケール設定で、指定できない値が入力されています。");
			$this->Error_Manager->PrintLastErrorAsPage();			
		}
		else
		{
			$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'language' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'locale')", -1, "", $language_list[$language_key]);
		}
	}

	private function VerifyTimeZone(string $Time_Zone)
	{	
		$time_zone_list = DateTimeZone::listIdentifiers();
		$time_zone_key = array_search($Time_Zone, $time_zone_list);
		if($time_zone_key === false)
		{
			$this->Error_Manager->SetLog("タイムゾーン設定で、指定できない値が入力されています。");
			$this->Error_Manager->PrintLastErrorAsPage();			
		}
		else
		{
			$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'time_zone' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'locale')", -1, "", $time_zone_list[$time_zone_key]);
		}
	}

	private function VerifyCharacterEncoding(string $Character_Encoding)
	{	
		$caracter_encoding_list = mb_list_encodings();
		$caracter_encoding_key = array_search($Character_Encoding, $caracter_encoding_list);
		if($caracter_encoding_key === false)
		{
			$this->Error_Manager->SetLog("文字エンコード設定で、指定できない値が入力されています。");
			$this->Error_Manager->PrintLastErrorAsPage();			
		}
		else
		{
			$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'caracter_encoding' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'locale')", -1, "", $caracter_encoding_list[$caracter_encoding_key]);
		}
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
			$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'search_engine_title' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'computer_page')", -1, "", $Search_Engine_Title);
			$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'search_engine_title' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'smartphone_page')", -1, "", $Search_Engine_Title);
		}
	}


/

	private function VerifyOutAccessRankingIsEnabled(string|int|bool $Out_Access_Ranking_Is_Enabled)
	{
		$Out_Access_Ranking_Is_Enabled = boolval($Out_Access_Ranking_Is_Enabled);
		$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'is_enabled' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'out_access_ranking')", -1, "", $Out_Access_Ranking_Is_Enabled);
	}

	private function VerifyInAccessRankingIsEnabled(string|int|bool $In_Access_Ranking_Is_Enabled)
	{
		$In_Access_Ranking_Is_Enabled = boolval($In_Access_Ranking_Is_Enabled);
		$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'is_enabled' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'in_access_ranking')", -1, "", $In_Access_Ranking_Is_Enabled);
	}

/* admin - homepage or this search engine だけにする？*/
	private function VerifyInAccessRankingTrasferDestinationUrl(string|int|bool $In_Access_Ranking_Is_Enabled, string $Transfer_Destinatipn_Url)
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
				$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'transfer_destination_url' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'in_access_ranking')", -1, "", $Transfer_Destinatipn_Url);
			}
		}
	}

	private function VerifyMylinkIsEnabled(string|int|bool $Mylink_Is_Enabled)
	{
		$Mylink_Is_Enabled = boolval($Mylink_Is_Enabled);
		$this->System->DataBase_Manager->System_Option->UpdateSql("option_name = 'is_enabled' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = 'mylink')", -1, "", $Mylink_Is_Enabled);
	}
}
