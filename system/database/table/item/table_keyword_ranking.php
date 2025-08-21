<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Keyword_Ranking extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}keyword_ranking";
    protected string $Table_Alias = "keyword_ranking";
    protected DataBase_Manager $DataBase_Manager;

    function __construct(DataBase_Manager &$DataBase_Manager)
    {
        $this->DataBase_Manager = &$DataBase_Manager;
    }

	public function CreateTable()
	{
		$sql = "CREATE TABLE " . $this->Table_Name
            . " ("
            .   " keyword_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,"
	        .   " is_enabled INT(1) NOT NULL CHECK (0 <= is_enabled >= 1),"
	        .   " keyword VARCHAR(255) NOT NULL DEFAULT '' UNIQUE,"
	        .   " display_keyword VARCHAR(255) NULL DEFAULT NULL,"
            .   " total_count BIGINT(100) NOT NULL CHECK (0 <= total_count),"
            .   " PRIMARY KEY (keyword_id, keyword )"
            . " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(string $Keyword, bool $Is_Enabled) 
    {
        if($Keyword)
        {
            $insert_values = 
                array(
                    "is_enabled" => $Is_Enabled,
                    "keyword" => $Keyword,
                    "display_keyword" => $Keyword,
                    "total_count" => 0
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

    public function UpdateSql(int $Keyword_Id, string $Display_Keyword = "", int|null $Total_Count = null, bool|null $Is_Enabled = null)
    {
        if($Keyword_Id > 0)
        {
            $update_values = 
                array(
                    "is_enabled" => $Is_Enabled,
                    "display_keyword" => $Display_Keyword,
                    "total_count" => $Total_Count
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
            $set_values[] = $Keyword_Id;
            $sql = "UPDATE " . $this->Table_Name . " SET " . implode(",", $set_item) ." WHERE keyword_id = ?";
            return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $set_values);
        }
        return false;
    }

    public function DeleteSql(int $Keyword_Id)
    {
        if($Keyword_Id > 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE keyword_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Keyword_Id));
		}
        return false;
    }

}