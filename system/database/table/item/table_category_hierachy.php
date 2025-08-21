<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Category_Hierachy extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}category_hierachy";
    protected string $Table_Alias = "category_hierachy";
    protected DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
    {
        $this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
        $sql = "CREATE TABLE " . $this->Table_Name
            . " ("
	        .   " hierachy_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
	        .   " parent_category_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 REFERENCES " . $this->DataBase_Manager->Category_List->GetTableName() . " (category_id),"
	        .   " child_category_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 REFERENCES " . $this->DataBase_Manager->Category_List->GetTableName() . " (category_id)"
            . " )";
		return$this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Parent_Category_Id, int $Child_Category_Id)
    {
        $insert_values = 
            array(
                "parent_category_id" => $Parent_Category_Id,
                "child_category_id" => $Child_Category_Id
            );
        $item_labels = array_keys($insert_values);
        $table_label = implode(", ", $item_labels);           
        $table_value = array_fill(0, count($insert_values), "?");
        $table_value = implode(",", $table_value);
        $sql = "INSERT INTO " . $this->Table_Name ." (" . $table_label . ") VALUES (" . $table_value . ")";
        return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $insert_values);
    }

    public function DeleteSql(int $Category_Id)
    {
        if($Category_Id >= 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE parent_category_id = ? OR child_category_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Category_Id, $Category_Id));
        }
        return false;
    }

}