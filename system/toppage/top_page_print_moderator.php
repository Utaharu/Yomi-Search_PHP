<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Toppage;

use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Manager\Keyword_Ranking_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\TopPage\Top_Page_Printer;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Top_Page_Print_Moderator
{
    private object $System;
    private Error_Manager $Error_Manager;
    private Keyword_Ranking_Search_Manager $Keyword_Ranking_Search_Manager;
    private Category_Search_Manager $Category_Search_Manager;
    private Top_Page_Printer $Toppage_Printer;
    private array $Keyword_Ranking_Data_List = array();
    private array $Ordinary_Category_List = array();
    private array $Special_Category_List = array();
    
    function __construct(&$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
       	$this->Error_Manager = new Error_Manager("Top Page Print Moderator Error");
        $this->Keyword_Ranking_Search_Manager = new Keyword_Ranking_Search_Manager($Yomi_Search);
        $this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
        $this->Toppage_Printer = new Top_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        if(!$this->System->Page_Mode)
        {
            if($this->System->Config->Options["keyword_ranking"]->IsEnabled())
            {
                $this->Keyword_Ranking_Data_List = $this->Keyword_Ranking_Search_Manager->GetRankingData(24*60*60*7, time(), 5);
            }
            $this->Category_Search_Manager->ClearSearchData();
//            $this->Category_Manager->SpecifySearchCriteriaOnTopDisplay();
            $this->Category_Search_Manager->SpecifySearchCriteriaOnTypeName($this->System::ORDINARY_CATEGORY_TYPE);
            $this->Category_Search_Manager->SearchCategories();
            $this->Ordinary_Category_List = $this->Category_Search_Manager->GetCategoryList();
            $this->Category_Search_Manager->ClearSearchData();
            $this->Category_Search_Manager->SpecifySearchCriteriaOnTypeName($this->System::SPECIAL_CATEGORY_TYPE);
            $this->Category_Search_Manager->SpecifySearchCriteriaOnIsEnabled(true);
            $this->Category_Search_Manager->SearchCategories();
            $this->Special_Category_List = $this->Category_Search_Manager->GetCategoryList();
            $this->Toppage_Printer->PrintPage($this->Ordinary_Category_List, $this->Special_Category_List, $this->Keyword_Ranking_Data_List);
        }
    }

}