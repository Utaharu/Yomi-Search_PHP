<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Manager;

use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Data_Object\Access_Ranking_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager\Access_Ranking_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Data_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Data_Manager
{
    private object $System;
   	private Error_Manager $Error_Manager;
	private Link_Search_Manager $Link_Search_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Link Data Manager Error");
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
    }

	
	public function DeleteLinkData(Link_Data_Object $Delete_Object)
	{
		if($Delete_Object->LinkId() > 0)
		{
			$this->System->Access_Ranking_Data_Manager->DeleteRankingData($Delete_Object->LinkId());
			$this->System->DataBase_Manager->Link_Category->DeleteSql($Delete_Object->LinkId());
			$this->System->DataBase_Manager->Link_Tag->DeleteSql($Delete_Object->LinkId());
			$this->System->DataBase_Manager->Table_Links_Data->DeleteSql($Delete_Object->LinkId());	
		}
	}

	public function UpdateLinkData(Link_Data_Object $Update_Object)
	{
		if($Update_Object->LinkId() <= 0)
		{
			$this->Error_Manager->SetLog("LinkIdが不正な値の為、データの更新は行われませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->Link_Search_Manager->ClearSearchData();
			$this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($Update_Object->LinkId());
			$this->Link_Search_Manager->SearchLinks();
			if($this->Link_Search_Manager->GetLinkList(0) == false)
			{
				$this->Error_Manager->SetLog("対象のリンクデータが見つからなかった為、データの更新に失敗しました。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$this->System->DataBase_Manager->Link_Category->DeleteSql($Update_Object->LinkId());
				$this->RegistrationLinkCategory($Update_Object->LinkId(), $Update_Object->OrdinaryCategories());
				$this->RegistrationLinkCategory($Update_Object->LinkId(), $Update_Object->SpecialCategories());
				$this->System->DataBase_Manager->Link_Tag->DeleteSql($Update_Object->LinkId());
				$this->RegistrationLinkTag($Update_Object->LinkId(), $Update_Object->Tags());			
				return $this->System->DetaBase_Manager->Link_List->UpdateSql
				(
					$Update_Object->LinkId(),
					$Update_Object->SiteName(),
					$Update_Object->SiteUrl(),
					$Update_Object->BannerUrl(),
					$Update_Object->Description(),
					$Update_Object->UserName(),
					$Update_Object->MailAddress(),
					$Update_Object->Password(),
					$Update_Object->IpAddress(),
					$Update_Object->UpdateTimestamp(),
					$Update_Object->RegistrationTimestamp()
				);
			}
		}
		return false;
	}

	private function RegistrationLinkCategory(int $Link_Id, array $Categories)
	{
		if(is_array($Categories))
		{
			foreach($Categories as $category)
			{
				if($category instanceof Category_Data_Object and $category->CategoryId() > 0)
				{
					$this->System->DataBase_Manager->Link_Category->InsertSql($Link_Id, $category->CategoryId());
				}
			}
			return true;
		}
		return false;
	}
	
	private function RegistrationLinkTag(int $Link_Id, array $Tags)
	{
		if(is_array($Tags))
		{
			foreach($Tags as $tag_value)
			{
				if($tag_value)
				{
					$this->System->DataBase_Manager->Link_Tag->InsertSql($Link_Id, $tag_value);
				}
			}
			return true;
		}
		return false;
	}

	public function RegistrationLinkData(Link_Data_Object $Registration_Object)
	{
		if($Registration_Object->LinkId() !== 0)
		{
			$this->Error_Manager->SetLog("LinkIdが入力されている為、データの追加に失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$result_sql = $this->System->DetaBase_Manager->Link_List->InsertSql
			(
				$Registration_Object->SiteName(),
				$Registration_Object->SiteUrl(),
				$Registration_Object->BannerUrl(),
				$Registration_Object->Description(),
				$Registration_Object->UserName(),
				$Registration_Object->MailAddress(),
				$Registration_Object->Password(),
				$Registration_Object->IpAddress(),
				$Registration_Object->UpdateTimestamp(),
				$Registration_Object->RegistrationTimestamp()
			);
			if($result_sql)
			{
				$last_id_sql = $this->System->DataBase_Manager->Link_List->SelectSql(array(), "LAST_INSERT_ID();");
				$last_id = $last_id_sql->fetch_row();
				if(isset($last_id[0]))
				{
					$this->RegistrationLinkTag(intval($last_id[0]), $Registration_Object->Tags());
					$this->RegistrationLinkCategory(intval($last_id[0]), $Registration_Object->OrdinaryCategories());
					$this->RegistrationLinkCategory(intval($last_id[0]), $Registration_Object->SpecialCategories());
					return true;
				}
			}
			return false;
		}	
	}


}