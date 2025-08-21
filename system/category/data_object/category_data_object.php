<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object;

/* Liblary */
use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Category_Data_Object extends Data_Object_Abstract_Class
{
    protected Error_Manager $Error_Manager;
    private int $Category_Id = 0;
    private int $Type_Id = 0;
    private string $Type_Name = "";
    private string $Alias = "";
    private string $Title = "";
    private string $Description = "";
    private string $Image_Url = "";
    private bool $Is_Enabled = false;
    private string $For_Sorting = "";

    private array $Child_Id_List = array();
    private int $Link_Count = 0;

    function __construct($Category_Values = null)
    {
        /* # - Set - Object Config - # */
        $this->Object_Config = 
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "category_id", "value" => &$this->Category_Id, "method_name" => "CategoryId", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "type_id", "value" => &$this->Type_Id, "method_name" => "TypeId", "readonly_flag" => false, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "type_name", "value" => &$this->Type_Name, "method_name" => "TypeName", "readonly_flag" => false, "required_flag" => true),
                        array("data_index" => 3, "data_name" => "alias", "value" => &$this->Alias, "method_name" => "Alias", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 4, "data_name" => "title", "value" => &$this->Title, "method_name" => "Title", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 5, "data_name" => "description", "value" => &$this->Description, "method_name" => "Description", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 6, "data_name" => "image_url", "value" => &$this->Image_Url, "method_name" => "ImageUrl", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 7, "data_name" => "is_enabled", "value" => &$this->Is_Enabled, "method_name" => "IsEnabled", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 8, "data_name" => "for_sorting", "value" => &$this->For_Sorting, "method_name" => "ForSorting", "readonly_flag" => true, "required_flag" => false),
                    ),
                'DataItem_Delimiter' => ",",
            );
        $this->Error_Manager = new Error_Manager("Category Data Object Error");
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

    public function CategoryId($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Category_Id;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Category_Id = intval($Value);
            }
        }
    }

    public function TypeId($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Type_Id;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Type_Id = intval($Value);
            }
        }
    }

    public function TypeName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Type_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Type_Name = $Value;
            }
        }
    }

    public function Alias($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Alias;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Alias = $Value;
            }
        }
    }

    public function Title($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Title;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Title = $Value;
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


    public function ImageUrl($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Image_Url;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Image_Url = $Value;
            }
        }
    }

   public function IsEnabled($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->Is_Enabled;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if($Value === 1 or $Value === "1" or $Value === true or preg_match("/true/i", $Value))
            {
                $this->Is_Enabled = true;
            }
            else
            {
                $this->Is_Enabled = false;
            }
        }       
    }

    public function ForSorting($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->For_Sorting;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->For_Sorting = $Value;
            }
        }
    }

    public function LinkCount($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Link_Count;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Link_Count = intval($Value);
            }
        }
    }

    public function AddChildCategoryId(int $Add_Category_Id)
    {
        $this->Child_Id_List[] = $Add_Category_Id;
    }

	public function ConvertFromDataBaseObject(object $Database_Category_Object)
	{

        if($Database_Category_Object->category_id)
        {
            $this->CategoryId($Database_Category_Object->category_id);
        }
        if($Database_Category_Object->category_type_id)
        {
            $this->TypeId($Database_Category_Object->category_type_id);
        }
        if($Database_Category_Object->type_name)
        {
            $this->TypeName($Database_Category_Object->type_name);
        }
        if($Database_Category_Object->alias)
        {
            $this->Alias($Database_Category_Object->alias);
        }
        if($Database_Category_Object->title)
        {
            $this->Title($Database_Category_Object->title);
        }
        if($Database_Category_Object->description)
        {
            $this->Description($Database_Category_Object->description);
        }
        if($Database_Category_Object->is_enabled)
        {
            $this->IsEnabled($Database_Category_Object->is_enabled);
        }
        if($Database_Category_Object->for_sorting)
        {
            $this->ForSorting($Database_Category_Object->for_sorting);
        }
	}

    protected function ObjectToMergeString(){}
    protected function KeywordSearchFlagger($Word){}

}