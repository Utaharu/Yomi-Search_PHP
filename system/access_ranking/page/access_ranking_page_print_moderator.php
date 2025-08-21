<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Page;

use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager\Access_Ranking_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Page\Access_Ranking_Page_Printer;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Access_Ranking_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
	private Category_Search_Manager $Category_Search_Manager;
	private Access_Ranking_Search_Manager $Ranking_Search_Manager;
	private Access_Ranking_Page_Printer $Ranking_Page_Printer;

	private string $Ranking_Type = "";
	private int $Ranking_Start_Period_Timestamp = 0;
	private int $Ranking_End_Period_Timestamp = 0;

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Access Ranking Print Moderator Error");
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
		$this->Ranking_Search_Manager = new Access_Ranking_Search_Manager($Yomi_Search);
		$this->Ranking_Page_Printer = new Access_Ranking_Page_Printer($Yomi_Search);
	}

	public function Printer()
	{
		$this->Ranking_Type = "";
		if($this->System->Page_Mode == "access_ranking")
		{
			if(preg_match("/^in_data/", $this->System->User->HttpQueries("type")) and !$this->System->Config->Options["in_access_ranking"]->IsEnabled())
			{
				$this->Error_Manager->SetLog('IN - Access Ranking は実施しない設定になっています');
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif(preg_match("/^out_data/", $this->System->User->HttpQueries("type")) and !$this->System->Config->Options["out_access_ranking"]->IsEnabled())
			{
				$this->Error_Manager->SetLog('OUT - Access Ranking は実施しない設定になっています');
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif(!$this->System->User->HttpQueries("type"))
			{
				$this->Error_Manager->SetLog("Access Ranking Page の表示パラメータが指定されていない為、処理に失敗しました。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$category_data = false;
				if($this->System->User->HttpQueries("path"))
				{
					$category_data = $this->GetCategoryData($this->System->User->HttpQueries("path"));
					if($category_data === false)
					{
						$this->Error_Manager->SetLog("Access Ranking において、要求された、カテゴリの判別に失敗しました。");
						$this->Error_Manager->PrintLastErrorAsPage();
					}
				}

				$ranking_data = $this->GetRankingData($this->System->User->HttpQueries("type"));
				if(!$this->Ranking_Type or ($this->Ranking_End_Period_Timestamp > -1 and $this->Ranking_End_Period_Timestamp > -1))
				{
					$this->System->Page_Title = $category_data->Title();
					if($category_data)
					{
						$this->Ranking_Page_Printer->PrintPage
						(
							$ranking_data,
							$category_data->Alias(),
							$this->Ranking_Start_Period_Timestamp,
							$this->Ranking_End_Period_Timestamp
						);
					}
					else
					{
						$this->Error_Manager->SetLog("アクセスランキングのランキングデータの取得が出来なかった為、処理に失敗しました。");
						$this->Error_Manager->PrintLastErrorAsPage();
					}
				}
				else
				{
					$this->Error_Manager->SetLog("アクセスランキングの表示パラメータが判別出来なかった為、処理に失敗しました。");
					$this->Error_Manager->PrintLastErrorAsPage();
				}
			}
		}

	}

	private function GetCategoryData(string $Category_Key)
	{
		$check_path = $this->System->DataBase_Manager->DataBase->EscapeStringValues($Category_Key);
		$this->Category_Search_Manager->ClearSearchData();
		$this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($check_path);
		if(preg_match("/^[a-z0-9_-@\/&]+$/", $Category_Key))
		{
			$this->Category_Search_Manager->SpecifySearchCriteriaOnIsEnabled(true);
            $this->Category_Search_Manager->SearchCategories();
			return $this->Category_Search_Manager->GetCategoryList(0);
		}
		return false;
	}

	private function GetRankingData(string $Ranking_Type, string $Category_Key = "")
	{
		$this->Ranking_Type = "";

		switch($Ranking_Type)
		{
			case "in_data_this_time" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["in_access_ranking"]->ThisPeriodTitle();
	           	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["in_access_ranking"]->AggregatrionPeriod()) * 86400;
				$this->Ranking_End_Period_Timestamp = time() - 1;
				$this->Ranking_Search_Manager->SearchAccessInRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			case "in_data_last_time" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["in_access_ranking"]->LastPeriodTitle();
	           	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["in_access_ranking"]->AggregatrionPeriod()) * 86400 * 2;
	           	$this->Ranking_End_Period_Timestamp = time() - intval($this->System->Config->Options["in_access_ranking"]->AggregatrionPeriod()) * 86400 - 1;
				$this->Ranking_Search_Manager->SearchAccessInRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			case "in_data_total" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["in_access_ranking"]->TotalPeriodTitle();
				$this->Ranking_Start_Period_Timestamp = 0;
				$this->Ranking_End_Period_Timestamp = 0;
				$this->Ranking_Search_Manager->SearchAccessInRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			case "out_data_this_time" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["out_access_ranking"]->ThisPeriodTitle();
	           	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["out_access_ranking"]->AggregatrionPeriod()) * 86400;
				$this->Ranking_End_Period_Timestamp = time() - 1;
				$this->Ranking_Search_Manager->SearchAccessOutRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			case "out_data_last_time" :
				$this->Ranking_Type = $Ranking_Type;
	           	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["out_access_ranking"]->AggregatrionPeriod()) * 86400 * 2;
	           	$this->Ranking_End_Period_Timestamp = time() - intval($this->System->Config->Options["out_access_ranking"]->AggregatrionPeriod()) * 86400 - 1;
				$this->System->Page_Title[] = $this->System->Config->Options["out_access_ranking"]->LastPeriodTitle();
				$this->Ranking_Search_Manager->SearchAccessOutRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			case "out_data_total" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["out_acccess_ranking"]->TotalPeriodTitle();
				$this->Ranking_Start_Period_Timestamp = 0;
				$this->Ranking_End_Period_Timestamp = 0;
				$this->Ranking_Search_Manager->SearchAccessOutRankingData($Category_Key, $this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp);
				return $this->Ranking_Search_Manager->GetRankingData();
			break;
			default :
				$this->Ranking_Start_Period_Timestamp = -1;
				$this->Ranking_End_Period_Timestamp = -1;
				return false;
			break;
		}
	}

}