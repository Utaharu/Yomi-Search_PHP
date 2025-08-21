<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Category\Manager;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Category_Search_Manager
{
    public const KEYWORD_SEARCH_OR = 0;
    public const KEYWORD_SEARCH_AND = 1;
    public const KEYWORD_SEARCH_FORMULA = 2;

    private object $System;
    private Error_Manager $Error_Manager;

    private array $Sql_Category_Search_Where = array();
	private array $Sql_Where_Variables = array();
    private array $Category_List = array();

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Category Search Manager Error");
    }

    public function SpecifySearchCriteriaOnCategoryId(int $Category_Id)
    {
		$this->Sql_Where_Variables[] = $Category_Id;
        $this->Sql_Category_Search_Where[] = "category_list.category_id = ?";
    }

    public function SpecifySearchCriteriaOnAlias(string $Category_Alias)
    {
		$this->Sql_Where_Variables[] = $Category_Alias;
        $this->Sql_Category_Search_Where[] = "category_list.alias LIKE ?";
    }

	public function SpecifySearchCriteriaOnTypeName(string $Category_Type)
	{
		$this->Sql_Where_Variables[] = $Category_Type;
		$this->Sql_Category_Search_Where[] = "type_name LIKE ?";
	}
	
	public function SpecifySearchCriteriaOnTypeId(string $Category_Type_Id)
	{
		$this->Sql_Where_Variables[] = $Category_Type_Id;
		$this->Sql_Category_Search_Where[] = "type_id LIKE ?";
	}

	public function SpecifySearchCriteriaOnTitle(string $Category_Title)
	{
		$this->Sql_Where_Variables[] = $Category_Title;
		$this->Sql_Category_Search_Where[] = "category_list.title LIKE ?";
	}

	public function SpecifySearchCriteriaOnDescription(string $Category_Description)
	{
		$this->Sql_Where_Variables[] = "%" . $Category_Description . "%";
		$this->Sql_Category_Search_Where[] = "category_list.description LIKE ?";
	}
	
	public function SpecifySearchCriteriaOnIsEnabled(bool $Category_Is_Enabled)
	{
		$this->Sql_Where_Variables[] = $Category_Is_Enabled;
		$this->Sql_Category_Search_Where[] = "category_list.is_enabled = ?";
	}

	public function SpecifySearchCriteriaOnForSotring(string $Category_For_Sorting)
	{
		$this->Sql_Where_Variables[] = "%" . $Category_For_Sorting . "%";
		$this->Sql_Category_Search_Where[] = "category_list.for_sorting LIKE ?";
	}

    public function SpecifySearchCriteriaOnKeyword(string $Keyword, int $Join_Type = 1)
	{
		// 検索構文の解析
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
			$this->Sql_Category_Search_Where['AND'][] = "(category_list.title LIKE '%{$Word}%')";
		}
		return;
	}

	private function UseOrTypeInKeywordSearch(string $Word)
	{
		if($Word)
		{
			$Word = $this->System->DataBase_Manager->DataBase->EscapeValue($Word);
			$this->Sql_Category_Search_Where['OR'][] = "(category_list.title LIKE '%{$Word}%')";
		}
		return;
	}

	private function UseNotTypeInKeywordSearch(string $Word)
	{
		if($Word)
		{
			$Word = $this->System->DataBase_Manager->DataBase->EscapeValue($Word);
			$this->Sql_Category_Search_Where["AND"][] = "(category_list.title collate NOT LIKE '%{$Word}%')";
		}
		return;
	}

    public function ClearSearchData()
    {
        $this->Sql_Category_Search_Where = array();
		$this->Sql_Where_Variables = array();
		$this->Category_List = array();		
    }

    public function SearchCategories()
    {
        $this->Category_List = array();
        $sql_result = $this->System->DataBase_Manager->Category_List->SelectSql
		(
			$this->Sql_Where_Variables,
			"category_id, category_list.category_type_id, category_type.type_name, category_list.alias, category_list.title, category_list.description, category_list.is_enabled, category_list.for_sorting",
			$this->System->DataBase_Manager->Category_Type->GetTableName() . " AS category_type ON category_type.type_id = category_list.category_type_id",
			$this->Sql_Category_Search_Where
		);
        while($category_data = $sql_result->fetchObject())
        {
			$category_object = new Category_Data_Object();
			$category_object->ConvertFromDataBaseObject($category_data);
            $this->Category_List[] = $category_object;
        }
    }



    public function GetCategoryList(null | int $Index = null)
    {
		if(is_null($Index) and count($this->Category_List) > 1)
		{
			return $this->Category_List;
		}
		elseif(is_numeric($Index) and $Index >= 0 and isset($this->Category_List[$Index]))
		{
			return $this->Category_List[$Index];
		}
		else
		{
			return false;
		}
    }

	public function GetCategoryHierachy(int $Parent_Category_Id = 0, int $Child_Category_Id = 0)
	{
		if($Parent_Category_Id > 0)
		{
			$this->Sql_Where_Variables[] = $Parent_Category_Id;
			$this->Sql_Category_Search_Where[] = "parent_category_id = ?";
		}
		else
		{
			$this->Sql_Category_Search_Where[] = "parent_category_id = child_category_id";
		}
		/* parent category */		
		$sql_result = $this->System->DataBase_Manager->Category_Hierachy->SelectSql
			(
				$this->Sql_Where_Variables,
				"child_category_id, category_list.category_id, category_type.type_id, category_type.type_name, category_list.alias, category_list.title, category_list.description, category_list.is_enabled, category_list.for_sorting",
				$this->System->DataBase_Manager->Category_List->GetTableName() . " AS category_list ON category_id = parent_category_id"
				. " JOIN " .  $this->System->DataBase_Manager->Category_Type->GetTableName() . " AS category_type ON category_type.type_id = category_list.category_type_id",
				$this->Sql_Category_Search_Where,
				"",
				"",
				"parent_category_id ASC"
			);
		if($sql_result instanceof PDOStatement)
		{
			while($category_data = $sql_result->fetchObject())
			{
				$category_object = new Category_Data_Object();
				$category_object->ConvertFromDataBaseObject($category_data);
				$this->Category_List[] =
				array(
					"data" => $category_object,
					"child_list" => $this->CategoryChildHierachy($category_data->child_category_id)
				);
			}
		}
	}

	private function CategoryChildHierachy(int $Child_Category_Id)
	{
		$sql_result = $this->System->DataBase_Manager->Category_Hierachy->SelectSql
			(
				"child_category_id,  category_list.category_id, category_type.type_name AS type, category_list.alias, category_list.title, category_list.description, category_list.is_enabled, category_list.for_sorting",
				$this->System->DataBase_Manager->Category_List->GetTableName() . " AS category_list ON child_category_id = category_id"
				. " JOIN " .  $this->System->DataBase_Manager->Category_Type->GetTableName() . " AS category_type ON category_type.type_id = category_list.category_type_id",
				"parent_category_id != child_category_id and parent_category_id = " . $Child_Category_Id,
				"",
				"",
				"child_category_id ASC"
			);
		if($sql_result instanceof PDOStatement)
		{
			$category_list = array();
			while($category_data = $sql_result->fetchObject())
			{
				$category_object = new Category_Data_Object();
				$category_object->ConvertFromDataBaseObject($category_data);
				$category_list[] =
					array(
					    "data" => $category_object,
						"child_list" => $this->CategoryChildHierachy($category_data->child_category_id)
					);
			}
			return $category_list;
		}
	}

}