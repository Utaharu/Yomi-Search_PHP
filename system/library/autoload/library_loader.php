<?php
namespace Sora_Kanata\Common_Library\Autoload;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Library_Loader
{
    private string $Library_Vender_Name = "";
    private string $Library_Name = "";
    private string $Library_Base_Name_Space = "";
    private string $Library_Base_Root = "";
    private object $Error_Manager;

    function __construct()
    {
        list($this->Library_Vender_Name, $this->Library_Name) = explode("\\", __NAMESPACE__);
        $this->Library_Base_Name_Space = $this->Library_Vender_Name . "\\" . $this->Library_Name;
        $this->Library_Base_Root = dirname(__FILE__, 2);
        spl_autoload_register(__CLASS__ . "::ScriptRequire");
        $this->Error_Manager = new Error_Manager("Library Loader Error");
    }

    /* # This NameSpace AutoLoader # */
    private function ScriptRequire($class_name)
    {
        if(preg_match("/^" . preg_quote($this->Library_Base_Name_Space, "/") .  "/i", $class_name))
        {
            $file_path = preg_replace("/^" . preg_quote($this->Library_Base_Name_Space, "/") ."\\\\(.+)/i", $this->Library_Base_Root . '\\\\$1.php' , $class_name);
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
