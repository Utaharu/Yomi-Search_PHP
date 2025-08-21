<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Link_Category extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}link_category";
    protected string $Table_Alias = "link_category";
    protected DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
    {
        $this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
        $sql = "CREATE TABLE " . $this->Table_Name
             . " ("
             .  " id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
             .  " link_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->Link_List->GetTableName() ." (link_id),"
	         .  " category_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->Category_List->GetTableName() . " (category_id)"
             . " )";
		return$this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Link_Id, int $Category_Id)
    {
        if($Link_Id > 0)
        {
            $insert_values = 
                array(
                    "link_id" => $Link_Id,
                    "category_id" => $Category_Id,
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

    public function DeleteSql(int $Link_Id)
    {
        if($Link_Id > 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE link_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Link_Id));
        }
        return false;
    }

}