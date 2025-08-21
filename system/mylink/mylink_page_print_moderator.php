<?php
namespace Wonder_Link\Yomi_Search_PHP\System\MyLink;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\MyLink\MyLink_Page_Printer;

class MyLink_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
	private Link_Search_Manager $Link_Search_Manager;

    private MyLink_Page_Printer $MyLink_Page_Printer;
	private int $Total_Link_Number = 0;

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("MyLink Page Print Moderator Error");
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
        $this->MyLink_Page_Printer = new MyLink_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        if($this->System->Page_Mode == "mylink")
        {
            switch($this->System->User->HttpQueries('method'))
            {
                case 'add':
                    $mylink_operator = new MyLink_Operator($this);
                    $mylink_operator->AddMyLink($this->System->User->HttpQueries('id'));
                break;
                case 'delete':
                    $mylink_operator = new MyLink_Operator($this);
                    $mylink_operator->DeleteMyLink($this->System->User->HttpQueries('id'));
                break;
            }
            $this->MyLink_Page_Printer->PrintPage($this->GetMyLinkData(), $this->Total_Link_Number);
        }
    }

    private function GetMyLinkData()
	{
		$this->Total_Link_Number = 0;
		$this->Link_Search_Manager->ClearSearchData();
		if ($this->System->User->GetCookie('mylink4php'))
		{			
			$mylink_id_array = explode(',', $this->System->User->GetCookie('mylink4php'));
			foreach ($mylink_id_array as $tmp)
			{
				if($tmp)
				{
					$this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($tmp);
				}
				$this->Total_Link_Number++;
			}
			$this->Link_Search_Manager->SpecifySortCriteria($this->System->User->HttpQueries("sort"));
			$this->Link_Search_Manager->SearchLinks();
			return $this->Link_Search_Manager->GetLinkList();
		}
    }

}