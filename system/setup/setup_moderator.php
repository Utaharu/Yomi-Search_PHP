<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Setup;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Sora_Kanata\Common_Library\File\File_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Setup\Operator\Setup_DataBase_Setting_Operator;
use Wonder_Link\Yomi_Search_PHP\System\Setup\Operator\Setup_DataBase_Table_Operator;
use Wonder_Link\Yomi_Search_PHP\System\Template\Template_Tag_Replacer;

use Wonder_Link\Yomi_Search_PHP\System\Setup\Operator\Setup_Engine_Option_Operator;

use PDO;
use PDOException;
use IntlCalendar;
use DateTimeZone;

class Setup_Moderator
{
	private object $System;
	private File_Manager $File_Manager;
	private Error_Manager $Error_Manager;

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->File_Manager = new File_Manager();
		$this->Error_Manager = new Error_Manager("Setup Page Print Moderator Error");
	}

	public function Printer()
	{
		if(!file_exists($this->System::SYSTEM_CONFIG_FILE))
		{
			if(!file_exists($this->System::SYSTEM_CONFIG_FILE) and !$this->System->Page_Mode)
			{
				$this->System->Page_Mode = "setup";
			}
			$html = "";
			switch($this->System->Page_Mode)
			{
				case "setup" :
					$html = $this->File_Manager->ReadToFile($this->System->Config->Directory_List['setup_template'] . "setup_start.html", false);
				break;
				case "setup-database_setting" :
					$html = $this->File_Manager->ReadToFile($this->System->Config->Directory_List['setup_template'] . "setup_database_setting.html", false);
				break;
				case "setup-database" :
					$html = $this->SetupDataBase();
				break;
				case "setup-search_engine_setting" :
					$html = $this->PrintSearchEngineSetting();
				break;
				case "setup-complete" :
					$html = $this->SetupComplete();
				break;
			}
			if(!$html)
			{
				$this->Error_Manager->SetLog("ページ判定が出来ませんでした。");
				$this->Error_Manager->PrintLastErrorAsPage();				
			}

			$pattern = array('/\$charset/i', '/\$css/i');
			$replace = array("UTF-8", "./css/setup_style.css");
			print preg_replace($pattern, $replace, $html);
		}
	}

	private function PrintSearchEngineSetting()
	{
		$search_engine_setting_html = $this->File_Manager->ReadToFile($this->System->Config->Directory_List['setup_template'] . "setup_engine_setting.html", false);
		$search_engine_setting_html = $this->ReplaceCharacterEncodingList($search_engine_setting_html);
		$search_engine_setting_html = $this->ReplaceLanguageList($search_engine_setting_html);
		$search_engine_setting_html = $this->ReplaceTimeZoneList($search_engine_setting_html);
		return $search_engine_setting_html;
	}

	private function SetupComplete()
	{
		$engine_option_operator = new Setup_Engine_Option_Operator($this->System);
		$engine_option_operator->UpdateEngineOptions();
		if(rename($this->System::TEMPORARY_SYSTEM_CONFIG_FILE, $this->System::SYSTEM_CONFIG_FILE) === false)
		{
			$this->Error_Manager->SetLog("Configファイルのリネームに失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$setup_complete_html = "";
			$setup_complete_html = $this->File_Manager->ReadToFile($this->System->Config->Directory_List['setup_template'] . "setup_complete.html", false);
			return $setup_complete_html;
		}
	}

	private function SetupDataBase()
	{
		$setup_database_setting = new Setup_DataBase_Setting_Operator($this->System);
		$setup_database_setting->VerifyDataBaseSettingValues();
		$database_connector = $setup_database_setting->ConnectTest();
		if($database_connector instanceof PDOException)
		{
			$this->Error_Manager->SetLog("データベースへの接続が確認できませんでした。({$database_connector->getMessage()}");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->System->DataBase_Manager->SetDataBaseConnector($database_connector);
			if($setup_database_setting->CreateDataBase() === false)
			{
				$this->Error_Manager->SetLog("データベースの作成に失敗しました。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			$this->System->DataBase_Manager->SelectDataBase();
			$this->System->Config->LoadOptions();		
			$setup_database_table = new Setup_DataBase_Table_Operator($this->System);
			$setup_database_setting->CreateSystemConfigFile($this->System::TEMPLATE_SYSTEM_CONFIG_FILE, $this->System::TEMPORARY_SYSTEM_CONFIG_FILE);

			$result_create_table = array();
			$result_create_table = $setup_database_table->SetupDataBaseTable();
			$result_page_html = $this->File_Manager->ReadToFile($this->System->Config->Directory_List['setup_template'] . "setup_database_results.html", false);
			$tag_replace = new Template_Tag_Replacer("result_message");
			$result_page_html = $tag_replace->Replace(array("message"=>$result_create_table), $result_page_html);

			return $result_page_html;
		}
	}

    private function ReplaceLanguageList(string $Template_Html)
    {
        $tag_replacer = new Template_Tag_Replacer("language_list");
        $value = IntlCalendar::getAvailableLocales();
        $caption = IntlCalendar::getAvailableLocales();
        $selected = array_pad(array(), count($value), "");
        $select_value_index = array_search("ja_JP", $value);
 
        if($select_value_index !== false and isset($selected[$select_value_index]))
        {
            $selected[$select_value_index] = "selected";
        }

        $values = array("value"=>$value, "caption"=>$caption, "selected"=>$selected);
        return $tag_replacer->Replace($values ,$Template_Html);
    }

    private function ReplaceTimeZoneList(string $Template_Html)
    {
        $tag_replacer = new Template_Tag_Replacer("time_zone_list");
        $value = DateTimeZone::listIdentifiers();
        $caption = DateTimeZone::listIdentifiers();
        $selected = array_pad(array(), count($value), "");
        $select_value_index = array_search("Asia/Tokyo", $value);
 
        if($select_value_index !== false and isset($selected[$select_value_index]))
        {
            $selected[$select_value_index] = "selected";
        }

        $values = array("value"=>$value, "caption"=>$caption, "selected"=>$selected);
        return $tag_replacer->Replace($values ,$Template_Html);
    }

    private function ReplaceCharacterEncodingList(string $Template_Html)
    {
        $tag_replacer = new Template_Tag_Replacer("character_list");
        $value = mb_list_encodings();
        $caption = mb_list_encodings();
        $selected = array_pad(array(), count($value), "");
        $select_value_index = array_search("UTF-8", $value);
 
        if($select_value_index !== false and isset($selected[$select_value_index]))
        {
            $selected[$select_value_index] = "selected";
        }

        $values = array("value"=>$value, "caption"=>$caption, "selected"=>$selected);
        return $tag_replacer->Replace($values ,$Template_Html);
    }   

}