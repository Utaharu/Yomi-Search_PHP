<?php
namespace Sora_Kanata\Common_Library\Inheritance_Class;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Sora_Kanata\Common_Library\File\File_Manager;

abstract class Data_Object_Abstract_Class
{
    /* # - Property - # */
    /* - # Object Config # - */
    protected $Object_Config = array();

    /* - # Object Property - Manage Flag # - */
    protected array $Written_Flags = array();
    protected array $Required_Flags = array();

    /* - # Option / Extension - Properties # - */
    protected array $Option_Properties = array();
    
    protected string $File_Path = "";

    /* # - Manager - # */
    protected File_Manager $File_Manager;
    protected Error_Manager $Error_Manager;

    /* # - Abstract Function - # */
    abstract protected function ObjectToMergeString();
    abstract protected function KeywordSearchFlagger($Word);


    /* # - - - - - Extend Function - - - - - # */

    protected function IsReadonly(string $Method_Name)
    {
        $readonly_flag = false;
        if(isset($this->Object_Config['DataItems']))
        {
            $method_name_list = array_column($this->Object_Config['DataItems'], "method_name");
            $search_key = array_search($Method_Name, $method_name_list);
            if($search_key !== false)
            {
                if(isset($this->Object_Config['DataItems'][$search_key]['readonly_flag']) and is_bool($this->Object_Config['DataItems'][$search_key]['readonly_flag']))
                {
                    $readonly_flag = boolval($this->Object_Config['DataItems'][$search_key]['readonly_flag']);
                }
            }
        }
        return $readonly_flag;
    }

    protected function CheckIfWritten(string $Method_Name)
    {
        $wrote_key = array_search($Method_Name, $this->Written_Flags, true);
        if($wrote_key === false)
        {
            $this->Written_Flags[] = $Method_Name;
            return false;
        }
        else
        {
            return true;
        }
    }

/* ##### Check Required ##### */
    public function CheckRequiredFields()
    {
        $return_flag = true;
        $this->Required_Flags = array();
        /* # - Check Object_Config Setting - # */
        if(isset($this->Object_Config['DataItems']) and is_array($this->Object_Config['DataItems']))
        {
            foreach($this->Object_Config['DataItems'] as $config_line)
            {
                /* # - Check Config - Required Flag - # */
                if(isset($config_line['required_flag']) and $config_line['required_flag'] === true)
                {
                    /* # - Set Property - Require_Flags - # */
                    $this->Required_Flags[$config_line['data_name']] = false;
                    if(isset($config_line['value']) and $config_line['value'] !== "")
                    {
                        $this->Required_Flags[$config_line['data_name']] = true;
                    }
                }
            }
            /* # - Check False Data - Required_Flags Variable - # */
            if(in_array(false, $this->Required_Flags, true) === true )
            {
                $return_flag = false;
            }
        }
        return $return_flag;
    }

/* - # Convert To Object */
    /* # - Array -> Object - # */
    protected function ArrayToObject(array $Array_Values)
    {
        $key_name_list = array_column($this->Object_Config['DataItems'], "data_name");
        $key_index_list = array_column($this->Object_Config['DataItems'], "data_index");
        $key_list = array_combine($key_index_list, $key_name_list);

        if(is_array($Array_Values))
        {
            foreach($Array_Values as $item_key => $item_value)
            {
                $data_index = array_search($item_key, $key_list, true);
                if($data_index === false and array_key_exists($item_key, $key_list))
                {
                    $data_index = $item_key;
                }
                if(isset($this->Object_Config['DataItems'][$data_index]['method_name']) and method_exists($this, $this->Object_Config['DataItems'][$data_index]['method_name']))
                {
                    call_user_func(array($this, $this->Object_Config['DataItems'][$data_index]['method_name']), $item_value);
                }
            }
        }
        $this->CheckRequiredFields();
        return;
    }

