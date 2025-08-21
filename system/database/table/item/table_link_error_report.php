<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Link_Error_Report extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}link_error_report";
	protected string $Table_Alias = "link_error";
    protected DataBase_Manager $DataBase_Manager;
	
	function __construct(DataBase_Manager &$DataBase_Manager)
	{
		$this->DataBase_Manager = &$DataBase_Manager;
	}

	public function CreateTable()
	{
		$sql = "CREATE TABLE " . $this->Table_Name
			. " ("
			.	" report_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
			.	" link_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->Link_List->GetTableName() . " (link_id),"
			.	" report_type_id BIGINT(20) UNSIGNED NOT NULL REFERENCES " . $this->DataBase_Manager->Link_Error_Report_Type->GetTableName() . " (type_id),"
			.	" description VARCHAR(500) NULL DEFAULT NULL,"
			.	" user_name VARCHAR(100) NULL DEFAULT NULL,"
			.	" mail_address VARCHAR(100) NULL DEFAULT NULL,"
			.	" ip_address VARCHAR(40) NULL DEFAULT NULL,"
			.	" report_timestamp BIGINT(255) UNSIGNED NOT NULL DEFAULT CURRENT_TIMESTAMP"
			. " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(int $Link_Id, int $Report_Type_Id, string $User_Name, string $Description, string $IpAddress, string $MailAddress, bool $Is_Broken_Link_Url = false, bool $Is_Broken_Banner_Url = false, bool $Is_Relocated = false, bool $Is_Violation_Terms = false, bool $Is_Other = false)
    {
		$insert_values = 
			array(
				"link_id" => $Link_Id,
				"report_type_id" => $Report_Type_Id,
				"description" => $Description,
				"user_name" => $User_Name,
				"mail_address" => $MailAddress,
				"ip_address" => $IpAddress,
				"report_timestamp" => time()
			);
		$item_labels = array_keys($insert_values);
		$table_label = implode(", ", $item_labels);           
		$table_value = array_fill(0, count($insert_values), "?");
		$table_value = implode(",", $table_value);
		$sql = "INSERT INTO " . $this->Table_Name ." (" . $table_label . ") VALUES (" . $table_value . ")";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $insert_values);
    }

    public function DeleteSql(int $Report_Id)
    {
        if($Report_Id > 0)
        {
            $sql = "DELETE FROM " . $this->Table_Name . " WHERE report_id = ?";
	    	return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Report_Id));
        }
		return false;
    }
	
}