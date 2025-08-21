<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_System_Option extends Table_Abstract_Class
{
	protected string $Table_Name = "{prefix}system_option";
	protected string $Table_Alias = "system_option";
    protected  DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
	{
		$this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
		$sql = "CREATE TABLE " . $this->Table_Name
			. " ("
			.	" option_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
			. 	" option_type_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->System_Option_Type->GetTableName() . " (type_id),"
			.	" option_name VARCHAR(255) NOT NULL,"
			.	" option_value LONGTEXT NOT NULL DEFAULT ''"
			. " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Option_Type_Id, string $Option_Name, string|int|bool $Option_Value)
    {
		if($Option_Name)
		{
            $insert_values = 
                array(
                    "option_type_id" => $Option_Type_Id,
                    "option_name" => $Option_Name,
					"option_value" => $Option_Value
                );
            $item_labels = array_keys($insert_values);
            $table_label = implode(", ", $item_labels);           
            $table_value = array_fill(0, count($insert_values), "?");
            $table_value = implode(",", $table_value);
            $sql = "INSERT INTO " . $this->Table_Name ." (" . $table_label . ") VALUES (" . $table_value . ")";
            return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $insert_values);
		}
		return false;
    }

	public function UpdateSql(string $Where, int $Option_Type_Id = -1, string $Option_Name = "", string|int|bool $Option_Value = "")
	{
		$update_values = 
			array(
				"option_type_id" => $Option_Type_Id,
				"option_name" => $Option_Name,
				"option_value" => $Option_Value
			);
		$set_item = array();
		$set_values = array();
		if(is_array($update_values))
		{
			foreach($update_values as $key => $value)
			{
				if((is_string($value) and $value) or is_bool($value) or (is_integer($value) and $value >= 0))
				{
					$set_item[] = $key . " = ?";
					$set_values[] = $value;
				}
			}
		}
		$sql = "UPDATE " . $this->Table_Name . " SET " . implode(",", $set_item) ." WHERE " . $Where;
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $set_values);
	}

}