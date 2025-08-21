<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Manager;

use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager\Access_Ranking_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Temporary_Data_Manager
{
    private object $System;
   	private Error_Manager $Error_Manager;
    private Link_Data_Manager $Link_Manager;
	private Link_Search_Manager $Link_Search_Manager;
	private Category_Search_Manager $Category_Search_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Link Temporary Data Manager Error");
        $this->Link_Manager = new Link_Data_Manager($Yomi_Search);
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
    }

	public function RegistrationTemporaryLinkData(Link_Data_Object $Registration_Object)
	{
		if($Registration_Object->LinkId() !== 0)
		{
			$this->Error_Manager->SetLog("LinkIdが入力されている為、データの追加に失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$category_id_list = array();
			if(is_array($Registration_Object->OrdinaryCategories()))
			{
				foreach($Registration_Object->OrdinaryCategories() as $category_data)
				{
					if($category_data instanceof Category_Data_Object)
					{
						$category_id_list[] = $category_data->CategoryId();
					}
				}
			}

			return $this->System->DetaBase_Manager->Table_Temporary_Link_List->InsertSql
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
				$Registration_Object->RegistrationTimestamp(),
				implode(" ", $Registration_Object->Tags()),
				implode(" ", $category_id_list)
			);
		}
		return false;	
	}

	/* Temporary Regstration Link -> Formal Regstration Link */
	public function DoFormalRegistration(int $Temporary_Id)
	{
		if($Temporary_Id > 0)
		{
			$this->Link_Search_Manager->ClearSearchData();
			$this->Link_Search_Manager->SearchTemporaryLinks
			(
				"temporary_id = {$Temporary_Id}"
			);
			$temporary_link_object = $this->Link_Search_Manager->GetLinkList(0);
			if($temporary_link_object == false)
			{
				$this->Error_Manager->SetLog("TemporaryID:{$Temporary_Id} は見つからなかった為、正式登録の移行に失敗しました。");
			}
			else
			{
				$category_object_list = array();
				foreach($temporary_link_object->OrdinaryCategories() as $category_id)
				{
					if(is_numeric($category_id))
					{
						$this->Category_Search_Manager->ClearSearchData();
						$this->Category_Search_Manager->SpecifySearchCriteriaOnCategoryId(intval($category_id));
						$this->Category_Search_Manager->SpecifySearchCriteriaOnIsEnabled(true);
						$this->Category_Search_Manager->SpecifySearchCriteriaOnTypeName($this->System::ORDINARY_CATEGORY_TYPE);
						$this->Category_Search_Manager->SearchCategories();
						$category_object = $this->Category_Search_Manager->GetCategoryList(0);
						if($category_object)
						{
							$category_object_list[] = $category_object;
						}
					}
					$temporary_link_object->ClearOrdinaryCategories();
					$temporary_link_object->OrdinaryCategories($category_object_list);
				}
				return $this->Link_Manager->RegistrationLinkData($temporary_link_object);
			}
		}
		return false;
	}

	public function DeleteTemporaryLinkData(int $Temporary_Id)
	{
		if($Temporary_Id > 0)
		{
			return $this->System->DataBase_Manager->Temporary_Link_List->DeleteSql($Temporary_Id);
		}
		return false;
	}


}