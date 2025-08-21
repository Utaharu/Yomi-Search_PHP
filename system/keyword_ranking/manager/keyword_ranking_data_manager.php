<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Manager;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Data_Object\Keyword_Ranking_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Keyword_Ranking_Data_Manager
{
    private object $System;
    private Keyword_Ranking_Search_Manager $Keyword_Ranking_Search_Manager;
    private Error_Manager $Error_Manager;

    private array $Ranking_Data = array();

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Keyword_Ranking_Search_Manager = new Keyword_Ranking_Search_Manager($Yomi_Search);
        $this->Error_Manager = new Error_Manager("Keyword Ranking Data Manager Error");
    }

// (6)キーワードをデータベースに記録(&set_word)
	public function RecordSearchWord(string $Search_Word)
	{
		if(strlen($Search_Word) < 50)
		{
			$time = time();
			$Search_Word = str_replace('　', ' ', $Search_Word);
			$word_list = explode(' ', $Search_Word);
			$word_list = $this->System->DataBase_Manager->DataBase->EscapeStringValues($word_list);
			if(is_array($word_list) and count($word_list) > 0)
			{
				foreach($word_list as $word)
				{
					if($word and preg_match("/^(or|and|not)$/i", $word) == false)
					{
						$word = str_replace("\n", '', $word);
						/* Check Word */
                        $this->AddNewKeyword($word);
                        $keyword_data = $this->Keyword_Ranking_Search_Manager->SearchKeyword($word);
                        if($keyword_data instanceof PDOStatement)
                        {
                            $keyword_data = $keyword_data->fetchObject();
                            if($keyword_data->keyword_id > 0 and $keyword_data->is_enabled)
                            {
                                $sql_result = $this->System->DataBase_Manager->Keyword_Ranking_Log->SelectSql
                                (
                                    array($keyword_data->keyword_id , $this->System->User->IpAddress(), (time() - 24 * 3600)),
                                    "log_id, keyword_id, ip_address",
                                    "",
                                    "keyword_id = ? AND ip_address = ? AND timestamp > ?)"
                                );
                                if(!$sql_result)
                                {
                                    $this->CountUp($sql_result);
                                    $this->AddCountLog($sql_result);
                                }
                            }
                        }
					}
				}
			}
		}
	}

    private function CountUp(object $DataBase_Ranking_Object)
    {
        if($DataBase_Ranking_Object->keyword_id > 0)
        {
            $this->System->DataBase_Manager->Keyword_Ranking->UpdateSql
            (
                $DataBase_Ranking_Object->keyword_id,
                "",
                intval($DataBase_Ranking_Object->total_count) + 1 
            );
        }
    }

    private function AddNewKeyword(string $Keyword)
    {
        $keyword_data = $this->Keyword_Ranking_Search_Manager->SearchKeyword($Keyword);
        if(!$keyword_data)
        {
            return $this->System->DataBase_Manager->Keyword_Ranking->InsertSql($Keyword, false);
        }
        return false;
    }

    private function AddCountLog(object $DataBase_Ranking_Object)
    {
        return $this->System->DataBase_Manager->Keyword_Ranking_Log->InsertSql
        (
            $DataBase_Ranking_Object->keyword_id,
            $this->System->User->IpAddress(),
            time()
        );    
    }

    public function DeleteRankingData(int $Keyword_Id)
    {
        $this->System->DataBase_Manager->Keyword_Ranking->DeleteSql($Keyword_Id);
        $this->System->DataBase_Manager->Keyword_Ranking_Log->DeleteSql($Keyword_Id);
    }


}