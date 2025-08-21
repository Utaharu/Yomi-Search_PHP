<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Manager;

use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;

class Link_Search_Manager
{
    public const KEYWORD_SEARCH_OR = 0;
    public const KEYWORD_SEARCH_AND = 1;
    public const KEYWORD_SEARCH_FORMULA = 2;

    private object $System;
   	private Error_Manager $Error_Manager;
    private Category_Search_Manager $Category_Search_Manager;

    private array $Link_List;
	private int $Registered_Link_Number;
    private string $Sql_Sort_Order = "";
	private array $Sql_Where_Variables = array();
  	private array $Sql_Log_Search_Where = array();

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Link Data Search Manager Error");
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
    }

    public function ClearSearchData()
    {
        $this->Sql_Log_Search_Where = array();
		$this->Sql_Where_Variables = array();
		$this->Registered_Link_Number = 0;
		$this->Link_List = array();
    }

	public function SpecifySearchCriteriaOnLinkId(int $Link_Id)
	{
		if($Link_Id > 0)
		{
			$this->Sql_Log_Search_Where["OR"][] = "(link_id = {$Link_Id})";
			return true;
		}
		else
		{
			return false;
		}
	}

	public function SpecifySearchCriteriaOnSiteUrl(string $Site_Url)
	{

		if(filter_var($Site_Url, FILTER_VALIDATE_URL))
		{
			$this->Sql_Where_Variables[] = $Site_Url;
			$this->Sql_Log_Search_Where[] = "(site_url = ?)";
			return true;
		}
		else
		{
			return false;
		}
	}

	public function SpecifySearchCriteriaOnSiteName(string $Site_Name)
	{
		if($Site_Name)
		{
			$this->Sql_Where_Variables[] = "%" . $Site_Name . "%";
			$this->Sql_Log_Search_Where[] = "(site_name = ?)";
		}
	}

	public function SpecifySearchCriteriaOnUpdateTimestamp(int $Timestamp, bool $Greater_Than = false)
	{
		$this->Sql_Where_Variables[] = $Timestamp;
		if($Greater_Than)
		{
			$this->Sql_Log_Search_Where[] = "(update_timestamp < ?)";
		}
		else
		{
	        $this->Sql_Log_Search_Where[] = "(update_timestamp > ?)";
		}
		return true;
	}

	public function SpecifySearchCriteriaOnRegisteredTimestamp(int $Timestamp, bool $Greater_Than = false)
	{
		$this->Sql_Where_Variables[] = $Timestamp;
		if($Greater_Than)
		{
			$this->Sql_Log_Search_Where[] = "(registered_timestamp < ?)";
		}
		else
		{
			$this->Sql_Log_Search_Where[] = "(registered_timestamp > ?)";
		}
		return true;
	}

	public function SpecifySearchCriteriaOnDate(string $Day_Setting = "")
	{
		// 日付検索部分
		if(preg_match('/^today-?(\d*)$/', $Day_Setting, $match))
		{
			// today-x
			if($match[1] > 10000)
			{
				$match[1] = 0;
			}
			$bf_day = time() - 86400 * intval($match[1]);
			$this->Sql_Where_Variables[] = $bf_day;
			$this->Sql_Where_Variables[] = $bf_day;
			$this->Sql_Log_Search_Where[]  = "(update_timestamp > ? OR registered_timestamp > ?)";
		}
		elseif(preg_match('/^(\d+)\-(\d+)$/', $Day_Setting, $match))
		{
			// [str_day]-[end_day]
			$start_time = time() - 86400 * intval($match[1]);
			$end_time = time() - 86400 * intval($match[2]);
			$this->Sql_Where_Variables[] = $start_time;
			$this->Sql_Where_Variables[] = $end_time;
			$this->Sql_Where_Variables[] = $start_time;
			$this->Sql_Where_Variables[] = $end_time;
			$this->Sql_Log_Search_Where[]  = "((update_timestamp BETWEEN ? AND ?) OR (registerd_timestamp BETWEEN ? AND ?))";
		}
		elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $Day_Setting, $match))
		{
			// year/mon/day
			$month = array('01'=>'January', '02'=>'February', '03'=>'March', '04'=>'April', '05'=>'May', '06'=>'June', '07'=>'July', '08'=>'August', '09'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
			$mon = $month[$match[2]];
			$start_time = intval(strtotime ("{$match[3]} {$mon} {$match[1]}"));
			$end_time = $start_time + 86400;
			$this->Sql_Where_Variables[] = $start_time;
			$this->Sql_Where_Variables[] = $end_time;
			$this->Sql_Where_Variables[] = $start_time;
			$this->Sql_Where_Variables[] = $end_time;			
			$this->Sql_Log_Search_Where[] = "((update_timestamp BETWEEN ? AND ?) OR (registered_timestamp BETWEEN ? AND ?)";
		}
		else
		{
			$this->Error_Manager->SetLog("日付指定のコマンドが正しく無い為、リンクの検索が行えませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		return;
	}

	public function SpecifySearchCriteriaOnKeyword(string $Keyword, int $Join_Type = 1)
	{
		// 検索構文の解析
		$Keyword = $this->System->DataBase->EscapeStringValues($Keyword);
		$Keyword = trim(str_replace('　', ' ', $Keyword), ' ');
		switch($Join_Type)
		{
			case self::KEYWORD_SEARCH_OR :
                $Keyword = str_replace(" ", " OR ", $Keyword);
			break;
			case self::KEYWORD_SEARCH_AND :
				$Keyword = str_replace(" ", " AND ", $Keyword);
			break;
		}
		$Keyword_List = explode(" ", $Keyword);
		if(is_array($Keyword_List))
		{
			$add_type = "";
			foreach($Keyword_List as $search_word)
			{
				if($search_word)
				{
					switch($add_type)
					{
						case "or" :
							$this->UseOrTypeInKeywordSearch($search_word);
							$add_type = "";
						break;
						case "and" :
							$this->UseAndTypeInKeywordSearch($search_word);
							$add_type = "";
						break;
						case "not" :
							$this->UseNotTypeInKeywordSearch($search_word);
							$add_type;
						break;
					}
					if(preg_match("/^or$/i", $search_word))
					{
						$add_type = "or";
					}
					elseif(preg_match("/^not$/i", $search_word))
					{
						$add_type = "not";
					}
					elseif(preg_match("/^and$/",$search_word))
					{
						$add_type = "and";
					}

				}
			}
		}
	}

	private function UseAndTypeInKeywordSearch(string $Word)
	{
		if($Word)
		{
			$Word = $this->System->DataBase_Manager->DataBase->EscapeValue($Word);
			$this->Sql_Log_Search_Where['AND'][] = "(link_id LIKE '%{$Word}%' OR user_name LIKE '%{$Word}% OR site_name LIKE '%{$Word}%' OR description LIKE '%{$Word}%' OR site_url LIKE '%{$Word}%')";
			return true;
		}
	}

	private function UseOrTypeInKeywordSearch(string $Word)
	{
		if($Word)
		{
			$Word = $this->System->DataBase_Manager->DataBase->EscapeValue($Word);
			$this->Sql_Log_Search_Where['OR'][] = "(link_id LIKE '%{$Word}%' OR user_name LIKE '%{$Word}% OR site_name LIKE '%{$Word}%' OR description LIKE '%{$Word}%' OR site_url LIKE '%{$Word}%')";
			return true;
		}
	}

	private function UseNotTypeInKeywordSearch(string $Word)
	{
		if($Word)
		{
			$Word = $this->System->DataBase_Manager->DataBase->EscapeValue($Word);
			$this->Sql_Log_Search_Where["AND"][] = "(link_id collate NOT LIKE '%{$Word}%' AND user_name collate NOT LIKE '%{$Word}%' AND site_name collate NOT LIKE '%{$Word}%' AND description collate NOT LIKE '%{$Word}%' AND site_url NOT LIKE '%{$Word}%')";
			return true;
		}		
	}

    public function SpecifySortCriteria(string $Sort_Key = "")
    {
        $this->Sql_Sort_Order = "";
        if(array_search($Sort_Key, $this->System->Config->Options["sort_type"]) === false)
        {
            $Sort_Key = "update_desc";
        }    
        switch($Sort_Key)
        {
            case 'registration_desc'  :
				$this->Sql_Sort_Order  = 'registered_timestamp DESC';
				return true;
			break;
            case 'registration_asc'  :
				$this->Sql_Sort_Order  = 'registered_timestamp';
				return true;
			break;
            case 'update_desc':
				$this->Sql_Sort_Order  = 'update_timestamp DESC';
				return true;
			break;
            case 'update_asc' :
				$this->Sql_Sort_Order  = 'update_timestamp';
				return true;
			break;
            case 'site_name_asc' :
				$this->Sql_Sort_Order  = 'site_name';
				return true;
			break;
            case 'site_name_desc' :
				$this->Sql_Sort_Order  = 'site_name DESC';
				return true;
			break;
            default :
				$this->Sql_Sort_Order  = "link_id DESC";
			break;
        }
        
    }
/*
	private function GetSelectRankingSql()
	{
		$select_ranking_sql = null;
        $time = time();
		$end = $time;
		if($this->System->Config->Options->GetValue("in_access_ranking", "is_enabled"))
		{
			$in_access_ranking_start_time = $time - intval($this->System->Config->Options->GetValue("in_access_ranking", "aggregation_period")) * 86400;
			$select_ranking_sql[] = "(SELECT COUNT(access_in.link_id) AS period_in_count FROM " . $this->System->DataBase_Manager->Access_Ranking_In_Log->GetTableName() . " AS access_in WHERE access_in.timestamp BETWEEN " . $in_access_ranking_start_time . " AND " . $end . " AND access_in.link_id = link_id)";
		}
		if($this->System->Config->Options->GetValue("out_access_ranking", "is_enabled"))
		{
			$out_access_ranking_start_time = $time - intval($this->System->Config->Options->GetValue("out_access_ranking", "aggregation_period")) * 86400;
			$select_ranking_sql[] = "(SELECT COUNT(access_out.link_id) AS period_out_count FROM " . $this->System->DataBase_Manager->Access_Ranking_Out_Log->GetTableName() . " AS access_out WHERE access_out.timestamp BETWEEN " . $out_access_ranking_start_time . " AND " . $end . " AND access_out.link_id = link_id)";
		}
		if(is_array($select_ranking_sql) and count($select_ranking_sql) > 0)
		{
			$select_ranking_sql =  implode(",", $select_ranking_sql);
		}
		return $select_ranking_sql;
	}
*/
    public function SearchLinks(array|string $Where = "", int $Offset = -1, int $Limit = 10)
    {
		if($Where)
		{
			$this->Sql_Log_Search_Where[] = $Where;
		}
        $this->Link_List = array();
		$this->Registered_Link_Number = 0;
        $sql_result = $this->System->DataBase_Manager->Links_List->SelectSql
		(
			$this->Sql_Where_Variables,
			"count(link_id) as link_count",
			"",
			$this->Sql_Log_Search_Where
		);
		$this->Registered_Link_Number = intval($sql_result->fetchObject()->link_count);
		if($Offset <= -1)
		{
	        $Offset = intval($Limit) * (intval($this->System->Page_Number) - 1);
		}
        $sql_result = $this->System->DataBase_Manager->Link_List->SelectSql
        (
			$this->Sql_Where_Variables,
            "link_id, site_name, site_url, banner_url, description, user_name, mail_address, password, ip_address, update_timestamp, registerd_timestamp ", //. $select_ranking_sql,
			"",
            $this->Sql_Log_Search_Where,
            "",
			"",
            $this->Sql_Sort_Order,
            $Offset,
            $Limit
        );
		while($fetch_object = $sql_result->fetchObject())
		{
			$link_object =  new Link_Data_Object();
			$link_object->ConvertObjectFromDataBaseObject($fetch_object);
			$link_object = $this->GetCategoryForObjectCreation($link_object);
			$link_object = $this->GetTagForObjectCreation($link_object);
			$this->Link_List[] = $link_object;
		}
    }

	public function SearchLinkRegisteredInCategory(int $Category_Id, int $Offset = -1, int $Limit = 10)
	{
		$this->Sql_Where_Variables[] = $Category_Id;
		$this->Sql_Log_Search_Where[] = "link_category.category_id = ?";
        $this->Link_List = array();
		$this->Registered_Link_Number = 0;
        $sql_result = $this->System->DataBase_Manager->Link_Category->SelectSql
		(
			$this->Sql_Where_Variables,
			"count(link_category.link_id) As link_count",
			$this->System->DataBase_Manager->Link_List->GetTableName() . " AS link_list ON link_category.link_id = link_list.link_id",
			$this->Sql_Log_Search_Where
		);
		$this->Registered_Link_Number = intval($sql_result->fetchObject()->link_count);
		if($Offset <= -1)
		{
	        $Offset = intval($Limit) * (intval($this->System->Page_Number) - 1);
		}
		$sql_result = $this->System->DataBase_Manager->Link_Category->SelectSql
		(
			$this->Sql_Where_Variables,
			"link_list.link_id, link_list.site_name, link_list.site_url, link_list.banner_url, link_list.description, link_list.user_name, link_list.mail_address, link_list.password, link_list.ip_address, link_list.update_timestamp, link_list.registered_timestamp ", //. $select_ranking_sql,
			$this->System->DataBase_Manager->Link_List->GetTableName() . " AS link_list ON link_category.link_id = link_list.link_id",
			$this->Sql_Log_Search_Where,
			"",
			"",
			$this->Sql_Sort_Order,
			$Offset,
			$Limit
		);
		while($fetch_object = $sql_result->fetchObject())
		{
			$link_object = new Link_Data_Object();
			$link_object->ConvertObjectFromDataBaseObject($fetch_object);
			$link_object = $this->GetCategoryForObjectCreation($link_object);
			$link_object = $this->GetTagForObjectCreation($link_object);
			$this->Link_List[] = $link_object;
		}
	}


	public function SearchTemporaryLinks(array|string $Where = "", int $Offset = -1, int $Limit = 10)
    {
		if($Where)
		{
			$this->Sql_Log_Search_Where[] = $Where;
		}	
        $this->Link_List = array();
		$this->Registered_Link_Number = 0;
        $sql_result = $this->System->DataBase_Manager->Temporary_Link_List->SelectSql
			(
				array(),
				"count(temporary_id)",
				"",
				$Where
			);
		$this->Registered_Link_Number = intval(array_shift($sql_result->fetch_row()));
		if($Offset <= -1)
		{
	        $Offset = intval($Limit) * (intval($this->System->Page_Number) - 1);
		}
        $sql_result = $this->System->DataBase_Manager->Temporary_Links_Data->SelectSql
        (
			array(),
			"temporary_link.site_name, temporary_link.site_url, temporary_link.banner_url, temporary_link.description, temporary_link.user_name, temporary_link.mail_address, temporary_link.password, temporary_link.ip_address, temporary_link.update_timestamp, temporary_link.registerd_timestamp, temporary_link.tag, temporary_link.categories",
			"",
            $Where,
            "",
			"",
            "temprorary_id",
            $Offset,
            $Limit
        );
		while($fetch_object = $sql_result->fetchObject())
		{
			$link_object = new Link_Data_Object();
			$link_object->ConvertObjectFromDataBaseObject($fetch_object);
			$link_object->Tags($fetch_object->tag);
/*一時登録 の場合、ミスがあるかも*/
			$link_object->OrdinaryCategories($fetch_object->categories);
			$this->Link_List[] = $link_object;
		}
	}



	private function GetCategoryForObjectCreation(Link_Data_Object $New_Link_Data_Object)
	{
		$category_sql_result = $this->System->DataBase_Manager->Link_Category->SelectSql
		(
			array($New_Link_Data_Object->LinkId()),
			"category_list.category_id, category_list.type, category_list.name, category_list.alias, category_list.is_enable",
			$this->System->DataBase_Manager->Category_List->GetTableName() . " AS category_list ON category_list.link_id = link_category.link_id",
			"link_category.link_id = ? AND category_list.is_enable = true"
		);
		while($link_category = $category_sql_result->fetchObject())
		{
			if($link_category->category_id)
			{
				$this->Category_Search_Manager->ClearSearchData();
				$this->Category_Search_Manager->SpecifySearchCriteriaOnCategoryId($link_category->link_id);
				$this->Category_Search_Manager->SearchCategories();
				$category_data = $this->Category_Search_Manager->GetCategoryList(0);				
				if($category_data)
				{
					switch($category_data->TypeName())
					{
						case "ordinary" : 
							$New_Link_Data_Object->OrdinaryCategories($category_data);
						break;
						case "special" :
							$New_Link_Data_Object->SpecialCategories($category_data);
						break;
					}
				}
			}
		}
		return $New_Link_Data_Object;
	}

	private function GetTagForObjectCreation(Link_Data_Object $New_Link_Data_Object)
	{
		$link_tag_sql_result = $this->System->DataBase_Manager->Link_Tag->SelectSql
		(
			"tag_value",
			"",
			"link_tag.link_id = " . $New_Link_Data_Object->LinkId()
		);
		while($tag_data = $link_tag_sql_result->fetchObject())
		{
			if($tag_data->tag_value)
			{
				$New_Link_Data_Object->Tags($tag_data->tag_value);
			}
		}
		return $New_Link_Data_Object;
	}


	public function GetRegisteredLinkNumber()
	{
		return $this->Registered_Link_Number;
	}

    public function GetLinkList(null | int $Index = null)
    {
		if(is_null($Index) and count($this->Link_List) > 1)
		{
			return $this->Link_List;
		}
		elseif(is_numeric($Index) and $Index >= 0 and isset($this->Link_List[$Index]))
		{
			return $this->Link_List[$Index];
		}
		else
		{
			return false;
		}
    }

}