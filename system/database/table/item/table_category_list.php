<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Category_List extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}category_list";
    protected string $Table_Alias = "category_list";
    protected DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
    {
        $this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
        $sql = "CREATE TABLE " . $this->Table_Name
            . " ("
            .   " category_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
            .   " category_type_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->Category_Type->GetTableName() . " (type_id),"
            .   " alias VARCHAR(255) NOT NULL UNIQUE,"
	        .   " title TEXT NOT NULL,"
	        .   " description TEXT NOT NULL,"
            .   " image_url VARCHAR(255) NOT NULL,"
	        .   " is_enabled INT(1) NOT NULL DEFAULT 0 CHECK (0 <= is_enabled >= 1),"
            .   " for_sorting TEXT NOT NULL"
            . " )";
		return$this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Category_Type_Id, string $Alias, string $Title, string $For_Sorting, string $Description, string $Image_Url, bool $Is_Enabled)
    {
        $insert_values = 
            array(
                "category_type_id" => $Category_Type_Id,
                "alias" => $Alias,
                "title" => $Title,
                "description" => $Description,
                "image_url" => $Image_Url,
                "is_enabled" => $Is_Enabled,
                "for_sorting" => $For_Sorting
            );
        $item_labels = array_keys($insert_values);
        $table_label = implode(", ", $item_labels);           
        $table_value = array_fill(0, count($insert_values), "?");
        $table_value = implode(",", $table_value);
        $sql = "INSERT INTO " . $this->Table_Name ." (" . $table_label . ") VALUES (" . $table_value . ")";
        return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $insert_values);
    }

    public function UpdateSql(int $Category_Id, int $Category_Type_Id, string $Alias, string $Title, string $Description, string $Image_Url, string $Is_Enabled, $For_Sorting)
    {
        $update_values = 
            array(
               "category_type_id" => $Category_Type_Id,
               "alias" => $Alias,
               "title" => $Title,
               "description" => $Description,
               "image_url" => $Image_Url,
               "is_enabled" => $Is_Enabled,
               "for_sorting" => $For_Sorting
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
            $set_values[] = $Category_Id;

        $sql = "UPDATE " . $this->Table_Name . " SET " . implode(",", $set_item) . " WHERE category_id = ?";
        return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $set_values);
    }

    public function DeleteSql(int $Category_Id)
    {
        if($Category_Id > 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE category_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Category_Id));
        }
        return false;
    }

}