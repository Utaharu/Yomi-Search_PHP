<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Page\Error_Report;

use Wonder_Link\Yomi_Search_PHP\System\Link\Page\Error_Report\Link_Error_Report_Page_Printer;
use Wonder_Link\Yomi_Search_PHP\System\Link\Operator\Link_Error_Report_Operator;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Error_Report_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
    private Link_Error_Report_Operator $Report_Operator;
    private Link_Error_Report_Page_Printer $Report_Page_Printer;

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Report Page Print Moderator Error");
        $this->Report_Operator = new Link_Error_Report_Operator($Yomi_Search);
        $this->Report_Page_Printer = new Link_Error_Report_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        if($this->System->Page_Mode == "report")
        {
            if($this->System->User->HttpQueries("method") == "registration")
            {
                $this->Report_Operator->RegisterApplication();
            }
            else
            {
                $this->Report_Page_Printer->PrintPage();
            }

        }  
    }
}