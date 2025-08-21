<?php
namespace Sora_Kanata\Common_Library\Error\Data_Object;

use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;

class Error_Data_Object extends Data_Object_Abstract_Class
{
    /* # Proparty */
    /* # - Object Config */
    /* # - Error Data - # */
    private string $Title = "";
    private string $Description = "";
    private string $File = "";
    private int $File_Line_Number = -1;
    private string $Class_Name = "";
    private string $Function_Name = "";
    private int $TimeStamp = -1;

    function __construct(string $Title ="", string $Description = "" , string $File = "", string $Class = "" , string $Function = "", ?int $Line_Number = null)
    {
        $this->Object_Config =
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "title", "value" => &$this->Title, "method_name" => "Title", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "description", "value" => &$this->Description, "method_name" => "Description", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "file", "value" => &$this->File,  "method_name" => "File", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 3, "data_name" => "line_number", "value" => &$this->File_Line_Number, "method_name" => "LineNumber", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 4, "data_name" => "class_name", "value" => &$this->Class_Name,  "method_name" => "ClassName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 5, "data_name" => "function_name", "value" => &$this->Function_Name,  "method_name" => "FunctionName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 6, "data_name" => "timestamp", "value" => &$this->TimeStamp,  "method_name" => "", "readonly_flag" => true, "required_flag" => false)
                    ),
                "DataItem_Delimiter" => " "
            );
        $this->Title($Title);
        $this->Description($Description);
        $this->File($File);
        $this->ClassName($Class);
        $this->FunctionName($Function);
        $this->LineNumber($Line_Number);
        $this->TimeStamp = time();
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
            $this->Title = $Value;
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
            $this->Description = $Value;
       }
    }

    public function File($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->File;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_file($Value))
            {
                $this->File = $Value;
            }
        }
    }

    public function LineNumber($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->File_Line_Number;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->File_Line_Number = $Value;
            }
        }
    }    

    public function ClassName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        $caller = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        if(is_null($Value))
        {
            return $this->Class_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            $this->Class_Name = $Value;
        }
    }

    public function FunctionName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);
        if(is_null($Value))
        {
            return $this->Function_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            $this->Function_Name = $Value;
        }
    }

    // # - Object -> Array
    public function ObjectToMergeString()
    {
        $string = $this->OBjectToArray();
        $string = implode($this->Object_Config['DataItem_Delimiter'], $string); 
        return $string;
    }

   protected function KeywordSearchFlagger($Word)
   {
        return false;
   }
}