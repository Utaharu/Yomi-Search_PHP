<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Manager;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Data_Object\Keyword_Ranking_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Keyword_Ranking_Search_Manager
{
    private object $System;
    private Error_Manager $Error_Manager;

    private array $Ranking_Data = array();

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Keyword Ranking Search Manager Error");
    }

    public function SearchKeyword(string $Keyword)
    {
        if($Keyword)
        {
            $sql_result = $this->System->DataBase_Manager->Keyword_Ranking->SelectSql
            (
                array($Keyword),
                "keyword_id, is_enabled, keyword, total_count",
                "",
                "keyword = ?"
            );
            return $sql_result;
        }
        return false;
    }

    public function SearchRankingData(int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0, int $Limit = 0)
    {
        $this->Ranking_Data = array();
        if($Limit <= 0)
        {
            $Limit = 0;
        }
        $min_pt = intval($this->System->Config->Options["keyword_ranking"]->MinimumScore());
        $sql_result = $this->System->DataBase_Manager->Keyword_Ranking->SelectSql
            (
                array($min_pt, $Start_Period_Timestamp, $End_Period_Timestamp),
                "COUNT(keyword_log.log_id) AS ranking_pt, keyword_ranking.is_enabled, keyword_ranking.word, keyword_ranking.display_word, keyword_ranking.total_count",
                $this->System->DataBase_Manager->Keyword_Ranking_Log->GetTableName() . "  AS keyword_log ON keyword_ranking.keyword_id = keyword_log.keyword_id",
                "keyword_ranking.is_enabled = true AND ranking_pt > ? AND (keyword_log.timestamp BETWEEN ? AND ?",
                "keyword_log.keyword_id",
                "",
                "ranking_pt DESC",
                "",
                $Limit
            );
        $this->CreateRankingDataList($sql_result);
        if(is_array($this->Ranking_Data) and count($this->Ranking_Data) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function CreateRankingDataList(PDOStatement $Ranking_Data_Sql_Result)
    {
        $ranking_number = 0;
        $ranking_point = 0;
        while($ranking_data = $Ranking_Data_Sql_Result->fetchObject())
        {
            if($ranking_data->keyword_id)
            {
                if (intval($ranking_data->ranking_pt) != $ranking_point) 
                {
                    $ranking_number++;
                    $ranking_point = intval($ranking_data->ranking_pt);
                }
                $ranking_data_object = new Keyword_Ranking_Data_Object();
                $ranking_data_object->RankingNumber($ranking_number);
                $ranking_data_object->RankingPoint($ranking_data->ranking_pt);
                if($ranking_data->display_keyword)
                {
                    $ranking_data_object->RankingKeyword($ranking_data->display_keyword);
                }
                else
                {
                    $ranking_data_object->RankingKeyword($ranking_data->keyword);
                }
                $this->Ranking_Data[] = $ranking_data_object;
            }
        }
    }

    public function GetRankingData()
    {
        return $this->Ranking_Data;
    }

}