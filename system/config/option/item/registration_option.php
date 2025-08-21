<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;

class Registration_Option extends Option_Inheritance_Class
{

    protected object $System;
    protected string $Option_Group_Name = "registration";
    protected array $Object_Config = array();
    public Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Object_Config =
            array(
				'administrator_only' => false,//* 管理人のみが登録できるモード=no_regist(ON=1/OFF=0)	
				'temporary_registration' => false, // 仮登録モードに(しない=0/する=1)	
				'reissue_password_function'   => true,  // パスワード再発行を(しない=0/する=1)
				// ユーザ権限設定
				'user_edit_operation'   => true,//管理人のみが修正・削除できるモード=no_mente(ON=1/OFF=0)
				'user_edit_category' => true, // カテゴリ変更(可能=0/不可=1)
				"new_registration_title" => "新規登録",
				"new_registration_preview_title" => "新規登録プレビュー",
				"new_registration_completed_title" => "登録完了",
				"temporary_registration_completed_title" => "仮登録完了",
				"registration_editing_title" => "登録変更",
				"registration_editing_complete_title" => "登録変更完了",
				"registration_deletion_title" => "登録削除",
				"registration_deletion_completed_title" => "登録削除完了",
/*
	field : 0 = false, 1 = true , 2 = true & required
*/
				"banner_url_field" => 1,
				"banner_url_maximum_length" => 130,
				"mail_address_field" => 2,
				"mail_address_maximum_length" => 50,
				"password_minimum_length" => 5,
				"password_maximum_length" => 20,
				"site_name_field" => 2,
				"site_name_maximum_length" => 25,
				"site_url_field" => 2,
				"site_url_maximum_length" => 150,
				"description_field" => 2,
				"description_maximum_length" => 500,
				"user_name_field" => 2,
				"user_name_maximum_length" => 25,
				"tag_keyword_field" => 1,
				"tag_keyword_maximum_pieces" => 3,
				"category_field_multiple_type" => true,
				"category_minimum_pieces" => 1,
				"category_maximum_pieces" => 2
            );
        $this->Error_Manager = new Error_Manager("Option Registration Error");
		$this->LoadOptionsValue();
    }

	public function AdministratorOnly(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["administrator_only"];
		}
		else
        {
			$bool_value = $this->GetConvertBoolValue($Value);
			$this->Object_Config["administrator_only"] = $bool_value;
    	    return $this->ChangeOption("administrator_only", $bool_value);
		}
	}

	public function TemporaryRegistration(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["administrator_only"];
		}
		else
        {
			$bool_value = $this->GetConvertBoolValue($Value);
			$this->Object_Config["temporary_registration"] = $bool_value;
    	    return $this->ChangeOption("temporary_registration", $bool_value);
		}
	}

	public function ReissuePasswordFunction(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["reissue_password_function"];
		}
		else
        {
			$bool_value = $this->GetConvertBoolValue($Value);
			$this->Object_Config["reissue_password_function"] = $bool_value;
    	    return $this->ChangeOption("reissue_password_function", $bool_value);
		}
	}

	public function UserEditOperation(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["user_edit_operation"];
		}
		else
        {
			$bool_value = $this->GetConvertBoolValue($Value);
			$this->Object_Config["user_edit_operation"] = $bool_value;
			return $this->ChangeOption("user_edit_operation", $bool_value);
		}
	}

	public function NewRegistrationTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["new_registration_title"];
		}
		else
        {
			$this->Object_Config["new_registration_title"] = $Title;
			return $this->ChangeOption("new_registration_title", $Title);
		}
	}

	public function NewRegistrationPreviewTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["new_registration_preview_title"];
		}
		else
        {
			$this->Object_Config["new_registration_preview_title"] = $Title;
			return $this->ChangeOption("new_registration_preview_title", $Title);
		}
	}

	public function NewRegistrationCompletedTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["new_registration_completed_title"];
		}
		else
        {
			$this->Object_Config["new_registration_completed_title"] = $Title;
			return $this->ChangeOption("new_registration_completed_title", $Title);
		}
	}

	public function TemporaryRegistrationCompletedTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["temporary_registration_completed_title"];
		}
		else
        {
			$this->Object_Config["temporary_registration_completed_title"] = $Title;
			return $this->ChangeOption("temporary_registration_completed_title", $Title);
		}
	}

	public function RegistrationEditingTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["temporary_registration_completed_title"];
		}
		else
        {
			$this->Object_Config["temporary_registration_completed_title"] = $Title;
			return $this->ChangeOption("temporary_registration_completed_title", $Title);
		}
	}

	public function RegistrationEditingCompleteTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["registration_editing_complete_title"];
		}
		else
        {
			$this->Object_Config["registration_editing_complete_title"] = $Title;
			return $this->ChangeOption("registration_editing_complete_title", $Title);
		}
	}

	public function RegistrationDeletionTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["registration_deletion_title"];
		}
		else
        {
			$this->Object_Config["registration_deletion_title"] = $Title;
			return $this->ChangeOption("registration_deletion_title", $Title);
		}
	}

	public function RegistrationDeletionCompletedTitle(?string $Title = null)
	{
		if(is_null($Title))
		{
			return $this->Object_Config["registration_deletion_completed_title"];
		}
		else
        {
			$this->Object_Config["registration_deeletion_completed_title"] = $Title;
			return $this->ChangeOption("registration_deletion_completed_title", $Title);
		}
	}

	public function BannerUrlField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["banner_url_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["banner_url_field"] = $int_value;
				return $this->ChangeOption("banner_url_field", $int_value);
			}
		}
	}

	public function BannerUrlMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["banner_url_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["banner_url_maximum_length"] = $int_value;
				return $this->ChangeOption("banner_url_maximum_length", $int_value);
			}
		}
	}

	public function MailAddressField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["mail_address_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["mail_address_field"] = $int_value;
				return $this->ChangeOption("mail_address_field", $int_value);
			}
		}
	}

	public function MailAddressMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["mail_address_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["mail_address_maximum_length"] = $int_value;
				return $this->ChangeOption("mail_address_maximum_length", $int_value);
			}
		}
	}

	public function PasswordMinimumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["password_minimum_length"];
		}
		else
        {
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["password_minimum_length"] = $int_value;
				return $this->ChangeOption("password_minimum_length", $int_value);
			}
		}
	}	

	public function PasswordMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["password_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["password_maximum_length"] = $int_value;
				return $this->ChangeOption("password_maximum_length", $int_value);
			}
		}
	}	

	public function SiteNameField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["site_name_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["site_name_field"] = $int_value;
				return $this->ChangeOption("site_name_field", $int_value);
			}
		}
	}

	public function SiteNameMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["site_name_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["site_name_maximum_length"] = $int_value;
				return $this->ChangeOption("site_name_maximum_length", $int_value);
			}
		}
	}

	public function SiteUrlField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["site_url_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["site_url_field"] = $int_value;
				return $this->ChangeOption("site_url_field", $int_value);
			}
		}
	}

	public function SiteUrlMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["site_url_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["site_url_maximum_length"] = $int_value;
				return $this->ChangeOption("site_url_maximum_length", $int_value);
			}
		}
	}

	public function DescriptionField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["description_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["description_field"] = $int_value;
				return $this->ChangeOption("description_field", $int_value);
			}
		}
	}

	public function DescriptionMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["description maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["description_maximum_length"] = $int_value;
				return $this->ChangeOption("description_maximum_length", $int_value);
			}
		}
	}

	public function UserNameField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["user_name_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["user_name_field"] = $int_value;
				return $this->ChangeOption("user_name_field", $int_value);
			}
		}
	}

	public function UserNameMaximumLength(null|int|string $Length = null)
	{
		if(is_null($Length))
		{
			return $this->Object_Config["user_name_maximum_length"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Length, 0);
			if($int_value !== false)
			{
				$this->Object_Config["user_name_maximum_length"] = $int_value;
				return $this->ChangeOption("user_name_maximum_length", $int_value);
			}
		}
	}

	public function TagKeywordField(null|int|string $Field_Type = null)
	{
		if(is_null($Field_Type))
		{
			return $this->Object_Config["tag_keyword_field"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Field_Type, 0, 3);
			if($int_value !== false)
			{
				$this->Object_Config["tag_keyword_field"] = $int_value;
				return $this->ChangeOption("tag_keyword_field", $int_value);
			}
		}
	}

	public function TagKeywordMaximumPieces(null|int|string $Pieces = null)
	{
		if(is_null($Pieces))
		{
			return $this->Object_Config["tag_keyword_maximum_pieces"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Pieces, 0);
			if($int_value !== false)
			{
				$this->Object_Config["tag_keyword_maximum_pieces"] = $Pieces;
				return $this->ChangeOption("tag_keyword_maximum_pieces", $int_value);
			}
		}
	}

	public function CategoryFieldMultipleType(null|string|int|bool $Value = null)
	{
		if(is_null($Value))
		{
			return $this->Object_Config["category_field_multiple_type"];
		}
		else
        {
			$bool_value = $this->GetConvertBoolValue($Value);
			$this->Object_Config["category_field_multiple_type"] = $bool_value;
    	    return $this->ChangeOption("category_field_multiple_type", $bool_value);
		}
	}
	
	public function CategoryMinimumPieces(null|int|string $Pieces = null)
	{
		if(is_null($Pieces))
		{
			return $this->Object_Config["category_minimum_pieces"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Pieces, 0);
			if($int_value !== false)
			{
				$this->Object_Config["category_minimum_pieces"] = $int_value;
				return $this->ChangeOption("category_minimum_pieces", $int_value);
			}
		}
	}

	public function CategoryMAximumPieces(null|int|string $Pieces = null)
	{
		if(is_null($Pieces))
		{
			return $this->Object_Config["category_maximum_pieces"];
		}
		else
        {	
			$int_value = $this->GetConvertIntValue($Pieces, 0);
			if($int_value !== false)
			{
				$this->Object_Config["category_maximum_pieces"] = $$int_value;
				return $this->ChangeOption("category_maximum_pieces", $int_value);
			}
		}
	}

}