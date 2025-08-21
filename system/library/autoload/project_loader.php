<?php
namespace Sora_Kanata\Common_Library\Autoload;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Project_Loader
{
    private string $Project_Vender_Name = "";
    private string $Project_Name = "";
    private string $Project_Base_Name_Space = "";
    private string $Project_Base_Root = "";
    private object $Error_Manager;

    function __construct(string $Script_Path, string $Name_Space)
    {
		require_once dirname(__FILE__) . "/library_loader.php";
		$library_loader = new Library_Loader();
        list($this->Project_Vender_Name, $this->Project_Name) = explode("\\", $Name_Space);
        $this->Project_Base_Name_Space = $this->Project_Vender_Name . "\\" . $this->Project_Name;
        $this->Project_Base_Root = dirname($Script_Path); 
        spl_autoload_register(__CLASS__ . "::ScriptRequire");
        $this->Error_Manager = new Error_Manager("Project Loader Error");
    }

    /* # This NameSpace AutoLoader # */
    private function ScriptRequire($class_name)
    {
        if(preg_match("/^" . preg_quote($this->Project_Base_Name_Space, "/") .  "/i", $class_name))
        {
            $file_path = preg_replace("/^" . preg_quote($this->Project_Base_Name_Space, "/") ."\\\\(.+)/i", $this->Project_Base_Root . '\\\\$1.php' , $class_name);
            $file_path = mb_strtolower($file_path);
            $file_path = strtr($file_path, "\\", "/");
            if(is_file($file_path) and file_exists($file_path))
            {
                require_once($file_path);
//               var_dump($debug_trace = debug_backtrace());
            }
            else
            {
                if(isset($this->Error_Manager))
                {     
                    $this->Error_Manager->SetLog(basename($file_path) . "のAutoloadに失敗しました。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
            }
        }
        return;
    }

}