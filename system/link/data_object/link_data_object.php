<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object;

use DateTime;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Yomi_Search;

class Link_Data_Object extends Data_Object_Abstract_Class
{
    protected Error_Manager $Error_Manager;

/*
            .   " link_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 AUTO_INCREMENT PRIMARY KEY,"
            .   " site_name VARCHAR(500) NULL DEFAULT NULL,"
            .   " site_url VARCHAR(1000) NULL DEFAULT NULL,"
            .   " banner_url VARCHAR(1000) NULL DEFAULT NULL,"
            .   " description TEXT NULL DEFAULT NULL,"
            .   " user_name VARCHAR(255) NULL DEFAULT NULL,"
            .   " mail_address VARCHAR(100) NULL DEFAULT NULL,"
            .   " password VARCHAR(255) NULL DEFAULT NULL,"
            .   " ip_address VARCHAR(40) NULL DEFAULT NULL,"
            .   " update_timestamp BIGINT(255) UNSIGNED NULL DEFAULT NULL,"
            .   " registered_timestamp BIGINT(255) UNSIGNED NULL DEFAULT NULL,"
    */
    private int $Link_Id;
    private string $Site_Name;
    private string $Site_Url;
    private string $Banner_Url;
    private string $Description; // (message)
    private string $User_Name;
    private string $Mail_Address;
    private string $Password;
    private string $IpAddress;
    private int $Update_Timestamp; //(last_time) Now @  String Type = 2025/05/21(Wed) 08:32 -> Change Timestamp?
    private int $Registration_Timestamp;

    private array $Tags; // (keywd)
    private array $Ordinary_Categories;
    private array $Special_Categories;

    function __construct($Category_Values = null)
    {
        /* # - Set - Object Config - # */
        $this->Object_Config = 
            array(
                
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "link_id", "value" => &$this->Link_Id, "method_name" => "LinkId", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "site_name", "value" => &$this->Site_Name, "method_name" => "SiteName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 2, "data_name" => "site_url", "value" => &$this->Site_Url, "method_name" => "SiteUrl", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 3, "data_name" => "banner_url", "value" => &$this->Banner_Url, "method_name" => "Banner_Url", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 4, "data_name" => "description", "value" => &$this->Description, "method_name" => "Description", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 5, "data_name" => "user_name", "value" => &$this->User_Name, "method_name" => "UserName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 6, "data_name" => "mail_address", "value" => &$this->Mail_Address, "method_name" => "MailAddress", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 7, "data_name" => "password", "value" => &$this->Password, "method_name" => "Password", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 8, "data_name" => "ip_address", "value" => &$this->IpAddress, "method_name" => "IpAddress", "readonly_flag" => false, "required_flag" => false),
                        array("data_index" => 9, "data_name" => "update_timestamp", "value" => &$this->Update_Timestamp, "method_name" => "IpAddress", "readonly_flag" => false, "required_flag" => false),
                        array("data_index" => 10, "data_name" => "registration_timestamp", "value" => &$this->Registration_Timestamp, "method_name" => "IpAddress", "readonly_flag" => false, "required_flag" => false),
                        array("data_index" => 11, "data_name" => "tag", "value" => &$this->Tags, "method_name" => "Tags", "readonly_flag" => false, "required_flag" => false),
                        array("data_index" => 12, "data_name" => "ordinary_categories", "value" => &$this->Ordinary_Categories, "method_name" => "OrdinaryCategories", "readonly_flag" => false, "required_flag" => false),
                        array("data_index" => 13, "data_name" => "special_categories", "value" => &$this->Special_Categories, "method_name" => "Special_Categories", "readonly_flag" => false, "required_flag" => false),
                    ),
                'DataItem_Delimiter' => ",",
                "SpecialCategoryData_Delimiter" => "",
            );
        $this->Error_Manager = new Error_Manager("Object Error");
        /* # - DataItems Sort - #
           - #  Sort key = "dataindex"
        */
        $data_items_sort = array_column($this->Object_Config['DataItems'], "data_index");
        array_multisort($data_items_sort, SORT_ASC, SORT_NUMERIC, $this->Object_Config['DataItems']);

        if(is_string($Category_Values))
        {
            if(is_file($Category_Values) and file_exists($Category_Values))
            {
                $this->FileToObject($Category_Values);
            }
            else
            {
                $this->StringToObject($Category_Values);
            }
        }
        elseif(is_array($Category_Values))
        {
            $this->ArrayToObject($Category_Values);
        }
    }

    public function LinkId($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Link_Id;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Link_Id = intval($Value);
            }
        }
    }

    public function SiteName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Site_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Site_Name = $Value;
            }
        }
    }

    public function SiteUrl($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Site_Url;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(preg_match("/^https?:\/\/.+/", $Value) and filter_var($Value, FILTER_VALIDATE_URL))
            {
                $this->Site_Url = $Value;
            }
        }
    }

    public function UpdateTimestamp($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Update_Timestamp;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Update_Timestamp = $Value;
            }
        }
    }

    public function Password($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Password;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Password = $Value;
            }
        }
    }

    public function Description($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Description;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Description = $Value;
            }
        }
    }

    public function UserName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->User_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->User_Name = $Value;
            }
        }
    }

    public function MailAddress($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Mail_Address;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Mail_Address = $Value;
            }
        }
    }

    public function OrdinaryCategories($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Ordinary_Categories;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_array($Value))
            {
                foreach($Value as $category_data)
                {
                    if($category_data instanceof Category_Data_Object)
                    {
                        if($category_data->TypeName() == Yomi_Search::ORDINARY_CATEGORY_TYPE)
                        {
                            $this->Ordinary_Categories[] = $category_data;
                        }
                    }
                }
            }
            elseif($Value instanceof Category_Data_Object and $Value->TypeName() == Yomi_Search::ORDINARY_CATEGORY_TYPE)
            {
                $this->Ordinary_Categories[] = $Value; 
            }
            elseif(is_string($Value))
            {
                $this->Ordinary_Categories = $this->ConvertArrayFromSpaceDelimited($Value);
            }
            else
            {
                return false;
            }
        }
    }

    public function ClearOrdinaryCategories()
    {
        $this->Ordinary_Categories = array();
        return true;
    }

    public function SpecialCategories($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->Special_Categories;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_array($Value))
            {
                foreach($Value as $category_data)
                {
                    if($category_data instanceof Category_Data_Object)
                    {
                        if($category_data->TypeName() == Yomi_Search::SPECIAL_CATEGORY_TYPE)
                        {
                            $this->Special_Categories[] = $category_data;
                        }
                    }
                }
            }
            elseif($Value instanceof Category_Data_Object and $Value->TypeName() == Yomi_Search::SPECIAL_CATEGORY_TYPE)
            {
                $this->Special_Categories[] = $Value; 
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function ClearSpecialCategories()
    {
        $this->Special_Categories = array();
        return true;
    }

    public function RegistrationTimestamp($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Registration_Timestamp;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_integer($Value))
            {
                $this->Registration_Timestamp = intval($Value);
            }
        }
    }

    public function BannerUrl($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Banner_Url;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(preg_match("/^https?:\/\/.+/", $Value) and filter_var($Value, FILTER_VALIDATE_URL))
            {
                $this->Banner_Url = $Value;
            }
        }
    }

    public function IpAddress($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->IpAddress;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(filter_var($Value, FILTER_VALIDATE_IP))
            {
                $this->IpAddress = $Value;
            }
        }
    }

    public function Tags($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Tags;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Tags = $this->ConvertArrayFromSpaceDelimited($Value);
            }
        }
    }

