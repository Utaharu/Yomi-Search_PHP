<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Keyword_Ranking_Log extends Table_Abstract_Class
{    
    protected string $Table_Name = "{prefix}keyword_ranking_log";
	protected string $Table_Alias = "keyword_log";
    protected DataBase_Manager $DataBase_Manager;

	function __construct(DataBase_Manager &$DataBase_Manager)
	{
		$this->DataBase_Manager = &$DataBase_Manager; 
    }

	public function CreateTable()
	{
		$sql = "CREATE TABLE " . $this->Table_Name
			. " ("
			.	" log_id BIGINT(255) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
			.	" keyword_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,"
			.	" ip_address VARCHAR(40) NOT NULL DEFAULT '',"
			.	" timestamp BIGINT(20) UNSIGNED NOT NULL DEFAULT CURRENT_TIMESTAMP"
			. ")";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Keyword_Id, string $IpAddress, int $Timestamp = 0)
    {
		if($Timestamp === 0)
		{
			$Timestamp = time();
		}
        if($Keyword_Id > 0 and $Timestamp <= time() and filter_var($IpAddress, FILTER_VALIDATE_IP))
        {
            $insert_values = 
                array(
                    "keyword_id" => $Keyword_Id,
                    "ip_address" => $IpAddress,
                    "timestamp" => $Timestamp
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