<?php
/* 
 ----- User Object -----
 
 */
namespace Sora_Kanata\Common_Library\User;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class User_Manager
{
	private array $Object_Config;
	private Error_Manager $Error_Manager;

	private string $IpAddress = "";
	private string $HostName = "";
	private string $DeviceType = "";
	private string $Agent = "";
	private string $Referer = "";
	private array $HttpQueries = array();
	private array $Cookies = array();
	private array $Files = array();
	private $Flag_List = array();

	private string $System_Encoding = "UTF-8";
	private array $System_From_Encoding_List = array("UTF-8", "EUC-JP", "JIS", "Shift_JIS");

	function __construct($System_Config = null)
	{
		$this->Object_Config =
			array(
				// - # HostName
				"device_decisiton" =>
					array(
						"host" =>
							array(
								"docomo" => array("docomo.\ne\.jp"),
								"au" => array("ezweb\.ne\.jp"),
								"softbank" =>
									array(
										'jp-d\.ne\.jp',
										'jp-h\.ne\.jp',
										'jp-t\.ne\.jp',
										'jp-c\.ne\.jp',
										'jp-k\.ne\.jp',
										'jp-r\.ne\.jp',
										'jp-n\.ne\.jp',
										'jp-s\.ne\.jp',
										'jp-q\.ne\.jp'
									)
							)
						,
						// - # Agent
						"agent" =>
							array(
								'GMobile' => array("Googlebot-Mobile"),
								'iPhone' => array("iPhone"),
								'iPod' => array("iPod"),
								'Ymobile' => array("Y!J-SRD\/1\.0","Y!J-MBS\/1\.0"),
								'BlackBerry' => array("BlackBerry"),
								'Android' => array("(?!(Android.*SC-01C))(Android.*Mobile)"),
								'IEMobile' => array("IEMobile")
							)
					)
				,
			);
		if($System_Config instanceof System_Config)
		{
			$this->OverwriteObjectConfig($System_Config);
		}
		$this->Error_Manager = new Error_Manager("User Object Error");
		$this->ImportIp();
		$this->Agent();
		$this->Device();
		$this->Referer();
		$this->ImportHttpQueries();
		$this->Flag_List = array();
	}

	private function OverwriteObjectConfig(object $System_Config)
	{
		if(is_object($System_Config) and !is_null($System_Config))
		{
			$system_config_property = get_object_vars($System_Config);
			if(is_array($system_config_property))
			{
				$this->OverwriteObjectConfigValues($system_config_property, $this->Object_Config);
			}
		}
		return;
	}

	private function OverwriteObjectConfigValues(array $System_Config_Properties, array &$Overwrite_Target)
	{
		foreach($System_Config_Properties as $system_property_key => $system_property_value)
		{
			$system_property_key = strtolower($system_property_key);
			if(isset($Overwrite_Target[$system_property_key]))
			{
				if(is_array($system_property_value))
				{
					$this->OverwriteObjectConfigValues($system_property_value, $Overwrite_Target[$system_property_key]);
				}
				else
				{
					$Overwrite_Target[$system_property_key] = $system_property_value;
				}
			}
		}
		return;
	}

/* IP Infomation */	
	private function ImportIp()
	{
		$this->IpAddress = "";
		$this->HostName = "";
		//Get - Env
		$this->IpAddress = getenv('REMOTE_ADDR');
		$this->HostName = getenv('REMOTE_HOST');
		
		if(!$this->HostName or $this->HostName == $this->IpAddress)
		{
			$this->HostName = gethostbyaddr($this->IpAddress);
		}
		if(!$this->IpAddress or $this->HostName == $this->IpAddress)
		{
			$this->IpAddress = gethostbyname($this->HostName);
		}
		
		if(!$this->HostName and $this->IpAddress)
		{
			$this->HostName = $this->IpAddress;
		}
		if(!$this->IpAddress and $this->HostName)
		{
			$this->IpAddress = $this->HostName;
		}
		return;
	}

	public function IpAddress()
	{
		return $this->IpAddress;
	}

	public function HostName()
	{
		return $this->HostName;
	}
	
/* Agent */
	public function Agent()
	{
		$this->Agent = "";
		$this->Agent = getenv('HTTP_USER_AGENT');
		return $this->Agent;
	}
	
/* Device */
	public function Device()
	{
		$this->DeviceType = "pc";
		if(isset($this->Object_Config['host']) and is_array($this->Object_Config['host']))
		{
			$this->DeviceType = $this->IdentifyDevice($this->Object_Config['host'], $this->HostName);
		}

		if($this->DeviceType == "pc")
		{
			//Agent
			if(isset($this->Object_Config['agent']) and is_array($this->Object_Config['agent']))
			{
				$this->DeviceType = $this->IdentifyDevice($this->Object_Config['agent'], $this->Agent);
			}
		}
		return $this->DeviceType;
	}
	
	private function IdentifyDevice($Check_set, $Check_value)
	{
		$device_name = False;
		if(is_array($Check_set))
		{
			foreach($Check_set as $device_type => $device_pattern)
			{
				if(!is_array($device_pattern) and $device_pattern)
				{
					if(preg_match("/" . $device_pattern . "/", $Check_value))
					{
						$device_name = $device_type;
						break;
					}
				}
			}
		}
		if($device_name === false)
		{
			$device_name = "pc";
		}
		return $device_name;
	}

/* Referer */
	public function Referer()
	{
		$this->Referer = "";
		$this->Referer = getenv('HTTP_REFERER');
		return $this->Referer;
	}

/* HttpQuery */
	public function HttpQueries(string $Key_Name)
	{
		$query_value = "";
		if(isset($this->HttpQueries[$Key_Name]))
		{
			$query_value = $this->ConvertHttpQueries($this->HttpQueries[$Key_Name]);
		}
		return $query_value;
	}

