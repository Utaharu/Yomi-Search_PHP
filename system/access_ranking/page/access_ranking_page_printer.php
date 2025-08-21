<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Page;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Access_Ranking_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

   	private string $Template_Path = "";

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Ranking Access Page Printer Error");
        switch($this->System->Page_Type)
        {
            default :
				$this->Template_Path = $this->System->Config->Directory_List['pc_template'];
			break;
            case "mobile" :
                $this->Template_Path = $this->System->Config->Directory_List['phone_template'];
            break;
        }
	}

    public function PrintPage(array $Ranking_Data_List , $Category_Data = "" , int $Ranking_Start_Period_Timestamp = 0, int $Ranking_End_Period_Timestamp = 0)
    {
		$time = time();
//        $total_num = $this->System->DataBase_Manager->LogCount('log');
		$total_num = $this->System->DataBase_Manager->Access_Ranking->SelectSql("count(*)");

		$base_url ="";// $this->System->Config->Options->GetValue('rank');

		$period_string = "";
		if($Ranking_Start_Period_Timestamp > 0 and $Ranking_End_Period_Timestamp > 0)
		{
			$period_string = date('Y/m/d H:i', $Ranking_Start_Period_Timestamp) . ' - ' . date('Y/m/d H:i', $Ranking_End_Period_Timestamp);
		}
		elseif($Ranking_Start_Period_Timestamp === 0 and $Ranking_End_Period_Timestamp === 0)
		{
			$period_string = ' - ' . date('Y/m/d H:i', time());
		}

		$path = "";
		if(is_object($Category_Data))
		{
			if($Category_Data->type_name)
			{
				$path = "&path={$Category_Data->Path}";
			}
		}

		$rank_ys  = "<a href=\"{$base_url}?mode=rank_ys{$path}\">現在の人気ランキング</a>";
		$rank_bf  = "<a href=\"{$base_url}?mode=rank_bf{$path}\">前回の人気ランキング</a>";
		$rank_rui = "<a href=\"{$base_url}?mode=rank_rui{$path}\">人気ランキング(累計)</a>";
		$rev      = "<a href=\"{$base_url}?mode=rev{$path}\">現在のアクセスランキング</a>";
		$rev_bf   = "<a href=\"{$base_url}?mode=rev_bf{$path}\">前回のアクセスランキング</a>";
		$rev_rui  = "<a href=\"{$base_url}?mode=rev_rui{$path}\">アクセスランキング(累計)</a>";

        require $this->Template_Path . 'rank.html';
    }

}