    /* # - String -> Object - # */
    protected function StringToObject(string $String_Value)
    {
        if(is_string($String_Value) and $String_Value)
        {
            $String_Value = preg_replace("/[\r\n]$/", "", $String_Value);
            $items = null;
            $items = explode($this->Object_Config['DataItem_Delimiter'], $String_Value);
            if(is_array($items))
            {
                $this->ArrayToObject($items);
            }
        }
        return;
    }

    /* # - Load File -> Object - # */
    protected function FileToObject($File)
    {
        $return_flag = false;
        $this->File_Path = $File;
        if($this->File_Path)
        {
            $read_data = "";
            $read_data = $this->File_Manager->ReadToFile($this->File_Path, false);
            if($read_data)
            {
                $this->StringToObject($read_data);
            }
        }
        if($this->CheckRequiredFields() === false)
        {
            $this->Error_Manager->SetLog("必要な値がファイルに存在していません。");
        }
        else
        {
            $return_flag = true;}
        return $return_flag;
    }

/* - # Convert From Object */
    // # - Object -> Array
    public function ObjectToArray()
    {
        $key_name_list = array_column($this->Object_Config['DataItems'], "data_name");
        $value_list = array_column($this->Object_Config['DataItems'], "value");
        $items = array_combine($key_name_list, $value_list);
        return $items;
    }