// # explode space delimit : String -> Array
	private function ConvertArrayFromSpaceDelimited(string $Target)
    {
		$converted_list = array();
        // - # String -> Array
		if(is_string($Target))
		{
			$Target = mb_ereg_replace("　", " ", $Target);
			$Target = explode(" ", $Target);
		}
        // - # Make Return Value
		if(is_array($Target))
		{
			foreach($Target as $line)
			{
				if($line)
                {
                    $converted_list[] = $line;
                }
			}
		}
		return $converted_list;
	}

    public function ConvertObjectFromDataBaseObject(object $Database_Link_Data_Object)
	{
		if($Database_Link_Data_Object->link_id)
		{
			$this->LinkId($Database_Link_Data_Object->link_id);
		}
		if($Database_Link_Data_Object->site_name)
		{
			$this->SiteName($Database_Link_Data_Object->site_name);
		}
		if($Database_Link_Data_Object->site_url)
		{
			$this->SiteUrl($Database_Link_Data_Object->site_url);
		}
		if($Database_Link_Data_Object->update_timestamp)
		{
			$this->UpdateTimeStamp($Database_Link_Data_Object->update_timestamp);
		}
		if($Database_Link_Data_Object->password)
		{
			$this->Password($Database_Link_Data_Object->password);
		}
		if($Database_Link_Data_Object->description)
		{
			$this->Description($Database_Link_Data_Object->description);
		}
		if($Database_Link_Data_Object->user_name)
		{
			$this->UserName($Database_Link_Data_Object->user_name);
		}
		if($Database_Link_Data_Object->mail_address)
		{
			$this->MailAddress($Database_Link_Data_Object->mail_address);
		}
		if($Database_Link_Data_Object->registered_timestamp)
		{
			$this->RegistrationTimestamp($Database_Link_Data_Object->registered_timestamp);
		}
		if($Database_Link_Data_Object->banner_url)
		{
			$this->BannerUrl($Database_Link_Data_Object->banner_url);
		}
		if($Database_Link_Data_Object->ip_address)
		{
			$this->IpAddress($Database_Link_Data_Object->ip_address);
		}
    }

    protected function ObjectToMergeString(){}
    protected function KeywordSearchFlagger($Word){}

}