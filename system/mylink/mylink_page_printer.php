<?php
namespace Wonder_Link\Yomi_Search_PHP\System\MyLink;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class MyLink_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

	private string $Template_Path = "";

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("MyLink Page Printer Error");
        switch($this->System->Page_Type)
        {
            default :
				$this->Template_Path = $this->System->Config->Directory_List['pc_template'];
			break;
            case "mobile" :
                $this->Template_Path = $this->System->Config->Directory_List['phone_template'];
            break;
        }

	}

	public function PrintPage(array $MyLink_List)
	{
		$title = 'マイリンク';
/*
		$arg = array($Form['page'], $log_count, $cfg['hyouji'], '&mode='.$Form['mode'].'&sort='.$Form['sort'], $cfg['script']);
		$mokuji = Navigation::Page($arg);
*/
		require $this->Template_Path . 'mylink.html';

    }


}