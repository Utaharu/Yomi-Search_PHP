<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Plugin;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Plugin_Loader
{
    private object $System;
    private $File_Path_List = array();
    private Error_Manager $Error_Manager;

    function __construct(object $Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Plugin Loader Error");
        if(isset($this->System->Config->Plugin['dir']) and isset($this->System->Config->Plugin['file_pattern']) and isset($this->System->Config->Plugin['extension']))
        {
            $this->File_Path_List = glob($this->System->Config->Plugin['dir'] . $this->System->Config->Plugin['file_pattern'] . $this->System->Config->Plugin['extension']);
        }
    }

    public function LoadPlugin()
    {
        global $Yomi_Search;
        if(is_array($this->File_Path_List))
        {
            foreach($this->File_Path_List as $plugin_file)
            {
                if(is_file($plugin_file))
                {
                    require_once $plugin_file;
                }
            }
        }
    }

}