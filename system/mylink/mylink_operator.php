<?php
namespace Wonder_Link\Yomi_Search_PHP\System\MyLink;

use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;

class MyLink_Operator
{
	private object $System;
	private Error_Manager $Error_Manager;
    private Link_Search_Manager $Link_Search_Manager;

	private string $Template_Path = "";

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
        $this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
		$this->Error_Manager = new Error_Manager("MyLink Operator Error");
    }

	public function AddMyLink(int $Link_Id)
	{
        if($Link_Id <= 0)
        {
            $this->Error_Manager->SetLog("IDが不正な為、MyLinkへの追加に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Link_Search_Manager->ClearSearchData();
            $this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId($Link_Id);
            if($this->Link_Search_Manager->GetLinkList() === false)
            {
                $this->Error_Manager->SetLog("IDが存在しない為、MyLinkへの追加に失敗しました。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            elseif($this->System->User->GetCookie('mylink4php'))
            {
                $id_array = explode(',', $this->System->User->GetCookie('mylink4php'));        
                if(!in_array($this->System->User->HttpQueries("id"), $id_array))
                {
                    array_push($id_array, $this->System->User->HttpQueries('id'));
                }
                $cookie_new = "";
                $cookie_new = implode(',', $id_array);
                $this->System->User->SetCookie('mylink4php', $cookie_new, time() + 31536000);
            }
        }
        return true;
    }

	public function DeleteMyLink(int $Link_Id)
	{
        if($Link_Id <= 0)
        {
            $this->Error_Manager->SetLog("IDが不正な為、MyLinkへの追加に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        { 
            if($this->System->User->GetCookie('mylink4php'))
            {
                $id_array = explode(',', $this->System->Ueser->GetCookie('mylink4php'));
                $delete_key = array_search($Link_Id, $id_array);
                if($delete_key === false)
                {
                    $this->Error_Manager->SetLog("MyLinkに登録されていないIDの為、削除処理に失敗しました。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
                else
                {
                    unset($id_array[$delete_key]);
                    $cookie_new = "";
                    $cookie_new = implode(',', $id_array);
                    $this->System->User->SetCookie('mylink4php', $cookie_new, time() + 31536000);
                }
            }
        }
        return true;
	}

}