<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Tables\Table_Access_Ranking_In_Log;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\Tables\Table_Access_Ranking_Out_Log;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Access_Ranking_Search_Manager
{
    private object $System;
    private Error_Manager $Error_Manager;
    private Link_Search_Manager $Link_Search_Manager;
    private Category_Search_Manager $Category_Search_Manager;

    private array $Ranking_Data;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Access Ranking Search Manager Error");
        $this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
        $this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
    }


    public function SearchAccessInRankingData(string $Category_Alias = "", int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0)
    {
        $this->SearchRankingData($this->System->DataBase_Manager->Table_Access_Ranking_In_Log, $Category_Alias, $Start_Period_Timestamp, $End_Period_Timestamp);
    }

    public function SearchAccessOutRankingData(string $Category_Alias = "", int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0)
    {
        $this->SearchRankingData($this->System->DataBase_Manager->Table_Access_Ranking_Out_Log, $Category_Alias, $Start_Period_Timestamp, $End_Period_Timestamp);
    }

    private function SearchRankingData(&$Target_DataBase_Table, string $Category_Alias = "", int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0)
    {
        $this->Ranking_Data = array();
        if(!$Category_Alias)
        {
            $sql_result = $this->GetRankingAllLink($Target_DataBase_Table, $Start_Period_Timestamp, $End_Period_Timestamp);
        }
        else
        {
            $sql_result = $this->GetRankingEachCategory($Target_DataBase_Table, $this->System->DataBase_Manager->DataBase->EscapeStringValue($Category_Alias), $Start_Period_Timestamp, $End_Period_Timestamp);
        }
        if($sql_result)
        {
            $this->CreateRankingData($sql_result);
        }        
        if(is_array($this->Ranking_Data) and count($this->Ranking_Data) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function GetRankingEachCategory(&$Target_DataBase_Table, string $Category_Alias, int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0)
    {
        // Category  Access Ranking
        $sql_result = false;
        $category_data = false;
        if($Category_Alias)
        {
            $this->Category_Search_Manager->ClearSearchData();
            $this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($Category_Alias);
            $this->Category_Search_Manager->SearchCategories();
            $category_data = $this->Category_Search_Manager->GetCategoryList(0);
        }
        if(!$category_data)
        {
            $this->Error_Manager->SetLog("Category Each Ranking - 指定したカテゴリ情報が見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif($Start_Period_Timestamp === 0)
        {
            $join_sql = $this->GetCategoryTotalRankingJoinSql($Target_DataBase_Table, $Category_Alias);
        }
        else
        {
            $join_sql = $this->GetCategoryPeriodRankingJoinSql($Target_DataBase_Table, $Category_Alias, $Start_Period_Timestamp, $End_Period_Timestamp);
        }
        if(!$join_sql)
        {
            $this->Error_Manager->SetLog("Category Each Ranking - JoinSqlの取得が出来なかった為、処理は行われませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $sql_result = $this->System->DataBase_Manager->Link_Category->SelectSql
                (
                    array($category_data->CategoryId()),
                    "pt, link_category.link_id",
                    "({$join_sql}) AS ranking_data ON link_category.link_id = ranking_data.link_id",
                    "link_category.category_id = ?",
                    "",
                    "",
                    "ranking_data.pt DESC"
                );
        }
        return $sql_result;
    }

    private function GetRankingAllLink(&$Target_DataBase_Table, int $Start_Period_Timestamp = 0, int $End_Period_Timestamp = 0)
    {
        $sql_result = false;
        if($Start_Period_Timestamp === 0)
        {
            $join_sql = $this->GetAllLinkTotalRankingJoinSql($Target_DataBase_Table);
        }
        else
        {
            $join_sql = $this->GetAllLinkPeriodRankingJoinSql($Target_DataBase_Table, $Start_Period_Timestamp, $End_Period_Timestamp);
        }
        if(!$join_sql)
        {
            $this->Error_Manager->SetLog("All Link Ranking - JoinSqlの取得が出来なかった為、処理は行われませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {   
            $sql_result = $this->System->DataBase_Manager->Link_List->SelectSql
                (
                    array(),
                    "link_list.link_id, pt",
                    "({$join_sql}) AS ranking_data ON link_list.link_id = ranking_data.link_id",
                    "",
                    "",
                    "",
                    "ranking_data.pt DESC"
                );
        }
        return $sql_result;
    }

   private function CreateRankingData(PDOStatement $Ranking_Data_Sql_Result)
   {
        $ranking_number = 0;
        $ranking_point = 0;
        while($ranking_log = $Ranking_Data_Sql_Result->fetchObject())
        {
            if($ranking_log->link_id)
            {
                if (intval($ranking_log->pt) != $ranking_point) 
                {
                    $ranking_number++;
                    $ranking_point = intval($ranking_log->pt);
                }
                $this->Link_Search_Manager->ClearSearchData();
                $this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($ranking_log->link_id);
                $this->Link_Search_Manager->SearchLinks();
                $link_data = $this->Link_Search_Manager->GetLinkList(0);
                if($link_data)
                {
                    $access_ranking_object = new Access_Ranking_Data_Object();
                    $access_ranking_object->RankingNumber($ranking_number);
                    $access_ranking_object->RankingPoint($ranking_log->pt);
                    $access_ranking_object->LinkData($link_data);
                    $this->Ranking_Data[] = $access_ranking_object;
                }
            }
        }
    }

   public function GetRankingData()
    {
        return $this->Ranking_Data;
    }

    private function GetJoinSqlReuiredParameter(object &$Target_DataBase_Table)
    {
        $min_pt = 1;
        $limit = 10;    
        if($Target_DataBase_Table instanceof Table_Access_Ranking_In_Log)
        {
            $min_pt = intval($this->System->Config->Options["in_access_ranking"]->MinimumScor());
            $limit = intval($this->System->Config->Options["in_access_ranking"]->RrankCountToDisplay()); 
        }
        elseif($Target_DataBase_Table instanceof Table_Access_Ranking_Out_Log)
        {
            $min_pt = intval($this->System->Config->Options["out_access_ranking"]->MinimumScore());
            $limit = intval($this->System->Config->Options["out_access_ranking"]->RankCountToDisplay());
        }
        return array($min_pt, $limit);
    }

    private function GetAllLinkPeriodRankingJoinSql(object &$Target_DataBase_Table, int $Start_Timestamp, int $End_Timestamp)
    {
        list($min_pt, $limit) = $this->GetJoinSqlReuiredParameter($Target_DataBase_Table);
        $join = " SELECT COUNT(*) AS pt"
            .   " FROM " . $Target_DataBase_Table->GetTableName() . " AS ranking_log"
            .   " WHERE ranking_log.timestamp BETWEEN " . $Start_Timestamp . " AND " . $End_Timestamp
            .   " GROUP BY ranking_log.link_id"
            .   " HAVING pt >= " . $min_pt
            .   " ORDER BY pt DESC"
            .   " LIMIT " . $limit;
        return $join;
    }

    private function GetAllLinkTotalRankingJoinSql(object &$Target_DataBase_Table)
    {
        list($min_pt, $limit) = $this->GetJoinSqlReuiredParameter($Target_DataBase_Table);
        $column_name = "";
        if($Target_DataBase_Table instanceof Table_Access_Ranking_In_Log)
        {
            $column_name = "total_in_count";
        }
        elseif($Target_DataBase_Table instanceof Table_Access_Ranking_Out_Log)
        {
            $column_name = "total_out_count";
        }
        if($column_name)
        {        
            $join = " SELECT " . $column_name . " AS pt"
                .   " FROM " . $this->System->DataBase_Manager->Access_Ranking->GetTableName() . " AS ranking_log"
                .   " WHERE pt >= " . $min_pt
                .   " ORDER BY pt DESC"
                .   " LIMIT " . $limit;
            return $join;
        }
        return false;
    }

    private function GetCategoryPeriodRankingJoinSql(object &$Target_DataBase_Table, int $Start_Timestamp, int $End_Timestamp)
    {
        list($min_pt, $limit) = $this->GetJoinSqlReuiredParameter($Target_DataBase_Table);
        $join = "SELECT Count(ranking_log.id) AS pt"
            .   " FROM " . $Target_DataBase_Table->GetTableName() . " AS ranking_log"
            .   " WHERE ranking_log.link_id = link_category.link_id"
            .   " AND ranking_log.timestamp BETWEEN " . $Start_Timestamp . " AND " . $End_Timestamp
            .   " GROUP BY ranking_log.link_id"
            .   " HAVING pt >= " . $min_pt
            .   " ORDER BY pt DESC"
            .   " LIMIT " . $limit;
/*
        $join = " SELECT ranking.id, COUNT(ranking.id) AS pt"
            .   " FROM {prefix}links_data AS link, {$Target_DataBase_Table->GetTableName()} AS ranking"
            .   " WHERE link.id = ranking.id"
            .   " AND link.category LIKE '%&{$Category_Path}&%'"
            .   " AND ranking.time BETWEEN {$Start_Timestamp} AND {$End_Timestamp}"
            .   " GROUP BY ranking.id"
            .   " HAVING pt >= {$min_pt}"
            .   " ORDER BY pt DESC"
            .   " LIMIT {$limit}";
*/
        return $join;
    }

    private function GetCategoryTotalRankingJoinSql(object &$Target_DataBase_Table)
    {
        list($min_pt, $limit) = $this->GetJoinSqlReuiredParameter($Target_DataBase_Table);
        $column_name = "";
        if($Target_DataBase_Table instanceof Table_Access_Ranking_In_Log)
        {
            $column_name = "total_in_count";
        }
        elseif($Target_DataBase_Table instanceof Table_Access_Ranking_Out_Log)
        {
            $column_name = "total_out_count";
        }
        if($column_name)
        {
            $join = "SELECT " . $column_name . " AS pt"
                .   " FROM " . $this->System->DataBase_Manager->Access_Ranking->GetTableName() . " AS ranking_log"
                .   " WHERE ranking_log.link_id = link_category.link_id"
                .   " AND pt >= " . $min_pt
                .   " ORDER BY pt DESC"
                .   " LIMIT " . $limit;
/*
            $join = " SELECT ranking.id, {$column_name} AS pt"
                .   " FROM {prefix}links_data AS link, {prefix}access_ranking_counter AS ranking"
                .   " WHERE link.id = ranking.id"
                .   " AND link.category LIKE '%&{$Category_Path}&%'"
                .   " AND pt >= {$min_pt}"
                .   " ORDER BY pt DESC"
                .   " LIMIT {$max_pt}";
*/
            return $join;
        }
        return false;
    }

}