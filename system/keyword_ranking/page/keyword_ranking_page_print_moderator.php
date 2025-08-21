<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Page;

use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Page\Keyword_Ranking_Page_Printer;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Manager\Keyword_Ranking_Search_Manager;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Keyword_Ranking_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
	private Keyword_Ranking_Search_Manager $Keyword_Ranking_Search_Manager;
	private Keyword_Ranking_Page_Printer $Ranking_Page_Printer;

	private string $Ranking_Type = "";
	private int $Ranking_Start_Period_Timestamp = 0;
	private int $Ranking_End_Period_Timestamp = 0;

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Keyword Ranking Page Print Moderator Error");
		$this->Keyword_Ranking_Search_Manager = new Keyword_Ranking_Search_Manager($Yomi_Search);
		$this->Ranking_Page_Printer = new Keyword_Ranking_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
       if($this->System->Page_Mode =='keyrank')
       {
            $this->System->Page_Mode == "keyword_ranking";
       }

	   if($this->System->Page_Mode == "keyword_ranking")
	   {
	   		if(!$this->System->Config->Options["keyword_ranking"]->IsEnabled())
			{
				$this->Error_Manager->SetLog('検索キーワードランキングは実施しない設定になっています');
				$this->Error_Manager->PrintLastErrorAsPage();				
			}
			elseif(!$this->System->User->HttpQueries("type"))
			{
				$this->Error_Manager->SetLog("検索キーワードランキングの表示パラメータが指定されていない為、処理に失敗しました。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$ranking_data = $this->GetRankingData($this->System->User->HttpQueries("type"));
				if(!$this->Ranking_Type or ($this->Ranking_End_Period_Timestamp > -1 and $this->Ranking_End_Period_Timestamp > -1))
				{
					$this->Ranking_Page_Printer->PrintPage
						(
							$ranking_data,
							$this->Ranking_Start_Period_Timestamp,
							$this->Ranking_End_Period_Timestamp
						);
				}
				else
				{
					$this->Error_Manager->SetLog("検索キーワードランキングの表示パラメータが判別出来なかった為、処理に失敗しました。");
					$this->Error_Manager->PrintLastErrorAsPage();
				}
			}
	   }
    }

	private function GetRankingData(string $Ranking_Type)
	{
		$this->Ranking_Type = "";
		switch($Ranking_Type)
		{
			case "this_time" : 
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["keyword_ranking"]->ThisPeriodTitle();
    	       	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["keyword_ranking"]->Period()) * 86400;
				$this->Ranking_End_Period_Timestamp = time() - 1;
				return $this->Keyword_Ranking_Search_Manager->GetRankingData($this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp, intval($this->System->Config->Options["keyword_ranking"]->RankCountToDisplay()));
			break;
			case "last_time" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["keyword_ranking"]->LastPeriodTitle();
   	        	$this->Ranking_Start_Period_Timestamp = time() - intval($this->System->Config->Options["keyword_ranking"]->Period()) * 86400 * 2;
	           	$this->Ranking_End_Period_Timestamp = time() - intval($this->System->Config->Options["keyword_ranking"]->Period())* 86400 - 1;
				return $this->Keyword_Ranking_Search_Manager->GetRankingData($this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp, intval($this->System->Config->Options["keyword_ranking"]->RankCountToDisplay()));
			break;
			case "total" :
				$this->Ranking_Type = $Ranking_Type;
				$this->System->Page_Title[] = $this->System->Config->Options["keyword_ranking"]->TotalPeriodTitle();
				$this->Ranking_Start_Period_Timestamp = 0;
				$this->Ranking_End_Period_Timestamp = time();
				return $this->Keyword_Ranking_Search_Manager->GetRankingData($this->Ranking_Start_Period_Timestamp, $this->Ranking_End_Period_Timestamp, intval($this->System->Config->Options["keyword_ranking"]->RankCountToDisplay()));
			break;
			default :
				$this->Ranking_Start_Period_Timestamp = -1;
				$this->Ranking_End_Period_Timestamp = -1;
				return false;
			break;
		}
	}

}