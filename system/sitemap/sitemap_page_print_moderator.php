<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Sitemap;

use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Sitemap_Page_Print_Moderator
{
	private object $System;
    private Category_Search_Manager $Category_Search_Manager;
	private Error_Manager $Error_Manager;
    private Sitemap_Page_Printer $Sitemap_Page_Printer;

	function __construct(&$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Sitemap Page Print Moderator Error");
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
        $this->Sitemap_Page_Printer = new Sitemap_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        if($this->System->Page_Mode == "sitemap")
        {
            $this->Category_Search_Manager->ClearSearchData();
            $this->Category_Search_Manager->SpecifySearchCriteriaOnTypeName("ordinary");
            $this->Category_Search_Manager->SpecifySearchCriteriaOnIsEnabled(true);
            $this->Category_Search_Manager->GetCategoryHierachy();
            $this->Sitemap_Page_Printer->PrintPage($this->Category_Search_Manager->GetCategoryList());
        }
    }

}