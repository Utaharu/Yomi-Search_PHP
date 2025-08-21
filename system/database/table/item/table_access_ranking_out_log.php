<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Access_Ranking_Out_Log extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}access_ranking_out_log";
	protected string $Table_Alias = "access_out_log";
	/* old name = rank */
    protected DataBase_Manager $DataBase_Manager;
	
	function __construct(DataBase_Manager &$DataBase_Manager)
	{
		$this->DataBase_Manager = &$DataBase_Manager;
	}

	public function CreateTable()
	{
		$sql = "CREATE TABLE " . $this->Table_Name
			. " ("
			.	" ranking_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
			.	" link_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 REFERENCES " . $this->DataBase_Manager->Link_List->GetTableName() . "(link_id),"
			.	" ip_address VARCHAR(40) NULL DEFAULT NULL,"
			.	" timesatamp INT(20) NULL DEFAULT CURRENT_TIMESTAMP"
			. " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Link_Id, string $IpAddress, int $Timestamp = 0)
    {
        if($Timestamp <= 0)
        {
            $Timestamp = time();
        }
        if(filter_var($IpAddress, FILTER_VALIDATE_IP))
        {
			$insert_values = 
				array(
					"link_id" => $Link_Id,
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

    public function DeleteSql(int $Link_Id)
    {
       	$sql = "DELETE FROM " . $this->Table_Name . " WHERE link_id = ?";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Link_Id));
    }
}