<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Page;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Keyword_Ranking_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

   	private string $Template_Path = "";

	function __construct(object &$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Ranking Keyword Page Printer Error");
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

    public function PrintPage(array $Ranking_Data_List, int $Ranking_Start_Period_Timestamp = 0, int $Ranking_End_Period_Timestamp = 0)
    {

        $total_num = $this->System->DataBase_Manager->LogCount('log');
		$period_string = "";
		if($Ranking_Start_Period_Timestamp > 0 and $Ranking_End_Period_Timestamp > 0)
		{
			$period_string = date('Y/m/d H:i', $Ranking_Start_Period_Timestamp) . ' - ' . date('Y/m/d H:i', $Ranking_End_Period_Timestamp);
		}
		elseif($Ranking_Start_Period_Timestamp === 0 and $Ranking_End_Period_Timestamp === 0)
		{
			$period_string = ' - ' . date('Y/m/d H:i', time());
		}

        require $this->Template_Path . 'keyrank.html';
    }
}