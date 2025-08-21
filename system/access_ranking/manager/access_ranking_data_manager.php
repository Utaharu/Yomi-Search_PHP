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

class Access_Ranking_Data_Manager
{
    private object $System;
    private Error_Manager $Error_Manager;
    private Link_Search_Manager $Link_Search_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Access Ranking Data Manager Error");
        $this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
    }

    private function CheckLinkData(int $Link_Id)
    {
        $this->Link_Search_Manager->ClearSearchData();
        if(!$this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($Link_Id))
        {
            $this->Error_Manager->SetLog("リンクIDが不正な為、ランキングカウントの処理に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Link_Search_Manager->SearchLinks();
            $link_data = null;
            $link_data = $this->Link_Search_Manager->GetLinkList(0);
            if($link_data === false)
            {
                $this->Error_Manager->SetLog("リンクIDが見つからない為、ランキングカウントの処理に失敗しました。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            else
            {
                return $link_data;
            }
        }
        return false;
    }

    public function AccessInCountUp(int $Link_Id)
    {
        return $this->CountUp($this->System->DataBase_Manager->Table_Access_Ranking_In_Log, $Link_Id);
    }

    public function AccessOutCountUp(int $Link_Id)
    {
        return $this->CountUp($this->System->DataBase_Manager->Table_Access_Ranking_Out_Log, $Link_Id);
    }

    private function CountUp(object &$Target_DataBase_Table, int $Link_Id)
    {
        $link_data = $this->CheckLinkData($Link_Id);
        if($link_data)
        {
            $time = time();
            if($Target_DataBase_Table instanceof Table_Access_Ranking_In_Log)
            {
                $log_update_limit = $time - intval($this->System->Config->Options["in_access_ranking"]->UpdateTime()) * 3600;
            }
            elseif($Target_DataBase_Table instanceof Table_Access_Ranking_Out_Log)
            {
                $log_update_limit = $time - intval($this->System->Config->Options["out_access_ranking"]->UpdateTime()) * 3600;
            }            
            $sql_result = $Target_DataBase_Table->SelectSql
                (
                    array($Link_Id, $this->System->User->IpAddrress(), $log_update_limit),
                    "",
                    "",
                    "link_id = ? AND ip_address=? AND timestamp > ?"
                );
            if(!$sql_result)
            {
                if($Target_DataBase_Table->InsertSql($Link_Id, $this->System->User->IpAddress(), $time))
                {
                    $result_sql = $this->System->DataBase_Manager->Access_Ranking->SelectSql
                        (
                            array($Link_Id),
                            "",
                            "",
                            "link_id = ?"
                        );
                    $access_counter_data = $result_sql->fetchObject();
                    if($Target_DataBase_Table instanceof Table_Access_Ranking_In_Log and $access_counter_data->total_in_count)
                    {
                        $this->System->DataBase_Manager->Access_Ranking->UpdateSql($Link_Id, intval($access_counter_data->total_in_count + 1));
                    }
                    elseif($Target_DataBase_Table instanceof Table_Access_Ranking_Out_Log and $access_counter_data->total_out_count)
                    {
                        $this->System->DataBase_Manager->Access_Ranking->UpdateSql($Link_Id, -1, intval($access_counter_data->total_out_count + 1));
                    }
                }
            }    
            return $link_data->SiteUrl();
        }
        return false;
    }

    public function AddRankingData(Link_Data_Object $Link_Data)
    {
        if($Link_Data->LinkId() > 0)
        {
            $sql_result = $this->System->DetaBase_Manager->Access_Ranking->SelectSql
                (
                    array($Link_Data->LinkId()),
                    "link_id",
                    "",
                    "link_id = ?"
                );
            if(!$sql_result)
            {
                return $sql_result = $this->System->DataBase_Manager->Access_Ranking->InsertSql($Link_Data->LinkId());
            }
        }
    }

    public function DeleteRankingData(Link_Data_Object $Link_Data)
    {
        if($Link_Data->LinkId() > 0)
        {
            $this->System->DataBase_Manager->Access_Ranking_In_Log->DeleteSql($Link_Data->LinkId());
            $this->System->DataBase_Manager->Access_Ranking_Out_Log->DeleteSql($Link_Data->LinkId());
            return $this->System->DataBase_Manager->Access_Ranking_Counter->DeleteSql($Link_Data->LinkId());            
        }
        return false;
    }

}