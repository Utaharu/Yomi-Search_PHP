<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Data_Object;

/* Liblary */
use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Keyword_Ranking_Data_Object extends Data_Object_Abstract_Class
{
    protected Error_Manager $Error_Manager;
    private int $Ranking_Number;
    private int $Ranking_Point;
    private string $Ranking_Keyword;

    function __construct($Keyword_Ranking_Values = null)
    {
        /* # - Set - Object Config - # */
        $this->Object_Config = 
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "ranking_number", "value" => &$this->Ranking_Number, "method_name" => "RankingNumber", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "ranking_point", "value" => &$this->Ranking_Point, "method_name" => "RankingPoint", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "ranking_keyword", "value" => &$this->Ranking_Keyword, "method_name" => "RankingKeyword", "readonly_flag" => true, "required_flag" => true),
                    ),
                'DataItem_Delimiter' => ",",
            );
        $this->Error_Manager = new Error_Manager("Keyword Ranking Data Object Error");
        /* # - DataItems Sort - #
           - #  Sort key = "dataindex"
        */
        $data_items_sort = array_column($this->Object_Config['DataItems'], "data_index");
        array_multisort($data_items_sort, SORT_ASC, SORT_NUMERIC, $this->Object_Config['DataItems']);

        if(is_string($Keyword_Ranking_Values))
        {
            if(is_file($Keyword_Ranking_Values) and file_exists($Keyword_Ranking_Values))
            {
                $this->FileToObject($Keyword_Ranking_Values);
            }
            else
            {
                $this->StringToObject($Keyword_Ranking_Values);
            }
        }
        elseif(is_array($Keyword_Ranking_Values))
        {
            $this->ArrayToObject($Keyword_Ranking_Values);
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

    public function RankingKeyword($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Ranking_Keyword;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Ranking_Keyword = $Value;
            }
        }
    }

    protected function ObjectToMergeString(){}
    protected function KeywordSearchFlagger($Word){}

}