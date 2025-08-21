<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Search;

use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Manager\Keyword_Ranking_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Search\Search_Page_Printer;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Search_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
	private Link_Search_Manager $Link_Search_Manager;
	private Category_Search_Manager $Category_Search_Manager;
	private Keyword_Ranking_Data_Manager $Keyword_Ranking_Manager;
	private Search_Page_Printer $Search_Page_Printer;

	function __construct(object $Yomi_Search)
	{
	    $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Search Result Page Print Moderator Error");
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
		$this->Keyword_Ranking_Manager = new Keyword_Ranking_Data_Manager($Yomi_Search);
		$this->Search_Page_Printer = new Search_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        if($this->System->Page_Mode == "search")
        {
			if(!$this->System->User->HttpQueries("word"))
			{
				$this->Search_Page_Printer->PrintFormPage();
			}
			else
			{
				// キーワードランキング用のデータを取得
				if($this->System->Config->Options["keyword_ranking"]->IsEnabled() and $this->System->Page_Number === 1)
				{
					$this->Keyword_Ranking_Manager->RecordSearchWord($this->System->User->HttpQueries('word'));
				}
				$this->Search_Page_Printer->PrintResultPage($this->GetLinkData(), $this->GetCategory());	
			}
        }
    }

    private function GetCategory()
	{
		$this->Category_Search_Manager->ClearSearchData();
		if($this->System->User->HttpQueries("kt_search"))
		{
			$this->Category_Search_Manager->SpecifySearchCriteriaOnKeyword($this->System->User->HttpQueries("word"), $this->Category_Search_Manager::KEYWORD_SEARCH_OR);
			$this->Category_Search_Manager->SearchCategories();
			return $this->Category_Search_Manager->GetCategoryList();
		}
		else
		{
			return false;
		}
	}
	
	private function GetLinkData()
	{
		$this->Link_Search_Manager->ClearSearchData();		
		$this->DateCriteria();
		$this->WordCriteria();
		$target_category_id = $this->CategoryCriteria();
		if($target_category_id)
		{
			$this->Link_Search_Manager->SearchLinkRegisteredInCategory($target_category_id);
		}
		else
		{
			$this->Link_Search_Manager->SearchLinks();
		}
		return $this->Link_Search_Manager->GetLinkList();
	}

	private function WordCriteria()
	{
		$search_expression_type = 0;
		$search_method = "";
		if($this->System->User->HttpQueries('search_method'))
		{
			/* old parameter */
			$search_method = $this->System->User->HttpQueries('search_method');
		}
		elseif($this->System->User->HttpQueries('method'))
		{
			$search_method = $this->System->User->HttpQueries('method');
		}
		switch($search_method)
		{
			case "0":
				$search_expression_type = $this->Link_Search_Manager::KEYWORD_SEARCH_OR;
			break;
			case "1" :
				$search_expression_type = $this->Link_Search_Manager::KEYWORD_SEARCH_AND;
			break;
			default :
				$search_expression_type = $this->Link_Search_Manager::KEYWORD_SEARCH_FORMULA;
			break;
		}
		$this->Link_Search_Manager->SpecifySearchCriteriaOnKeyword($this->System->User->HttpQueries("word"), $search_expression_type);
	}

	private function CategoryCriteria()
	{
		$search_category_alias = "";
		if($this->System->User->HttpQueries("category"))
		{
			$search_category_alias = $this->System->User->HttpQueries('category');
		}
		elseif($this->System->User->HttpQueries('search_kt'))
		{
			/* old parameter */
			$search_category_alias = $this->System->User->HttpQueries('search_kt');
		}

		if($search_category_alias)
		{
			$direct_category_flag = false;
			if($this->System->User->HttpQueries("search_kt_ex"))
			{
				/* old parameter */
				$direct_category_flag = false;
			}
			elseif($this->System->User->HttpQueries("category_type"))
			{
				$direct_category_flag = false;
			}
//階層判別を作ってない。
			$this->Category_Search_Manager->ClearSearchData();
			$this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($search_category_alias);
			$this->Category_Search_Manager->SpecifySearchCriteriaOnTypeName($this->System::ORDINARY_CATEGORY_TYPE);
			$this->Category_Search_Manager->SearchCategories();
			$category_data = $this->GetCategory(0);
			if($category_data)
			{
				return $category_data->CategoryId();
			}
			else
			{
				return false;
			}
		}
	}

	private function DateCriteria()
	{
		$day_search_type ="";
		if($this->System->User->HttpQueries("date"))
		{
			$date_search_type = $this->System->User->HttpQueries("date");
		}
		elseif($this->System->User->HttpQuery('search_day_ex'))
		{
			/* old parameter */
			$date_search_type = $this->System->User->HttpQueries("search_day_ex");
		}
		elseif($this->System->User->HttpQueries("search_day"))
		{
			/* old parameter */
			$date_search_type = $this->System->User->HttpQueries("search_day");
		}
		if($day_search_type)
		{
			$this->Link_Search_Manager->SpecifySearchCriteriaOnDate($date_search_type);
		}
	}

}