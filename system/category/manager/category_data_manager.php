<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Category\Manager;

use PDOStatement;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Category_Data_Manager
{
    public const KEYWORD_SEARCH_OR = 0;
    public const KEYWORD_SEARCH_AND = 1;
    public const KEYWORD_SEARCH_FORMULA = 2;

    private object $System;
    private Error_Manager $Error_Manager;

    private array $Sql_Category_Search_Where = array();
    private array $Category_List = array();

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Category Data Manager Error");
    }

    public function RegistrationCategory(Category_Data_Object $Category_Object)
    {
        $category_type = $this->VerifyCategoryType($Category_Object);
        if(!$category_type)
        {
          $this->Error_Manager->SetLog("カテゴリタイプが不明の為、カテゴリの追加に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            if($category_type->type_id)
            {
                $Category_Object->TypeId(intval($category_type->type_id));
            }
            if($category_type->type_name)
            {
                $Category_Object->TypeName($category_type->type_name);
            }

            return $this->System->DataBase_Manager->Category_List->InsertSql
            (
                $Category_Object->TypeId(),
                $Category_Object->Alias(),
                $Category_Object->Title(),
                $Category_Object->ForSorting(),
                $Category_Object->Description(),
                $Category_Object->ImageUrl(),
                $Category_Object->IsEnabled()
            );
        }
    }

    public function RegistrationCategoryType(string $Category_Type_Name)
    {
        $category_type_result_sql = $this->System->DataBase_Manager->Category_Type->SelectSql
        (
            array($Category_Type_Name),
            "type_id",
            "",
            "type_name = ?"
        );
        if($category_type_result_sql == false or $category_type_result_sql->fetchObject() == false)
        {
            return $this->System->DataBase_Manager->Category_Type->InsertSql($Category_Type_Name);
        }
        else
        {
            $this->Error_Manager->SetLog("既に登録済みの為、カテゴリタイプの追加に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        return false;
    }

    public function RegistrationCategoryHierachy()
    {
    }

    public function UpdateCategory(Category_Data_Object $Category_Object)
    {
        $category_type = $this->VerifyCategoryType($Category_Object);
        $result_sql = $this->System->DataBase_Manager->Category_List->SelectSql
        (   
            array($Category_Object->CategoryId()),
            "category_id, title",
            "",
            "category_id = ?"
        );
        if($result_sql === false or ($result_sql instanceof PDOStatement and $result_sql->fetchObject() === false))
        {
            $this->Error_Manager->SetLog("カテゴリが見つからなかった為、カテゴリの更新に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!$category_type)
        {
            $this->Error_Manager->SetLog("カテゴリタイプが不明の為、カテゴリの更新に失敗しました。");
            $this->Error_Manager->PrintLastErrorAsPage();            
        }
        else
        {
            if($category_type->type_id)
            {
                $Category_Object->TypeId(intval($category_type->type_id));
            }
            if($category_type->type_name)
            {
                $Category_Object->TypeName($category_type->type_name);
            }

            return $this->System->DataBase_Manager->Category_List->UpdateSql
            (
                $Category_Object->CategoryId(),
                $Category_Object->TypeId(),
                $Category_Object->Alias(),
                $Category_Object->Title(),
                $Category_Object->Description(),
                $Category_Object->ImageUrl(),
                $Category_Object->IsEnabled(),
                $Category_Object->ForSorting()
            );
        }
    }

	public function DeleteCategory(int $Category_Id)
	{
		if($Category_Id > 0)
		{
			$this->System->DataBase_Manager->Table_Category_List->DeleteSql($Category_Id);
			$this->System->DataBase_Manager->Table_Category_Hierachy->DeleteSql($Category_Id);
		}
	}

    private function VerifyCategoryType(Category_Data_Object $Category_Object)
    {
        if(!$Category_Object->TypeName() and $Category_Object->TypeId() > 0)
        {
            $category_type_result_sql = $this->System->DataBase_Manager->Category_Type->SelectSql
                (
                    array($Category_Object->TypeId()),
                    "type_id, type_name",
                    "",
                    "type_id = ?"
                );
        }
        elseif($Category_Object->TypeName() and $Category_Object->TypeId() <= 0)
        {
            $category_type_result_sql = $this->System->DataBase_Manager->Category_Type->SelectSql
            (
                array($Category_Object->TypeName()),
                "type_id, type_name",
                "",
                "type_name = ?"
            );
        }
        if($category_type_result_sql instanceof PDOStatement)
        {
            return $category_type_result_sql->fetchObject();
        }
        else
        {
            return false;
        }
    }

}