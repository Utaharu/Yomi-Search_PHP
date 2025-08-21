<?php
namespace Sora_Kanata\Common_Library\Error\Manager;

use Sora_Kanata\Common_Library\Error\Error_Printer;
use Sora_Kanata\Common_Library\Error\Data_Object\Error_Data_Object;

class Error_Manager
{
    /* ##### Property ##### */
	private string $Project_Name = "";
	private string $Default_Error_Title = "";
	private array $Error_Data_List = array();
	private $Error_Printer = null;

	function __construct(string $Default_Error_Title = "")
	{
		$this->Default_Error_Title = "";
		if($Default_Error_Title)
		{
			$this->Default_Error_Title = $Default_Error_Title;
		}
		list(,$this->Project_Name) = explode("\\", __NAMESPACE__);
		$this->Error_Printer = new Error_Printer();
	}

	public function SetLog(string $Description, string $Title = "", string $File = "", string $Class = "", string $Function = "", ?int $Line_No = null)
	{
		if($Title == "")
		{
			if($this->Default_Error_Title)
			{
				$Title = $this->Default_Error_Title;
			}
			else
			{
				$Title = $this->Project_Name . " Error";
			}
		}
		$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
		if(isset($backtrace[0]))
		{
			if(!$File or !is_file($File))
			{
				$File = $backtrace[0]['file'];
			}
			if(!$Class)
			{
				$Class = $backtrace[0]['class'];
			}
			if(!$Function)
			{
				$Function = $backtrace[0]['function'];
			}
			if(is_null($Line_No))
			{
				$Line_No = $backtrace[0]['line'];
			}
		}
		$error_data_object = new Error_Data_Object($Title, $Description, $File, $Class, $Function, $Line_No);
		if($error_data_object instanceof Error_Data_Object and $error_data_object->CheckRequiredFields())
		{
			$this->Error_Data_List[] = $error_data_object;
		}

		return;
	}

	public function PrintLastErrorAsPage(bool $System_Exit_Flag = true)
	{
		$error_data_object = array_pop($this->Error_Data_List);
		if($error_data_object instanceof Error_Data_Object)
		{
			$this->Error_Printer->PagePrint($error_data_object->Title(), $error_data_object->Description());
			if($System_Exit_Flag)
			{
				exit;
			}
		}
	}

	public function PrintLastErrorAsText(bool $System_Exit_Flag = false)
	{
		$error_data_object = array_pop($this->Error_Data_List);
		if($error_data_object instanceof Error_Data_Object)
		{
			$this->Error_Printer->TextPrint($error_data_object->Title(), $error_data_object->Description());
			if($System_Exit_Flag)
			{
				exit;
			}
		}
	}

	public function GetLog(int $Limit = 0)
	{
		$return_error_list = array();
		if($Limit > 0)
		{
			$return_error_list = array_slice($this->Error_Data_List, 0, $Limit);
		}
		else
		{
			$return_error_list = $this->Error_Data_List;
		}
		return $return_error_list;
	}
}