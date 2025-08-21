<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Inheritance_Class;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;

abstract class Table_Abstract_Class
{
    abstract protected string $Table_Name{get; set;}
    abstract protected string $Table_Alias{get; set;}
    abstract protected DataBase_Manager $DataBase_Manager{get; set;}

    abstract public function CreateTable();

    public function GetTableName()
    {
        return $this->Table_Name;
    }

    public function SelectSql(array $Variables, string $Columns = "*", string $Join = "", array|string $Where = "", string $Group_By = "", string $Having = "", string $Order_By = "",  int $Offset = -1, int $Limit = -1,  string $Option = "")
    {
        $sql = "SELECT ". $Columns . " FROM " . $this->Table_Name;
        if($this->Table_Alias)
        {
            $sql .= " AS ". $this->Table_Alias;
        }
        if($Join)
        {
            $sql .= " JOIN " . $Join;
        }
        if(is_array($Where))
        {
            if(array_key_exists("or", $Where))
            {
                $Where['OR'] = array_merge($Where['OR'], $Where['or']);
                unset($Where['or']);
            }
            if(array_key_exists("OR", $Where))
            {
                $Where['OR'] = implode(" OR ", $Where['OR']);
            }
            if(array_key_exists("and", $Where))
            {
                $Where['AND'] = array_merge($Where['AND'], $Where['and']);
                unset($Where['and']);
            }
            if(array_key_exists("AND", $Where))
            {
                $Where['AND'] = implode(" AND ", $Where['AND']);
            }
            $Where = implode(" AND ", $Where);
        }
        if($Where)
        {
            $sql .= " WHERE ". $Where;
        }
        if($Group_By)
        {
            $sql .= " GROUP BY " . $Group_By;
        }
        if($Having)
        {
            $sql .= " HAVING " . $Having;
        }
        if($Order_By)
        {
            $sql .= " ORDER BY " . $Order_By;
        }
        if($Option)
        {
            $sql .= " " . $Option;
        }        
        if($Limit >= 0)
        {
            $sql .= " LIMIT " . $Limit;
            if($Offset >= 0)
            {
                $sql .= " OFFSET " . $Offset;
            }            
        }

        return $this->DataBase_Manager->DataBase->ExecuteQuery($sql, $Variables);
    }

}