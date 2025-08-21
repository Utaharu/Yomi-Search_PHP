<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Page\Registration;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;

class Link_Registration_Page_Printer
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

    public function PrintOperationFormPage()
    {
        require $this->Template_Path . 'registration_operation_form.html';     
    }

    public function PrintEditorFormPage(null|Link_Data_Object $Link_Data_Object = null)
    { 
        require $this->Template_Path . 'registration_form.html';
    }

    public function PrintCompleatePage(Link_Data_Object $Link_Data_Object)
    {
        require $this->Template_Path . "registration_compleate.html";
    }
}