	private function ImportHttpQueries()
	{
		$queries = array();
		if($_GET)
		{
			$queries = $_GET;
		}
		if($_POST)
		{
			if(!is_array($queries))
			{
				$queries = $_POST;
			}
			else
			{
				$queries = array_merge($queries, $_POST);
			}
		}
		$this->HttpQueries = $queries;
		return;
	}
	
	private function ConvertHttpQueries(string|array $Http_Query = array())
	{
		$converted_query = null;
		if(is_array($Http_Query))
		{
			$converted_query = array();
			foreach ($Http_Query as $key => $value)
			{
				if(is_array($value))
				{
					$value = $this->ConvertHttpQueries($value);
				}
				else
				{
					if($this->System_Encoding)
					{
						$value = mb_convert_encoding($value, $this->System_Encoding, $this->System_From_Encoding_List);
						$value = htmlspecialchars($value, ENT_XHTML and ENT_QUOTES, $this->System_Encoding);
					}
					else
					{
						$value = htmlspecialchars($value, ENT_XHTML and ENT_QUOTES);
					}
				}
				$converted_query[$key] = $value;
			}
		}
		else
		{
			$converted_query = "";
			if($this->System_Encoding)
			{
				$converted_query = mb_convert_encoding($Http_Query, $this->System_Encoding, $this->System_From_Encoding_List);
				$converted_query = htmlspecialchars($Http_Query, ENT_XHTML and ENT_QUOTES, $this->System_Encoding);
			}
			else
			{
				$converted_query = htmlspecialchars($Http_Query, ENT_XHTML and ENT_QUOTES);
			}
		}
		return $converted_query;
	}
	
/* Cookie */
	public function GetCookie(string $Key_Name)
	{
		$this->Cookies = $_COOKIE;
		if(isset($this->Cookies[$Key_Name]))
		{
			return $this->Cookies[$Key_Name];
		}
		return false;
	}

	public function SetCookie($CookieName, $CookieData, $LimitStamp = false)
	{
		if($LimitStamp === false)
		{
			$LimitStamp = time();
		}
		if($CookieName)
		{
			setcookie($CookieName, $CookieData, $LimitStamp);
		}
		return;
	}
	
/* upload files */
	public function GetFiles(string $Key_Name)
	{
		$this->Files = $_FILES;
		if(isset($this->Files[$Key_Name]))
		{
			return $this->Files[$Key_Name];
		}
		return false;
	}

/* Set Flag */
	public function SetFlag($Flag_Name, $Flag_Value)
	{
		$result_flag = false;
		if(is_array($this->Flag_List) and $Flag_Name)
		{
			$name_list = array_column($this->Flag_List, "name");
			$search_key = false;
			$search_key = array_search($Flag_Name, $name_list, true);
			if($search_key === false)
			{
				$this->Flag_List[] = array("name"=>$Flag_Name, "value"=> $Flag_Value);
				$result_flag = true;
			}
			elseif(isset($this->Flag_List[$search_key]) and $this->Flag_List[$search_key]['name'] === $Flag_Name)
			{
				$this->Flag_List[$search_key]['value'] = $Flag_Value;
				$result_flag = true;
			}
		}
		return $result_flag;
	}

	/* Delete Flag */
	public function DeleteFlag($Flag_Name)
	{
		$result_flag = false;
		if(is_array($this->Flag_List) and $Flag_Name)
		{
			$name_list = array_column($this->Flag_List, "name");
			$search_key = false;
			$search_key = array_search($Flag_Name, $name_list, true);
			if($search_key !== false and isset($this->Flag_List[$search_key]) and $this->Flag_List[$search_key]['name'] === $Flag_Name)
			{
				unset($this->Flag_List[$search_key]);
				$result_flag = true;
			}
		}
		return $result_flag;
	}
	
	public function GetFlagValue($Flag_Name)
	{
		$return_value = null;
		if(is_array($this->Flag_List) and $Flag_Name)
		{
			$name_list = array_column($this->Flag_List, "name");
			$search_key = false;
			$search_key = array_search($Flag_Name, $name_list, true);
			if($search_key !== false and isset($this->Flag_List[$search_key]) and $this->Flag_List[$search_key]['name'] === $Flag_Name)
			{
				$return_value = $this->Flag_List[$search_key]['value'];
			}
			else
			{
				$return_value = null;
			}
		}
		return $return_value;
	}

	public function SetSystemEncoding(string $Character_Encoding)
	{
		$server_enabled_encoding_list = mb_list_encodings();
		$find_flag = false;
		$find_flag = array_search($Character_Encoding, $server_enabled_encoding_list);
		if($find_flag !== false)
		{
			$this->System_Encoding = $Character_Encoding;
		}
	}

	public function SetSystemFromEncodingList(string|array $From_Encoding)
	{
		if($From_Encoding)
		{
			$this->System_From_Encoding_List = array();
			if(is_string($From_Encoding))
			{
				$encoding_list = explode(",", $From_Encoding);
			}
			if(is_array($encoding_list))
			{
				$server_enabled_encoding_list = mb_list_encodings();
				foreach($encoding_list as $encoding_string)
				{
					$find_flag = false;
					$find_flag = array_search($encoding_string, $server_enabled_encoding_list);
					if($find_flag !== false)
					{
						$this->System_From_Encoding_List[] = $encoding_string;
					}
				}
			}
		}
	}
}
