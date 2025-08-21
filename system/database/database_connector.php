<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase;

use Sora_Kanata\Common_Library\Inheritance_Class\Data_Object_Abstract_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

use PDO;
use PDOException;
use PDOStatement;

class DataBase_Connector extends Data_Object_Abstract_Class
{
	private string $Data_Source_Name = "";
	private string $Host_Address = '';
    private int $Port_Number = 0;
	private string $User_Name = '';
	private $Password = '';
	private $DataBase_Name = '';
	private $Table_Prefix = '';
	private $Charset = "";
	private $Options = 
		array(
		 	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);
  
    private object $Pdo;
    public $row = array();

	protected Error_Manager $Error_Manager;

    function __construct($Connecter_Values = null)
    {
        /* # - Set - Object Config - # */
        $this->Object_Config = 
            array(
                'DataItems' => 
                    array(
                        array("data_index" => 0, "data_name" => "data_source_name", "value" => &$this->Data_Source_Name, "method_name" => "DataSourceName", "readonly_flag" => true, "required_flag" => true),
                        array("data_index" => 1, "data_name" => "host_address", "value" => &$this->Host_Address, "method_name" => "HostAddress", "readonly_flag" => false, "required_flag" => true),
                        array("data_index" => 2, "data_name" => "port_number", "value" => &$this->Port_Number, "method_name" => "PortNumber", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 3, "data_name" => "user_name", "value" => &$this->User_Name, "method_name" => "UserName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 4, "data_name" => "password", "value" => &$this->Password, "method_name" => "Password", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 5, "data_name" => "database_name", "value" => &$this->DataBase_Name, "method_name" => "DataBaseName", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 6, "data_name" => "table_prefix", "value" => &$this->Table_Prefix, "method_name" => "TablePrefix", "readonly_flag" => true, "required_flag" => false),
                        array("data_index" => 7, "data_name" => "charset", "value" => &$this->Charset, "method_name" => "Charset", "readonly_flag" => true, "required_flag" => false),
                    ),
                'DataItem_Delimiter' => ",",
            );
        $this->Error_Manager = new Error_Manager("DataBase Connecter Object Error");
        /* # - DataItems Sort - #
           - #  Sort key = "dataindex"
        */
        $data_items_sort = array_column($this->Object_Config['DataItems'], "data_index");
        array_multisort($data_items_sort, SORT_ASC, SORT_NUMERIC, $this->Object_Config['DataItems']);

        if(is_string($Connecter_Values))
        {
            if(is_file($Connecter_Values) and file_exists($Connecter_Values))
            {
                $this->FileToObject($Connecter_Values);
            }
            else
            {
                $this->StringToObject($Connecter_Values);
            }
        }
        elseif(is_array($Connecter_Values))
        {
            $this->ArrayToObject($Connecter_Values);
        }
    }

    public function DataSourceName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Data_Source_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Data_Source_Name = $Value;
            }
        }
    }

	public function HostAddress($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Host_Address;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Host_Address = $Value;
            }
        }
    }

    public function PortNumber($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Port_Number;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_numeric($Value))
            {
                $this->Port_Number = intval($Value);
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

    public function DataBaseName($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->DataBase_Name;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->DataBase_Name = $Value;
            }
        }
    }

    public function TablePrefix($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Table_Prefix;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Table_Prefix = $Value;
            }
        }
    }

    public function Charset($Value = null)
    {
        $read_only = $this->IsReadonly(__FUNCTION__);     
        if(is_null($Value))
        {
            return $this->Charset;
        }
        elseif($read_only === false or ($read_only === true and $this->CheckIfWritten(__FUNCTION__) === false))
        {
            if(is_string($Value))
            {
                $this->Charset = $Value;
            }
        }
    }

    public function Connect(array $Options = array())
	{
		if(count($Options) > 0)
		{
			array_merge($this->Options, $Options);
		}
		$match_pattern = array("/%host_address%/i", "/%charset%/i", "/%data_base_name%/i", "/%port_number%/i", "/%user_name%/i", "/%password%/i");
		$replace_pattern = array($this->Host_Address, $this->Charset, $this->DataBase_Name, $this->Port_Number, $this->User_Name, $this->Password);
		$Data_Source_Name = preg_replace($match_pattern, $replace_pattern, $this->Data_Source_Name);

		try
		{
			$this->Pdo = new PDO($Data_Source_Name, $this->User_Name, $this->Password, $this->Options);
            return true;
		}
		catch(PDOException $pdo_error)
		{
            return $pdo_error;
		}
    }

    // クエリ発行
    public function ExecuteQuery(string $Sql, null|array $Variable = null)
	{
        try
        {
            $pdo_statement = null;
            $Sql = preg_replace("/{prefix}/", $this->Table_Prefix, $Sql);

            if(is_array($Variable))
            {
                $pdo_statement = $this->Pdo->prepare($Sql);
                if($pdo_statement)
                {             
                    $this->BindValue($pdo_statement, $Variable);
                    $pdo_statement->execute();
                }
            }
            else
            {
                $pdo_statement = $this->Pdo->query($Sql, PDO::FETCH_OBJ);                
            }
        }
        catch(PDOException $pdo_error)
        {
            print "$Sql<BR>";
            $this->Error_Manager->SetLog("データベースの処理でエラーが発生しました。<br />" . $pdo_error->getMessage());
            $this->Error_Manager->PrintLastErrorAsPage();
        }

        return $pdo_statement;
    }

    private function BindValue(PDOStatement &$Pdo_Statement, $Argument)
	{
		if(is_array($Argument))
		{
            $argument_index = 0;
			foreach($Argument as $argument_value)
			{
                $argument_index++;
				$bind_param_type = PDO::PARAM_STR;
				if(!is_null($argument_value))
				{
					list($bind_param_type, $bind_param_value) = $this->GetBindParameters($argument_value);
					$Pdo_Statement->bindValue($argument_index, $bind_param_value, $bind_param_type);
				}
			}
		}
		else
		{
			$bind_param_type = PDO::PARAM_STR;
            $bind_param_value = array();
			list($bind_param_type, $bind_param_value) = $this->GetBindParameters($Pdo_Statement, $Argument);
			$Pdo_Statement->bindValue(1, $bind_param_value, $bind_param_type);
		}
        return;
	}

	private function GetBindParameters($Value)
	{   
		$bind_param_type = PDO::PARAM_STR;
		if(is_bool($Value))
		{
			$bind_param_type = PDO::PARAM_BOOL;
            $Value = boolval($Value);
		}
		elseif(is_int($Value) or is_numeric($Value))
		{
			$bind_param_type = PDO::PARAM_INT;
            $Value = intval($Value);
		}
		elseif(is_string($Value))
		{
			$bind_param_type = PDO::PARAM_STR;
            $Value = strval($Value);
		}
		return array($bind_param_type, $Value);
	}

    public function EscapeValue(string $Value)
    {
        list($bind_param_type, $bind_value) = $this->GetBindParameters($Value);
        return $this->Pdo->quote($bind_value, $bind_param_type);
    }

    public function Close()
	{
        unset($this->Pdo);
    }

    protected function ObjectToMergeString(){}
    protected function KeywordSearchFlagger($Word){}

}