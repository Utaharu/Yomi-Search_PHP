<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_System_Option_Type extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}system_option_type";
    protected string $Table_Alias = "system_option_type";
    protected DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
    {
        $this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
        $sql = "CREATE TABLE " . $this->Table_Name
            . "("
	        .   " type_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,"
            .   " type_name VARCHAR(30) NOT NULL ,"
            .   " PRIMARY KEY (type_id, type_name)"
            . " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(string $Option_Type_Name)
    {
        if($Option_Type_Name)
        {
            $insert_values = 
                array(
                    "type_name" => $Option_Type_Name,
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

    public function UpdateSql(int $Option_Type_Id, string $Option_Type_Name)
    {
        if($Option_Type_Id > 0 and $Option_Type_Name)
        {
            $update_values = 
                array(
                    "type_name" => $Option_Type_Name,
                );
            $set_item = array();
            $set_values = array();
            if(is_array($update_values))
            {
                foreach($update_values as $key => $value)
                {
                    if(is_string($value) or is_bool($value) or (is_integer($value) and $value >= 0))
                    {
                        $set_item[] = $key . " = ?";
                        $set_values[] = $value;
                    }
                }
            }
            $set_values[] = $Option_Type_Id;
            $sql = "UPDATE " . $this->Table_Name . " SET " . implode(",", $set_item) ." WHERE type_id = ?";
            return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $set_values);
        }
    }

    public function DeleteSql(int $Option_Type_Id)
    {
        if($Option_Type_Id > 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE type_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Option_Type_Id));
        }
        return false;
    }
}