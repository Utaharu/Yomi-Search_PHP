<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Operator;

use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Error_Report_Operator
{
	private object $System;
	private Link_Search_Manager $Link_Search_Manager;
	private Error_Manager $Error_Manager;

    private array $Registration_Data;

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
		$this->Error_Manager = new Error_Manager("Report Operator Error");
	}

	private function MakeRegistrationData()
	{
		$this->Registration_Data['link_id'] = preg_replace('/\D/', '', $this->System->User->HttpQueries('link_id'));
		// 説明
        $this->Registration_Data['description'] = str_replace(array("\r\n", "\r"), "\n", $this->System->User->HttpQueries('description'));
		$this->Registration_Data['description'] = htmlspecialchars($this->Registration_Data['discription'], ENT_HTML5 and ENT_QUOTES);
		// 名前
		$this->Registration_Data['user_name'] = str_replace(array("\r", "\n"), '', $this->System->User->HttpQueries('user_name'));
		$this->Registration_Data['user_name'] = htmlspecialchars($this->Registration_Data['user_name'], ENT_HTML5 and ENT_QUOTES);
		// E-Mail
		$this->Registration_Data['mail_address'] = str_replace(array("\r", "\n"), '', $this->System->User->HttpQueries('mail_address'));
		$this->Registration_Data['mail_address'] = htmlspecialchars($this->Registration_Data['mail_address'] ,ENT_HTML5 and ENT_QUOTES);
        $this->Registration_Data['ip_address'] = $this->System->User->IpAddress();

		if($this->System->User->HttpQueries('type_no_link'))
		{
            $this->Registration_Data['dead_link'] = true;
		}
		else
		{
			$this->Registration_Data['dead_link'] = false;
		}
		if($this->System->User->HttpQueries('type_move'))
		{
            $this->Registration_Data['transfer'] = true;
		}
		else
		{
			$this->Registration_Data['transfer'] = false;
		}
		
		if($this->System->User->HttpQueries('type_bana_no_link'))
		{
			$this->Registration_Data['dead_banner'] = true;
		}
		else
		{
			$this->Registration_Data['dead_banner'] = false;
		}
		
		if($this->System->User->HttpQueries('type_ill'))
		{
			$this->Registration_Data['violation_terms'] = true;
		}
		else
		{
			$this->Registration_Data['violation_terms'] = false;
		}
		
		if($this->System->User->HttpQueries('type_other'))
		{
			$this->Registration_Data['other'] = true;
		}
		else
		{
			$this->Registration_Data['other'] = false;
		}
	}

    private function VerifyRegstrationData()
    {
    	if(!$this->Registration_Data['link_id'] or !is_numeric($this->Registration_Data['link_id']))
		{
			$this->Error_Manager->SetLog("IDが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
        elseif(!$this->LinkIDExists($this->Registration_Data['link_id']))
        {
            $this->Error_Manager->SetLog("報告対象のIDが見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
		elseif(!$this->Registration_Data['dead_link'] and !$this->Registration_Data['transfer'] and !$this->Registration_Data['dead_banner'] and !$this->Registration_Data['violation_terms'] and !$this->Registration_Data['other'])
		{
			// 報告種別のチェック無し(リンク切れ=0/サイト移転=1/バナーリンク切れ=2/規約違反=3/その他=4)
			$this->Error_Manager->SetLog("「通知種別」に最低一つはチェックしてください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(strlen($this->Registration_Data['comment'] . $this->Registration_Data['user_name'] . $this->Registration_Data['mail_address']) > 500)
		{
			// 文字数制限チェック
			$this->Error_Manager->SetLog("コメント、お名前、E-Mailの文字数は<br>合計250文字(全角換算)以内でご記入ください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
        $this->CheckDupulicateReport($this->Registration_Data['link_id'], $this->System->User->Ipaddress());
    }


	private function LinkIDExists(int $Link_Id)
	{
		//リンクIDの存在チェック
		$this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($Link_Id);
		$this->Link_Search_Manager->SearchLinks();
		$comparsion = $this->Link_Search_Manager->GetLinkList();
		if(count($comparsion) <= 0 or $Link_Id <= 0 or $comparsion[0]->LinkId() != $Link_Id)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	private function CheckDupulicateReport(int $Link_Id, string $IpAddress)
	{
		//reportログのチェック
		$remote_ip = $_SERVER['REMOTE_ADDR'];
        $sql_result = $this->System->DataBase_Manager->Link_Error_Report->SelectSql
			(
				"link_error.report_id, link_error.ip_address",
				"",
				"link_error.ip_address = '{$IpAddress}'"
			);
		$sql_result = $sql_result->fetch_all(MYSQLI_ASSOC);
		if(is_array($sql_result))
		{
			//同一IPからの報告数制限
			if(count($sql_result) > 5)
			{
				$this->Error_Manager->SetLog("同一ユーザからの報告上限に達した為、リンク切れの報告の受付はできません。管理者に、直接ご報告お願いいたします。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			//同じLinkIDへの多重報告
			foreach($sql_result as $line)
			{
				if($line and isset($line['id']) and $line['id'] and $line['id'] == $Link_Id)
				{
					$this->Error_Manager->SetLog("選択したサイトのリンク切れ報告は、既に受付済みです");
					$this->Error_Manager->PrintLastErrorAsPage();
				}
			}
		}
	}

	private function ShouldDenyApplication(string $IpAddress)
	{
		/*** 正規表現でのチェックに変更した方が良いかも。　***/
		//拒否するIPアドレスか確認
		if($this->System->Config->Options["error_report_filter"]->DenyIp())
		{
			$ip_list = $this->System->Config->Options["error_report_filter"]->DenyIp();
			$ip_flag = array_search($IpAddress, $ip_list);
			
			if($ip_flag !== false)
			{
				return true;
			}
		}
		return false;
	}

	public function RegisterApplication()
	{
        if($this->ShouldDenyApplication($this->System->User->IpAddress()))
        {
            $this->Error_Manager->SetLog("申請の受付を行う事が出来ません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        $this->MakeRegistrationData();
        $this->VerifyRegstrationData();
		
		//報告の保存。
        $this->Registration_Data = $this->System->DataBase_Manager->DataBase->EscapeStringValues($this->Registration_Data);
        $this->System->DataBase_Manager->Report->InsertSql(
            $this->Registration_Data['link_id'],
            $this->Registration_Data['user_name'],
            $this->Registration_Data['comment'],
            $this->Registration_Data['ip_address'],
            $this->Registration_Data['mail_address'],
            $this->Registration_Data['dead_link'],
            $this->Registration_Data['dead_banner'],
            $this->Registration_Data['transfer'],
            $this->Registration_Data['violation_terms'],
            $this->Registration_Data['other']
        );
		return true;
	}

	public function DeleteReportData(int $Link_Id)
	{
		if($Link_Id > 0)
		{
			return $this->System->DataBase_Manager->Report->DeleteSql($Link_Id);
		}
	}

}