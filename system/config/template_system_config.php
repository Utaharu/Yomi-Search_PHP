<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;

class System_Config
{
	private Object $System;
	public bool $Debug_Mode = false;
	public string $Root;
	public array $Directory_List;
	public array $Plugin;
    private array $DataBase;
	public array $Options = array();
	private array $Option_File_List = array();

    function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Root = dirname(__DIR__, 2);
		$this->DataBase = 
			array(
				'data_source_name' => "%data_source_name%",/* データソース名*/
				'host_address' => "%host_address%",/* データベースのホスト名 */
				'port_number' => "%port_number%",
				'user_name' => "%user_name%",/* ログインに使用するユーザ名 */
				'password' => "%password%", /* ログインに使用するパスワード */
				'database_name' => "%database_name%",/* データベース名 */
				'table_prefix' => "%table_prefix%",/* テーブルの先頭に付加するプレフィックス */
				'charset' => "%charset%"//"utf8mb4"/* 文字コードの指定 */		
			);

		$this->Directory_List = 
			array(
				'pc_template' => $this->Root . "/template/pc/",
				'phone_template' => $this->Root ."/template/phone/",
				'setup_template' => $this->Root ."/template/setup/",
				'administrator_template' => $this->Root ."/template/admin/",
				'css' => $this->Root . "/css/",
				'image' => $this->Root."img"
			);

		$this->Plugin =
			array(
				'dir' => $this->Root . "/plugin/*",
				'file_pattern' => "*_loader",
				'extension' => ".php"
			);
    }

    public function GetDataBaseSetting($Key_Name)
    {
        if($Key_Name)
        {
            if(isset($this->DataBase[$Key_Name]))
            {
                return $this->DataBase[$Key_Name];
            }
            else
            {
                return;
            }
        }
    }

	public function LoadOptions()
    {
		$option_dir = dirname(__FILE__) ."/option/item";
		$option_name_space = "Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item\\";
        if(file_exists($option_dir))
        {
			$this->Option_File_List = glob($option_dir . "/*.php");
		}
        if(is_array($this->Option_File_List))
        {
            foreach($this->Option_File_List as $file)
            {
                $file_name = pathinfo($file, PATHINFO_FILENAME);
                $load_class_name =  $option_name_space. $file_name;
                if(class_exists(($load_class_name)))
                {
                    $option_instance = new  $load_class_name($this->System);
					if($option_instance->GetOptionGroupName())
					{
	                    $this->Options[$option_instance->GetOptionGroupName()] = $option_instance;
					}
                }
            }
        }
    }

}