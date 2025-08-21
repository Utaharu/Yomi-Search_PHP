<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class\Table_Abstract_Class;

class Table_Temporary_Link_List extends Table_Abstract_Class
{
    protected string $Table_Name = "{prefix}temporary_link_list";
    protected string $Table_Alias = "temporary_link";
    protected DataBase_Manager $DataBase_Manager;
	
	function __construct(DataBase_Manager &$DataBase_Manager)
	{
		$this->DataBase_Manager = &$DataBase_Manager;
	}

	public function CreateTable()
	{
        $sql = "CREATE TABLE " . $this->Table_Name
            . " ("
            .   " temporary_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,"
            .   " site_name VARCHAR(500) NULL DEFAULT NULL,"
            .   " site_url VARCHAR(255) NULL DEFAULT NULL,"
            .   " banner_url VARCHAR(255) NULL DEFAULT NULL,"
            .   " description TEXT NULL DEFAULT NULL,"
            .   " user_name VARCHAR(255) NULL DEFAULT NULL,"
            .   " mail_address VARCHAR(100) NULL DEFAULT NULL,"
            .   " password VARCHAR(255) NULL DEFAULT NULL,"
            .   " ip_address VARCHAR(40) NULL DEFAULT NULL,"
            .   " update_timestamp BIGINT(255) UNSIGNED NULL DEFAULT NULL,"
            .   " registered_timestamp BIGINT(255) UNSIGNED NULL DEFAULT NULL,"
            .   " tag TEXT NULL DEFAULT NULL,"
	        .   " categories TEXT NULL DEFAULT NULL"
            . " )";
		return $this->DataBase_Manager->DataBase->ExecuteQuery($sql);
	}

    public function InsertSql(string $Site_Name, string $Site_Url, string $Banner_Url, string $Description, string $User_Name, string $MailAddress, string $Password ,string $IpAddress, int $Update_Timestamp, int $Registerd_Timestamp, string $Tags, string $Categories)
    {
        if(filter_var($IpAddress, FILTER_VALIDATE_IP) and filter_var($MailAddress, FILTER_VALIDATE_EMAIL) and filter_var($Site_Url, FILTER_VALIDATE_URL))
        {
            $insert_values = 
                array(
                    "site_name" => $Site_Name,
                    "site_url" => $Site_Url,
                    "banner_url" => $Banner_Url,
                    "description" => $Description,
                    "user_name" => $User_Name,
                    "mail_address" => $MailAddress,
                    "password" => $Password,
                    "ip_address" => $IpAddress,
                    "update_timestamp" => $Update_Timestamp,
                    "registered_timestamp" => $Registerd_Timestamp,
                    "tag" => $Tags,
                    "categories" => $Categories
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

    public function UpdateSql(int $Link_Id, string $Site_Name, string $Site_Url, string $Banner_Url, string $Description, string $User_Name, string $MailAddress, string $Password ,string $IpAddress, int $Update_Timestamp, int $Registerd_Timestamp, string $Tags, string $Categories)
    {
        $update_values = 
            array(
                "temporary_id" => null,
                "site_name" => $Site_Name,
                "site_url" => $Site_Url,
                "banner_url" => $Banner_Url,
                "description" => $Description,
                "user_name" => $User_Name,
                "mail_address" => $MailAddress,
                "password" => $Password,
                "ip_address" => $IpAddress,
                "update_timestamp" => $Update_Timestamp,
                "registered_timestamp" => $Registerd_Timestamp,
                "tag" => $Tags,
                "categories" => $Categories
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
        $set_values[] = $Link_Id;
    	$sql = "UPDATE " . $this->Table_Name . " SET " . implode(",", $set_item) ." WHERE link_id = ?";
    	return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $set_values);
    }

    public function DeleteSql(int $Temporary_Id)
    {
        if($Temporary_Id > 0)
        {
			$sql = "DELETE FROM " . $this->Table_Name . " WHERE temporary_id = ?";
			return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, array($Temporary_Id));
		}
        return false;
    }

}