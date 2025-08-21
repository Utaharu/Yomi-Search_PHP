<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;

use IntlCalendar;
use DateTimeZone;

class Locale_Option extends Option_Inheritance_Class
{
    protected object $System;
    protected string $Option_Group_Name = "locale";
    protected array $Object_Config = array();
    public Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Object_Config =
            array(
				"language" => "ja-JP",
				"time_zone" => "Asia/Tokyo",
				"character_encoding" => "UTF-8",
				"from_list" => "UTF-8,EUC-JP,JIS,Shift_JIS"
            );
        $this->Error_Manager = new Error_Manager("Option Locale Error");
		$this->LoadOptionsValue();
    }

   	public function Language(?string $Language = null)
	{
		if(is_null($Language))
		{
			return $this->Object_Config["language"];
		}
		else
        {	
			$language_list = IntlCalendar::getAvailableLocales();
			$language_key = array_search($Language, $language_list);
			if($language_key === false)
			{
				$this->Error_Manager->SetLog("ロケール設定で、指定できない値が入力されています。");
			}
			else
			{
				$this->Object_Config["language"] = $language_list[$language_key];
				return $this->ChangeOption("language", $language_list[$language_key]);
			}
		}
	}

	public function TimeZone(?string $Time_Zone = null)
	{
		if(is_null($Time_Zone))
		{
			return $this->Object_Config["time_zone"];
		}
		else
        {
			$time_zone_list = DateTimeZone::listIdentifiers();
			$time_zone_key = array_search($Time_Zone, $time_zone_list);
			if($time_zone_key === false)
			{
				$this->Error_Manager->SetLog("タイムゾーン設定で、指定できない値が入力されています。");
			}
			else
			{
				$this->Object_Config["time_zone"] = $time_zone_list[$time_zone_key];
				return $this->ChangeOption("time_zone", $time_zone_list[$time_zone_key]);
			}
		}
	}

	public function CharacterEncoding(?string $Character_Encoding = null)
	{
		if(is_null($Character_Encoding))
		{
			return $this->Object_Config["character_encoding"];
		}
		else
        {
			$character_encoding_list = mb_list_encodings();
			$character_encoding_key = array_search($Character_Encoding, $character_encoding_list);
			if($character_encoding_key === false)
			{
				$this->Error_Manager->SetLog("文字エンコード設定で、指定できない値が入力されています。");
			}
			else
			{
				$this->Object_Config["character_encoding"] = $character_encoding_list[$character_encoding_key];
				return $this->ChangeOption("character_encoding", $character_encoding_list[$character_encoding_key]);
			}
		}
	}

	public function FromList(?string $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["form_list"];
		}
		elseif($Value)
		{
			$this->Object_Config["form_list"] = $Value;
			return $this->ChangeOption("from_list", $Value);
		}
	}
}