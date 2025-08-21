<?php
namespace Wonder_Link\Yomi_Search_PHP\System\DataBase;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Connector;

//use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Counter;
//use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Counter_Log;

/* Link Data */
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Link_List;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Link_Category;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Link_Tag;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Temporary_Link_List;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Link_Error_Report;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Link_Error_Report_Type;

/* Category List */
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Category_List;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Category_Type;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Category_Hierachy;

/* Access Ranking */
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Access_Ranking_In_Log;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Access_Ranking_Out_Log;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Access_Ranking;
/* Keyword Ranking */
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Keyword_Ranking_Log;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_Keyword_Ranking;
/* Site Config */
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_System_Config;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_System_Filter_Option;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_System_Filter_Type;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_System_Option;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Table\Item\Table_System_Option_Type;
use Wonder_Link\Yomi_Search_PHP\System\Yomi_Search;
use PDO;
use PDOStatement;

class DataBase_Manager
{
	private object $System;
	public DataBase_Connector $DataBase;
	public Table_Counter $Counter;
	public Table_Counter_Log $Counter_Log;

	/* Link Data */
	public Table_Link_List $Link_List;
	public Table_Link_Category $Link_Category;
	public Table_Link_Tag $Link_Tag;
	public Table_Temporary_Link_List $Temporary_Link_List;
	public Table_Link_Error_Report $Link_Error_Report;
	public Table_Link_Error_Report_Type $Link_Error_Report_Type;

	/* Category List */
	public Table_Category_List $Category_List;
	public Table_Category_Type $Category_Type;
	public Table_Category_Hierachy $Category_Hierachy;
	/* Access Ranking */
	public Table_Access_Ranking_Out_Log $Access_Ranking_Out_Log;
	public Table_Access_Ranking_In_Log $Access_Ranking_In_Log;
	public Table_Access_Ranking $Access_Ranking;
	/* Keyword Ranking */
	public Table_Keyword_Ranking_Log $Keyword_Ranking_Log;
	public Table_Keyword_Ranking $Keyword_Ranking;
	/*Config */
    public Table_System_Option $System_Option;
	public Table_System_Option_Type $System_Option_Type;
	public Table_System_Filter_Option $System_Filter_Option;
	public Table_System_Filter_Type $System_Filter_Type;

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Link_Error_Report = new Table_Link_Error_Report($this);
		$this->InitializeSystemOptions();
		$this->InitializeLinksData();
		$this->InitializeCategory();
		$this->InitializeKeywordRanking();
		$this->InitializeAccessRanking();
		$this->InitializeCounter();
	}

	public function SetDataBaseConnector(DataBase_Connector &$DataBase_Connector)
	{
		$this->DataBase =  &$DataBase_Connector;
	}

	public function ConnectDataBase()
	{
		return $this->DataBase->Connect();
	}

	public function CreateDataBase()
	{
		if($this->DataBase->DataBaseName() and preg_match("/[a-z_]+/i", $this->DataBase->DataBaseName()))
		{
			$sql = "CREATE DATABASE " . $this->DataBase->DataBaseName();
			return $this->DataBase->ExecuteQuery($sql);
		}
		return false;
	}

	public function SelectDataBase()
	{
		if($this->DataBase->DataBaseName() and preg_match("/[a-z_]+/i", $this->DataBase->DataBaseName()))
		{
			$sql = "USE " . $this->DataBase->DataBaseName();
		
			return $this->DataBase->ExecuteQuery($sql);
		}
		return false;
	}

	private function InitializeSystemOptions()
	{
		$this->System_Option = new Table_System_Option($this);
		$this->System_Option_Type = new Table_System_Option_Type($this);
		$this->System_Filter_Option = new Table_System_Filter_Option($this);
		$this->System_Filter_Type = new Table_System_Filter_Type($this);
	}

	private function InitializeLinksData()
	{
		$this->Link_List = new Table_Link_List($this);
		$this->Link_Category = new Table_Link_Category($this);
		$this->Link_Tag = new Table_Link_Tag($this);
		$this->Temporary_Link_List = new Table_Temporary_Link_List($this);
		$this->Link_Error_Report = new Table_Link_Error_Report($this);
		$this->Link_Error_Report_Type = new Table_Link_Error_Report_Type($this);
	}

	private function InitializeCounter()
	{
//		$this->Counter = new Table_Counter($this);
//		$this->Counter_Log = new Table_Counter_Log($this);
	}

	private function InitializeCategory()
	{
		$this->Category_List = new Table_Category_List($this);
		$this->Category_Type = new Table_Category_Type($this);
		$this->Category_Hierachy = new Table_Category_Hierachy($this);
	}

	private function InitializeAccessRanking()
	{
		$this->Access_Ranking_In_Log = new Table_Access_Ranking_In_Log($this);
		$this->Access_Ranking_Out_Log = new Table_Access_Ranking_Out_Log($this);
		$this->Access_Ranking = new Table_Access_Ranking($this);
	}
	
	private function InitializeKeywordRanking()
	{
		$this->Keyword_Ranking_Log = new Table_Keyword_Ranking_Log($this);
		$this->Keyword_Ranking = new Table_Keyword_Ranking($this);	
	}

/*
 	 ## データベース内のテーブルリスト取得 ##
	  * key(table_name) = value(table_name)
	   $table_list[$table_name] = $table_name;
	*/
	public function GetTableList()
	{
		$sql_result = $this->DataBase->ExecuteQuery('SHOW TABLES FROM ' . $this->System->DataBase_Manager->DataBase->DataBaseName());
		$i = 0;
        $table_list = array();
		if($sql_result instanceof PDOStatement)
		{
			while ($table_name = $sql_result->fetch(PDO::FETCH_NUM))
			{
				$table_list[$table_name[0]] = $table_name[0];
			}
		}
		return $table_list;
	}

}