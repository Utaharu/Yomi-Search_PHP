<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class New_Link_List_Page_Option extends Option_Inheritance_Class
{
	protected object $System;
	public Error_Manager $Error_Manager;
	protected string $Option_Group_Name = "new_link_list_page";
	
	function __construct(object &$Yomi_Search)
	{
		$this->Object_Config =
			array(
				"title" => '新着サイト',
				"description" => '14日以内に登録されたサイト',
				'period'     => 14,                       // 新着・更新期間の日数
				"display_quantity" => 20
			);
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Option New Link List Page Error");
        $this->LoadOptionsValue();
	}

	public function Title(?string $Title = null)
    {
		if(is_null($Title))
		{
			return $this->Object_Config["title"];
		}
		elseif(!$Title)
		{
			$this->Error_Manager->SetLog("タイトルが入力されていません。");	
		}
		else
        {
            $this->Object_Config["title"] = $Title;
            return $this->ChangeOption("title", $Title);
        }
    }

    public function Description(?string $Description = null)
    {
		if(is_null($Description))
		{
			return $this->Object_Config["description"];
		}
		else
        {
            $this->Object_Config["description"] = $Description;
            return $this->ChangeOption("description", $Description);
        }
    }

	public function Period(null|int|string $Period = null)
    {
		if(is_null($Period))
		{
			return $this->Object_Config["period"];
		}
		elseif(!is_numeric($Period))
		{
			$this->Error_Manager->SetLog("期間は、数字で指定してください。");
		}
		else
        {    
            $int_value = $this->GetConvertIntValue($Period, 0);
            if($int_value !== false)
            {
                $this->Object_Config["period"] = $int_value;
                return $this->ChangeOption("period", $int_value);
            }
        }
    }

	public function DisplayQuantity(null|int|string $Quantity = null)
    {
		if(is_null($Quantity))
		{
			return $this->Object_Config["display_quantity"];
		}
		elseif(!is_numeric($Quantity))
		{
			$this->Error_Manager->SetLog("表示数は、数字で指定してください。");
		}
		else
        {
            $int_value = $this->GetConvertIntValue($Quantity, 0);
            if($int_value !== false)
            {
                $this->Object_Config["display_quantity"] = $int_value;
                return $this->ChangeOption("display_quantity", $int_value);
            }
        }
    }

}
