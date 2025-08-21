<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Setup\Operator;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Data_Manager;
/* Library */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Config\Default_Category_Config;

class Setup_DataBase_Table_Operator
{
	/* Property */
	private object $System;
	private Category_Data_Manager $Category_Data_Manager;
	private Error_Manager $Error_Manager;
	private Default_Category_Config $Default_Category_Config;

	var $Create_Table;
	var $Table_List = array();
	
	function __construct(object &$Yomi_Search)
	{
		$this->System = $Yomi_Search;
		$this->Default_Category_Config = new Default_Category_Config();
		$this->Category_Data_Manager = new Category_Data_Manager($Yomi_Search);
		$this->Error_Manager = new Error_Manager("Setup Table Error");
		//Get - Table Name List
		$this->Table_List = $this->System->DataBase_Manager->GetTableList();
	}

	public function SetupDataBaseTable()
	{
		$result_message = array();
		/* System Option */
		$result_message[] = $this->SystemOptionType();
		$result_message[] = $this->SystemOption();

		$result_message[] = $this->CategoryType();
		$result_message[] = $this->CategoryList();
		$result_message[] = $this->CategoryHierachy();

		$result_message[] = $this->LinkList();
		$result_message[] = $this->LinkCategory();
		$result_message[] = $this->LinkTag();
		$result_message[] = $this->TemporaryLinkList();

		$result_message[] = $this->AccessRanking();
		$result_message[] = $this->AccessRankingInLog();
		$result_message[] = $this->AccessRankingOutLog();

		$result_message[] = $this->KeywordRanking();
		$result_message[] = $this->KeywordRankingLog();

		$result_message[] = $this->LinkErrorReportType();
		$result_message[] = $this->LinkErrorReport();
		
		return $result_message;
	}

	/* # Check - $Table_List 
	 $Table = Not included prefix
	*/
	private function CheckTableList(string $Table_Name)
	{
		$Table_Name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $Table_Name);
		if(array_search($Table_Name, $this->Table_List) === false)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	private function CategoryList()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Category_List->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Category_List->CreateTable();
			foreach($this->Default_Category_Config->Categories as $category_type_name => $category_list)
			{
				if(is_array($category_list))
				{
					foreach($category_list as $category_key => $category_values)
					{
						$category_object = new Category_Data_Object();
						$category_object->TypeName($category_type_name);
						$category_object->Alias($category_values[0]);
						$category_object->Title($category_values[1]);
						$category_object->ForSorting($category_values[2]);
						$category_object->Description($category_values[3]);
						$category_object->IsEnabled($category_values[4]);
						$this->Category_Data_Manager->RegistrationCategory($category_object);					
					}
				}
			}
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function CategoryType()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Category_Type->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Category_Type->CreateTable();
			if(isset($this->Default_Category_Config->Categories) and is_array($this->Default_Category_Config->Categories))
			{
				foreach($this->Default_Category_Config->Categories as $category_type_name => $category_list)
				{
					$this->Category_Data_Manager->RegistrationCategoryType($category_type_name);
				}
			}
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function CategoryHierachy()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Category_Hierachy->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Category_Hierachy->CreateTable();

			foreach($this->Default_Category_Config->Categories as $category_list)
			{
				if(is_array($category_list))
				{
					$category_key_list = array_keys($category_list);
					foreach($category_list as $category_key => $category_values)
					{
						if(preg_match("/^[0-9_]+$/", $category_key))
						{
							$result_sql = $this->System->DataBase_Manager->Category_List->SelectSql(array($category_values[1]), "category_id", "", "title = ?");
							$this_category = 0;
							if($result_sql != false)
							{
								$this_category = $result_sql->fetchObject();
								if($this_category)
								{
									$hierachy_my_category_id = $this_category->category_id;
								}
							}
							//Get -Hierachy(Parent - Category ID)
							if (stristr($category_key, '_'))
							{	
								if(isset($category_list[substr($category_key, 0, strrpos($category_key, '_'))]))
								{
									$category_data = $category_list[substr($category_key, 0, strrpos($category_key, '_'))];
									$result_sql = $this->System->DataBase_Manager->Category_List->SelectSql(array($category_data[1]), "category_id", "", "title = ?");
									if($result_sql != false)
									{
										$parent_category = $result_sql->fetchObject();
										if($parent_category)
										{
											$hierachy_parent_category_id = $parent_category->category_id;
										}
									}
								}
							}
							else
							{
								$hierachy_parent_category_id = $hierachy_my_category_id;
							}
						}					
						$this->System->DataBase_Manager->Category_Hierachy->InsertSql($hierachy_parent_category_id, $hierachy_my_category_id);
					}
				}
			}
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function SystemOption()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->System_Option->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->System_Option->CreateTable();
			if(is_array($this->System->Config->Options))
			{
				foreach($this->System->Config->Options as $option_object)
				{
					$option_object->SetUpOptionValue();
				}
			}

			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function SystemOptionType()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->System_Option_Type->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->System_Option_Type->CreateTable();
			if(is_array($this->System->Config->Options))
			{
				foreach($this->System->Config->Options as $option_object)
				{
					$option_object->SetUpOptionType();
				}
			}

			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}
	
	private function KeywordRanking()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Keyword_Ranking->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Keyword_Ranking->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}
	
	private function KeywordRankingLog()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Keyword_Ranking_Log->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Keyword_Ranking_Log->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function AccessRanking()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Access_Ranking->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Access_Ranking->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function AccessRankingInLog()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Access_Ranking_In_Log->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Access_Ranking_In_Log->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function AccessRankingOutLog()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Access_Ranking_Out_Log->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Access_Ranking_Out_Log->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function LinkList()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Link_List->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Link_List->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function LinkCategory()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Link_List->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Link_Category->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function LinkTag()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Link_Tag->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Link_Tag->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function TemporaryLinkList()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Temporary_Link_List->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Temporary_Link_List->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function LinkErrorReport()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Link_Error_Report->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Link_Error_Report->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	private function LinkErrorReportType()
	{
		$table_name = preg_replace("/{prefix}/", $this->System->DataBase_Manager->DataBase->TablePrefix(), $this->System->DataBase_Manager->Link_Error_Report_Type->GetTableName());
		if(!$this->CheckTableList($table_name))
		{
			$this->System->DataBase_Manager->Link_Error_Report_Type->CreateTable();
			return $this->ResultMessage($table_name, true);
		}
		else
		{
			return $this->ResultMessage($table_name, false);
		}
	}

	
	protected function ResultMessage($Table, $Flag)
	{
		if(!isset($this->Table_List[$Table]) and $Flag)
		{
			return "データベース " . $this->System->DataBase_Manager->DataBase->DataBaseName() . " にテーブル {$Table} を作成しました。";
		}
		else
		{
			return "テーブル {$Table} は作成済みです！";
		}
	}
	
	private function GetServerSchema()
	{
		$scheme = "";
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
		{
			$scheme = 'https';
		}
		else
		{
			$scheme = 'http';
		}
		return $scheme;
	}
	
}
