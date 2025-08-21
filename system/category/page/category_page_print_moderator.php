<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Category\Page;

use Wonder_Link\Yomi_Search_PHP\System\Category\Page\Category_Page_Printer;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Category_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;

    private Link_Search_Manager $Link_Search_Manager;
    private Category_Search_Manager $Category_Search_Manager;
    private Category_Page_Printer $Category_Page_Printer;

    private string $Category_Key = "";
	private object $Category_Data;
    private string $Link_Type = "";

    private array $Link_Data_List = array();

	function __construct(&$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Category Page Print Moderator Error");
        $this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
        $this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
        $this->Category_Page_Printer = new Category_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        $this->Link_Type = $this->System->User->HttpQueries("type");
        if(preg_match("/^(new|renew)/", $this->System->Page_Mode))
        {
            $this->Link_Type = $this->System->Page_Mode;
            $this->System->Page_Mode = "category";
        }
        elseif($this->System->Page_Mode =="dir")
        {
            $this->System->Page_Mode = "category";
        }

        if($this->System->Page_Mode == "category")
        {
            if($this->System->User->HttpQueries("path"))
            {
                $this->SettingCategoryData($this->System->User->HttpQueries("path"));
                if($this->Category_Data === false)
                {
                    $this->Error_Manager->SetLog("指定したカテゴリが見つからない為、処理に失敗しました。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
                else
                {  
                    $this->SettingLinkData($this->Link_Type, $this->System->User->HttpQueries("sort"));
                    $this->Category_Page_Printer->PrintPage($this->Link_Data_List, intval($this->Link_Search_Manager->GetRegisteredLinkNumber()), $this->Category_Data);
                }
            }
        }

    }

    private function SettingLinkData(string $Type = "", string $Sort = "")
    {
        $this->Link_Search_Manager->ClearSearchData();
        switch ($Type)
        {
            case "new" :
                $this->Link_Search_Manager->SpecifySearchCriteriaOnRegisteredTimestamp(time() - intval($this->System->Config->Options["new_link_list_page"]->Pperiod()) * 24 * 3600);
            break;
            case "renew" : case "update" :
                $this->Link_Search_Manager->SpecifySearchCriteriaOnUpdateTimestamp(time() - intval($this->System->Config->Options["uodate_link_list_page"]->Period()) * 24 * 3600);
            break;
        }
        $this->Link_Search_Manager->SpecifySortCriteria($Sort);
        if($this->Category_Data instanceof Category_Data_Object)
        {
            $this->Link_Search_Manager->SearchLinkRegisteredInCategory($this->Category_Data->CategoryId());
        }
        else
        {
            $this->Link_Search_Manager->SearchLinks();
        }
        if(is_array($this->Link_Search_Manager->GetLinkList()))
        {
            $this->Link_Data_List = $this->Link_Search_Manager->GetLinkList();
        }
    }

	private function SettingCategoryData(string $Category_Key)
	{
		unset($this->Category_Data);
		$this->Category_Key = "";
		$this->Category_Search_Manager->ClearSearchData();
		$this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($Category_Key);
		$this->Category_Search_Manager->SearchCategories();
        $category_data = $this->Category_Search_Manager->GetCategoryList(0);
        if($category_data instanceof Category_Data_Object)
        {
            $this->System->Page_Title[] = $category_data->Title();
            $this->Category_Data = $category_data;
            $this->Category_Key = $category_data->Alias();
        }
	}

    private function ReplaceTimeZoneList(string $Template_Html)
    {
        $tag_replacer = new Template_Tag_Replacer("link_data");
        $value = DateTimeZone::listIdentifiers();
        $caption = DateTimeZone::listIdentifiers();
        $selected = array_pad(array(), count($value), "");
        $select_value_index = array_search("Asia/Tokyo", $value);
 
        if($select_value_index !== false and isset($selected[$select_value_index]))
        {
            $selected[$select_value_index] = "selected";
        }

        $values = array("value"=>$value, "caption"=>$caption, "selected"=>$selected);
        return $tag_replacer->Replace($values ,$Template_Html);
    }

}
