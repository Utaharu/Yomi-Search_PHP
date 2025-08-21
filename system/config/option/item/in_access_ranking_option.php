<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;

class In_Access_Ranking_Option extends Option_Inheritance_Class
{
    protected object $System;
    protected string $Option_Group_Name = "in_access_ranking";
    protected array $Object_Config = array();
    public Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Object_Config =
            array(
                "is_enabled" => true,
				"minimum_score" => 1,
				"aggregation_period" => 14,
				"update_time" => 6,
				"rank_count_to_display" => 100,
				"transfar_destination_url" => "http://",// アクセスランキング時のリンクジャンプ先URL
				'this_period_title' => 'アクセスランキング',
				'this_period_description' => 'アクセスランキングをベスト100位まで紹介しています',
				'last_period_title' => '前回のアクセスランキング',
				'last_period_description' => '前回のアクセスランキングをベスト100位まで紹介しています',
				'total_period_title' => '累計のアクセスランキング',
				'total_period_description' => '累計のアクセスランキングをベスト100位まで紹介しています'
			);
        $this->Error_Manager = new Error_Manager("Option In Access Ranking Error");
        $this->LoadOptionsValue();
    }

	public function IsEnabled(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["is_enabled"];
		}
		else
        {
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["is_enabled"] = $bool_value;
            return $this->ChangeOption("is_enabled", $bool_value);
        }
	}

    public function MinimumScore(null|int|string $Minimum_Point_Number = null)
    {
		if(is_null($Minimum_Point_Number))
		{
			return $this->Object_Config["minimum_score"];
		}
		elseif(!is_numeric($Minimum_Point_Number))
        {
            $this->Error_Manager->SetLog("最小Pt数は、数字で指定してください。");
        }
		else
        {
            $int_value = $this->GetConvertIntValue($Minimum_Point_Number);
            if($int_value !== false)
            {
                $this->Object_Config["minimum_score"] = $int_value;
                return $this->ChangeOption("minimum_score", $int_value);
            }
        }
    }

    public function AggreGationPeriod(null|int|string $Period_Number = null)
    {
		if(is_null($Period_Number))
		{
			return $this->Object_Config["aggregation_period"];
		}
		elseif(!is_numeric($Period_Number))
        {
            $this->Error_Manager->SetLog("集計期間は、数字で指定してください。");
        }        
		else
        {
            $int_value = $this->GetConvertIntValue($Period_Number, 0);
            if($int_value !== false)
            {
                $this->Object_Config["aggregation_period"] = $int_value;  
                return $this->ChangeOption("aggregation_period", intval($Period_Number));
            }
        }
    }

    public function UpdateTime(null|int|string $Hour = null)
    {
		if(is_null($Hour))
		{
			return $this->Object_Config["update_time"];
		}
		elseif(!is_numeric($Hour))
        {
            $this->Error_Manager->SetLog("同ユーザからのアクセス更新制限時間は、数字で指定してください。");
        }
		else
        {    
            $int_value = $this->GetConvertIntValue($Hour, 0);
            if($int_value !== false)
            {
                $this->Object_Config["update_time"] = $int_value;
                return $this->ChangeOption("update_time", $int_value);
            }
        }
    }

    public function RankCountToDisplay(null|int|string $Rank_Number = null)
    {
		if(is_null($Rank_Number))
		{
			return $this->Object_Config["rank_count_to_display"];            
		}
		elseif(!is_numeric($Rank_Number))
        {
            $this->Error_Manager->SetLog("表示する順位数は、数字で指定してください。");
        }
		else
        {    
            $int_value = $this->GetConvertIntValue($Rank_Number, 0);
            if($int_value !== false)
            {
                $this->Object_Config["rank_count_to_display"] = $int_value;
                return $this->ChangeOption("rank_count_to_display", $int_value);
            }
        }
    }

	public function TrasferDestinationUrl(?string $Transfer_Destinatipn_Url = null)
	{
		if(is_null($Transfer_Destinatipn_Url))
		{
			return $this->Object_Config["transfer_destination_url"];
		}
    	elseif(!filter_var($Transfer_Destinatipn_Url, FILTER_VALIDATE_URL) or !preg_match("/^https?:\/\/.+/", $Transfer_Destinatipn_Url))
		{
			$this->Error_Manager->SetLog("集計対象URLの入力形式が正しくありません");
		}
        else
        {
            $this->Object_Config["transfer_destination_url"] = $Transfer_Destinatipn_Url;
            return $this->ChangeOption("transfer_destination_url", $Transfer_Destinatipn_Url);
        }
	}

    public function ThisPeriodTitle(?string $Title = null)
    {
		if(is_null($Title))
		{
			return $this->Object_Config["this_period_title"];
		}
		else
        {
            $this->Object_Config["this_period_title"] = $Title;
            return $this->ChangeOption("this_period_title", $Title);
        }
    }

    public function ThisPeriodDescription(?string $Description = null)
    {
		if(is_null($Description))
		{
			return $this->Object_Config["this_period_description"];
		}
		else
        {
            $this->Object_Config["this_period_description"] = $Description;
            return $this->ChangeOption("this_period_description", $Description);
        }
    }

    public function LastPeriodTitle(?string $Title = null)
    {
    	if(is_null($Title))
		{
			return $this->Object_Config["last_period_title"];
		}
		else
        {
            $this->Object_Config["last_period_title"] = $Title;
            return $this->ChangeOption("last_period_title", $Title);
        }
    }

    public function LastPeriodDescription(?string $Description = null)
    {
		if(is_null($Description))
		{
			return $this->Object_Config["last_period_description"];
		}
		else
        {
            $this->Object_Config["last_period_description"] = $Description;
            return $this->ChangeOption("last_period_description", $Description);
        }
    }

    public function TotalPeriodTitle(?string $Title = null)
    {
		if(is_null($Title))
		{
			return $this->Object_Config["total_period_title"];
		}
		else
        {
            $this->Object_Config["total_period_title"] = $Title;
            return $this->ChangeOption("total_period_title", $Title);
        }
    }

    public function TotalPeriodDescription(?string $Description = null)
    {
		if(is_null($Description))
		{
			return $this->Object_Config["total_period_description"];
		}
		else
        {
            $this->Object_Config["total_period_description"] = $Description;
            return $this->ChangeOption("total_period_description", $Description);
        }
    }

}