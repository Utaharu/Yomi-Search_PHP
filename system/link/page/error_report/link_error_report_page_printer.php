<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Page\Error_Report;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Error_Report_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

	private string $Template_Path = "";

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Link Error Report Page Printer Error");
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

	public function PrintPage()
	{		
		$Eref = "";
		if(isset($_SERVER['HTTP_REFERER']))
		{
			$Eref = urlencode($_SERVER['HTTP_REFERER']);
		}
		require $this->Template_Path . 'no_link.html';
	}
}
