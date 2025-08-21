<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class;

use PDOStatement;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Connector;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;

class Option_Inheritance_Class
{
	protected object $System;
	public Error_Manager $Error_Manager;
	protected string $Option_Group_Name = "";
    protected array $Object_Config = array();

    public function SetUpOptionType()
    {
        $option_type_result_sql = $this->System->DataBase_Manager->System_Option_Type->SelectSql
        (
            array($this->Option_Group_Name),
            "type_id",
            "",
            "type_name = ?"
        );
        if($option_type_result_sql == false or $option_type_result_sql->fetchObject() == false)
        {
            return $this->System->DataBase_Manager->System_Option_Type->InsertSql($this->Option_Group_Name);
        }
    }

    public function SetUpOptionValue()
    {
        if(is_array($this->Object_Config))
        {        
            $option_type_result_sql = $this->System->DataBase_Manager->System_Option_Type->SelectSql(array($this->Option_Group_Name), "type_id", "", "type_name = ?");
            if($option_type = $option_type_result_sql->fetchObject())
            {
                foreach($this->Object_Config as $option_name => $option_value)
                {
                    $option_value_result_sql = $this->System->DataBase_Manager->System_Option->SelectSql(array($option_name),"option_id, option_name, option_value", "", "option_name = ?");
                    if($option_value_result_sql == false or $option_value_result_sql->fetchObject() == false)
                    {
                        $this->System->DataBase_Manager->System_Option->InsertSql($option_type->type_id, $option_name, $option_value);
                    }
                }
            }
        }
    }

	protected function LoadOptionsValue()
    {
        if(file_exists($this->System::SYSTEM_CONFIG_FILE))
        {
            $sql_result = $this->System->DataBase_Manager->System_Option->SelectSql
            (
                array($this->Option_Group_Name),
                "option_type.type_name, option_name, option_value",
                $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " AS option_type ON system_option.option_type_id = option_type.type_id",
                "option_type.type_name = ?"
            );

            if($sql_result instanceof PDOStatement)
            {
                while($option_data = $sql_result->fetchObject())
                {
                    if(isset($this->Object_Config[$option_data->option_name]))
                    {
                        $this->Object_Config[$option_data->option_name] = $option_data->option_value;
                    }
                }
            }
        }
    }

    public function GetOptionGroupName()
    {
        return $this->Option_Group_Name;
    }

    protected function ChangeOption(string $KeyName, $Value)
    {
        if(array_key_exists($KeyName, $this->Object_Config))
        {        
   			return $this->System->DataBase_Manager->System_Option->UpdateSql
            (
                "option_name = '" . $KeyName . "' AND option_type_id = (select type_id FROM " . $this->System->DataBase_Manager->System_Option_Type->GetTableName() . " WHERE type_name = '" . $this->Option_Group_Name . "')",
                -1,
                "",
                $Value
            );
        }
        return false;
    }

    protected function GetConvertBoolValue(string|bool|int $Value)
    {
        if($Value === 1 or $Value === "1" or $Value === true or preg_match("/true/i", $Value))
		{
			return true;
		}
		else
		{
			return  false;
		}	
    }

    protected function GetConvertIntValue(int|string $Value, ?int $Min = null, ?int $Max = null)
    {
        if(!is_numeric($Value))
        {
            return false;
        }
        elseif(!is_null($Min) and intval($Value) < intval($Min))
        {
            return false;
        }
        elseif(!is_null($Max) and intval($Value) > intval($Max))
        {
            return false;
        }
        else
        {
            return intval($Value);
        }
    }

}