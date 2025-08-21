<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;

class Keyword_Ranking_Option extends Option_Inheritance_Class
{
    protected object $System;
    protected string $Option_Group_Name = "keyword_ranking";
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
				"rank_count_to_display" => 20,
				'this_period_title' => '検索ワードランキング',
				'this_period_description' => '検索ワードランキングをベスト20位まで紹介しています',
				'last_period_title' => '前回の検索ワードランキング',
				'last_period_description' => '前回の検索ワードランキングをベスト20位まで紹介しています',
				'total_period_title' => '累計の検索ワードランキング',
				'total_period_description' => '累計nの検索ワードランキングをベスト20位まで紹介しています'
            );
        $this->Error_Manager = new Error_Manager("Option Keyword Ranking Error");
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
		else
        {
            $int_value = $this->GetConvertIntValue($Minimum_Point_Number, 0);
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
		else
        {
            $int_value = $this->GetConvertIntValue($Period_Number, 0);
            if($int_value !== false)
            {
                $this->Object_Config["aggregation_period"] = $int_value;
                return $this->ChangeOption("aggregation_period", $int_value);
            }
        }
    }

    public function RankCountToDisplay(null|int|string $Rank_Number = null)
    {
    	if(is_null($Rank_Number))
		{
			return $this->Object_Config["rank_count_to_display"];
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