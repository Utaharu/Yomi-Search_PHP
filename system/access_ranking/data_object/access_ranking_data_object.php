<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Data_Object;

use Wonder_Link\Yomi_Search_PHP\System\Links\Data_Object\Link_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Access_Ranking_Data_Object extends Data_Object_Abstract_Class
{
    protected Error_Manager $Error_Manager;
    private int $Ranking_Number = 0;
    private int $Ranking_Point = 0;
    private Link_Data_Object $Link_Data_Object;

    function __construct($Access_Ranking_Values = null)
    {
        /* # - Set - Object Config - # */
        $this->Object_Config = 
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "ranking_number", "value" => &$this->Ranking_Number, "method_name" => "RankingNumber", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "ranking_point", "value" => &$this->Ranking_Point, "method_name" => "RankingPoint", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "link_data", "value" => &$this->Link_Data_Object, "method_name" => "LinkData", "readonly_flag" => true, "required_flag" => true),
                    ),
                'DataItem_Delimiter' => ",",
            );
        $this->Error_Manager = new Error_Manager("Access Ranking Data Object Error");
        /* # - DataItems Sort - #
           - #  Sort key = "dataindex"
        */
        $data_items_sort = array_column($this->Object_Config['DataItems'], "data_index");
        array_multisort($data_items_sort, SORT_ASC, SORT_NUMERIC, $this->Object_Config['DataItems']);

        if(is_string($Access_Ranking_Values))
        {
            if(is_file($Access_Ranking_Values) and file_exists($Access_Ranking_Values))
            {
                $this->FileToObject($Access_Ranking_Values);
            }
            else
            {
                $this->StringToObject($Access_Ranking_Values);
            }
        }
        elseif(is_array($Access_Ranking_Values))
        {
            $this->ArrayToObject($Access_Ranking_Values);
        }
    }

    public function RankingNumber($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Ranking_Number;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Ranking_Number = intval($Value);
            }
        }
    }

    public function RankingPoint($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Ranking_Point;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Ranking_Point = intval($Value);
            }
        }
    }

    public function LinkData($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Link_Data_Object;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if($Value instanceof Link_Data_Object)
            {
                $this->Link_Data_Object = $Value;
            }
        }
    }

    protected function ObjectToMergeString(){}
    protected function KeywordSearchFlagger($Word){}

}