    /* # - Object -> Save File - # */
    public function ObjectToFile($File = "")
    {
        $return_flag = false;
        if($File !== "" and $this->File_Path != $File)
        {
            $this->File_Path = $File;
        }
        if($this->CheckRequiredFields() === false)
        {
            $this->Error_Manager->SetLog("必須項目の入力が不足している為、保存されませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            if($this->File_Path)
            {
                $save_data = "";
                $save_data = $this->ObjectToMergeString();
                if($save_data)
                {
                    $this->File_Manager->WriteToFile($this->File_Path, $save_data);
                    if(is_file($this->File_Path))
                    {
                        umask(0);
                        chmod($this->File_Path, 0644);
                        $return_flag = true;
                    }
                    else
                    {
                        $this->Error_Manager->SetLog("ファイルを作成出来なかった為、保存できませんでした。");
                    }
                }
            }
        }
        return $return_flag;
    }

    public function GetFilePath()
    {
        return $this->File_Path;
    }
    
    public function DeleteFile()
    {
        $delete_flag = false;
        if($this->File_Path)
        {
            if(is_file($this->File_Path))
            {
                $delete_flag = unlink($this->File_Path);
            }
            else
            {
                $this->Error_Manager->SetLog("ファイルが見つからないので削除できませんでした。");
            }
        }
        return $delete_flag;
    }

/* - # Search Data Value */
    public function SearchValue($KeyWords)
    {
        $return_flag = false;

        $key_name_list = array_column($this->Object_Config['DataItems'], "data_name");
        
        $conditions_list = array();
        if(!is_array($KeyWords))
        {
            $conditions_list = preg_replace("/　/", " ", $KeyWords);
            $conditions_list = explode(" ", $conditions_list);
        }
        else
        {
            $conditions_list = $KeyWords;
        }

        $condition_flags = array();
        if(is_array($conditions_list))
        {
            foreach($conditions_list as $condition_key => $condition_value)
            {
                if($condition_value !== "")
                {
                    // Check Key - $condition_key = prpparty key
                    $target_proparty_key = array_search($condition_key, $key_name_list);
                    if($target_proparty_key !== false)
                    {
                        // # Match String Key(data_name) -> Check Target Property
                        if($this->Object_Config['DataItems'][$target_proparty_key]['value'] !== "")
                        {
                            $check_value = $this->Object_Config['DataItems'][$target_proparty_key]['value'];

                            if(preg_match("/^\s?==\s?(.+)/", $condition_value, $compare_value) and isset($compare_value[1]))
                            {
                                if(is_bool($check_value) === true and $check_value === $compare_value[1])
                                {
                                    $condition_flags[] = true;
                                }
                                elseif($check_value == $compare_value[1])
                                {
                                    $condition_flags[] = true;
                                }
                                else
                                {
                                    $condition_flags[] = false;
                                }
                            }
                            elseif(preg_match("/^\s?!=\s?(.+)/", $condition_value, $compare_value) and isset($compare_value[1]))
                            {
                                if(is_bool($check_value) === true and $check_value !== $compare_value[1])
                                {
                                    $condition_flags[] = true; 
                                }
                                elseif($check_value != $compare_value[1])
                                {
                                    $condition_flags[] = true;
                                }
                                else
                                {
                                    $condition_flags[] = false;
                                }
                            }
                            elseif(preg_match("/^\s?>\s?([0-9]+)/", $condition_value, $compare_value) and isset($compare_value[1]))
                            {
                                if($check_value > $compare_value[1])
                                {
                                    $condition_flags[] = true;
                                }
                                else
                                {
                                    $condition_flags[] = false;
                                }
                            }
                            elseif(preg_match("/^\s?<\s?([0-9]+)/", $condition_value, $compare_value) and isset($compare_value[1]))
                            {
                                if($check_value < $compare_value[1])
                                {
                                    $condition_flags[] = true;
                                }
                                else
                                {
                                    $condition_flags[] = false;
                                }
                            }
                            elseif(preg_match("/^\s?([0-9]+)\s?-\s?([0-9]+)/", $condition_value, $compare_value) and isset($compare_value[1]))
                            {
                                if(isset($compare_value[1]) and $check_value >= $compare_value[1] and $check_value <= $compare_value[2])
                                {
                                    $condition_flags[] = true;
                                }
                                else
                                {
                                    $condition_flags[] = false;
                                }
                            }
                            elseif($check_value == $condition_value)
                            {
                                $condition_flags[] = true; 
                            }
                            else
                            {
                                $condition_flags[] = false;
                            }
                        }
                }
                else
                {
                        $condition_flags[] = $this->KeywordSearchFlagger($condition_value);
                }
                }
            }
        }
        if(in_array(false, $condition_flags, true) === false)
        {
            $return_flag = true;
        }
        return $return_flag;
    }


/* - #  Option / Extension Property  */

    public function SetOptionProperty(string $Option_Name, $Flag_Value)
    {
        $result_flag = false;
        if(is_array($this->Option_Properties) and $Option_Name)
        {
            $name_list = array_column($this->Option_Properties, "name");
            $search_key = false;
            $search_key = array_search($Option_Name, $name_list, true);
            if($search_key === false)
            {
                $this->Option_Properties[] = array("name"=>$Option_Name, "value"=> $Flag_Value);
                $result_flag = true;
            }
            elseif(isset($this->Option_Properties[$search_key]) and $this->Option_Properties[$search_key]['name'] === $Option_Name)
            {
                $this->Option_Properties[$search_key]['value'] = $Flag_Value;
                $result_flag = true;
            }
        }
        return $result_flag;
    }


    public function DeleteOptionProperty(string $Option_Name)
    {
        $result_flag = false;
        if(is_array($this->Option_Properties) and $Option_Name)
        {
            $name_list = array_column($this->Option_Properties, "name");
            $search_key = false;
            $search_key = array_search($Option_Name, $name_list, true);
            if($search_key !== false and isset($this->Option_Properties[$search_key]) and $this->Option_Properties[$search_key]['name'] === $Option_Name)
            {
                unset($this->Option_Properties[$search_key]);
                $result_flag = true;
            }
        }
        return $result_flag;
    }

    public function GetOptionProperty(string $Option_Name)
    {
        $return_value = null;
        if(is_array($this->Option_Properties) and $Option_Name)
        {
            $name_list = array_column($this->Option_Properties, "name");
            $search_key = false;
            $search_key = array_search($Option_Name, $name_list, true);
            if($search_key !== false and isset($this->Option_Properties[$search_key]) and $this->Option_Properties[$search_key]['name'] === $Option_Name)
            {
                $return_value = $this->Option_Properties[$search_key]['value'];
            }
            else
            {
                $return_value = null;
            }
        }
        return $return_value;
    }

}