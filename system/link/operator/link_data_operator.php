<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Operator;

use Wonder_Link\Yomi_Search_PHP\System\Link\Data_Object\Link_Data_Object;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Temporary_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Operator\Link_Error_Report_Operator;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager\Access_Ranking_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Data_Object\Category_Data_Object;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Data_Operator
{
	private const AUTHORITY_ADMINISTRATOR = "Administrator";
	private const AUTHORITY_USER = "User"; 
	
    private object $System;
    private Error_Manager $Error_Manager;
    private Link_Data_Manager $Link_Manager;
	private Link_Temporary_Data_Manager $Temporary_Link_Manager;
	private Link_Search_Manager $Link_Search_Manager;
	private Category_Search_Manager $Category_Search_Manager;
    private Access_Ranking_Data_Manager $Access_Ranking_Manager;
    private Link_Error_Report_Operator $Report_Operator;

    private string $Authority_Type;

	/* # - New Links DAta Object - # */
	private Link_Data_Object $New_Link_Object;
    private object $Registered_Link_Object;

    function __construct(&$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Link Data Operator Error");
		$this->Category_Search_Manager = new Category_Search_Manager($Yomi_Search);
        $this->Link_Manager = new Link_Data_Manager($Yomi_Search);
		$this->Temporary_Link_Manager = new Link_Temporary_Data_Manager($Yomi_Search);
		$this->Link_Search_Manager = new Link_Search_Manager($Yomi_Search);
        $this->Access_Ranking_Manager = new Access_Ranking_Data_Manager($Yomi_Search);
    }

    public function Registration()
    {
		$registration_flag = false;
		$this->CheckPasswordAuthority($this->System->HttpQueries("password"));
		$this->CreateNewLinksObject();
		$this->VerifyNewLinkObject();
		if($this->Authority_Type === $this::AUTHORITY_USER and $this->System->Config->Options["registration"]->TemporaryRegistration() and $this->DuplicatedSiteUrlExists($this->New_Link_Object->SiteUrl(), 0, true))
		{
			$this->Error_Manager->SetLog("指定したホームページURLは、登録申請済みの為、登録受付できませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!$this->System->Config->Options["registration"]->TemporaryRegistration() and $this->DuplicatedSiteUrlExists($this->New_Link_Object->SiteUrl()))
		{
			$this->Error_Manager->SetLog("指定したホームページURLは、登録済みの為、登録受付できませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->New_Link_Object->RegistrationTimestamp(time());
			$this->New_Link_Object->UpdateTimestamp(time());

			switch($this->System->Config->Options["registration"]->TemporaryRegistration())
			{
				case true :
					$registration_flag = $this->Temporary_Link_Manager->RegistrationTemporaryLinkData($this->New_Link_Object);
				break;
				case false :
					$registration_flag = $this->Link_Manager->RegistrationLinkData($this->New_Link_Object);
				break;
			}
		}
		if(!$registration_flag)
		{
			$this->Error_Manager->SetLog("不明なエラーが発生した為、新規登録に失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			return $this->New_Link_Object;
		}
    }

	public function RegistrationPreview()
	{
		$this->CheckPasswordAuthority($this->System->HttpQueries("password"));
		$this->CreateNewLinksObject();
		$this->VerifyNewLinkObject();
		if($this->Authority_Type === $this::AUTHORITY_USER and $this->System->Config->Options["registration"]->TemporaryRegistration() and $this->DuplicatedSiteUrlExists($this->New_Link_Object->SiteUrl(), 0, true))
		{
			$this->Error_Manager->SetLog("指定したホームページURLは、登録申請済みの為、登録受付できませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!$this->System->Config->Options["registration"]->TemporaryRegistration() and $this->DuplicatedSiteUrlExists($this->New_Link_Object->SiteUrl()))
		{
			$this->Error_Manager->SetLog("指定したホームページURLは、登録済みの為、登録受付できませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			return $this->New_Link_Object;
		}
	}

	public function GetEditingLinkData()
	{
		$this->FindRegisteredLinkDataObject();
        if(!$this->Registered_Link_Object instanceof Link_Data_Object)
        {
            $this->Error_Manager->SetLog("登録情報が見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!$this->CheckPasswordAuthority($this->System->HttpQueries("password"), $this->Registered_Link_Object))
        {
            $this->Error_Manager->SetLog("パスワードが一致しませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
		else
		{
			return $this->Registered_Link_Object;
		}
	}

    public function Editing()
    {
		$edit_flag = false;
        $this->FindRegisteredLinkDataObject();
        if(!$this->Registered_Link_Object instanceof Link_Data_Object)
        {
            $this->Error_Manager->SetLog("登録情報が見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!$this->CheckPasswordAuthority($this->System->HttpQueries("password"), $this->Registered_Link_Object))
        {
            $this->Error_Manager->SetLog("パスワードが一致しませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
		else
		{
			$this->CreateNewLinksObject();
			$this->VerifyNewLinkObject();
			if($this->DuplicatedSiteUrlExists($this->New_Link_Object->SiteUrl(), $this->Registered_Link_Object->LinkId()))
			{
				$this->Error_Manager->SetLog("指定したホームページURLは、既に登録済みの為、変更受付できませんでした。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$this->New_Link_Object->LinkId($this->Registered_Link_Object->LinkId());
				$this->New_Link_Object->RegistrationTimestamp($this->Registered_Link_Object->RegistrationTimestamp());
				switch($this->Authority_Type)
				{
					case $this::AUTHORITY_USER :
						$this->New_Link_Object->UpdateTimestamp(time());
						$this->New_Link_Object->SpecialCategories($this->Registered_Link_Object->SpecialCategories());
					break;
					case $this::AUTHORITY_ADMINISTRATOR :
						$this->New_Link_Object->UpdateTimestamp($this->Registered_Link_Object->UpdateTimestamp());
						$this->New_Link_Object->IpAddress($this->Registered_Link_Object->IpAddress());
					break;
				}
				$edit_flag =  $this->Link_Manager->UpdateLinkData($this->New_Link_Object);
			}
		}
		if(!$edit_flag)
		{
			$this->Error_Manager->SetLog("不明なエラーが発生した為、登録変更に失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			return $this->New_Link_Object;
		}
    }

    public function Deleting()
    {
		$delete_flag = false;
        $this->FindRegisteredLinkDataObject();
        if(!$this->Registered_Link_Object instanceof Link_Data_Object)
        {
            $this->Error_Manager->SetLog("登録情報が見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!$this->CheckPasswordAuthority($this->System->HttpQueries("password"), $this->Registered_Link_Object))
        {
            $this->Error_Manager->SetLog("パスワードが一致しませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Report_Operator->DeleteReportData($this->Registered_Link_Object->LinkId());
            $this->Access_Ranking_Manager->DeleteRankingData($this->Registered_Link_Object);
            $delete_flag = $this->Link_Manager->DeleteLinkData($this->Registered_Link_Object);
        }
		if(!$delete_flag)
		{
			$this->Error_Manager->SetLog("不明なエラーが発生した為、登録削除に失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			return $this->Registered_Link_Object;
		}
    }

	public function ReissuePassword()
	{
		if(!$this->System->Config->Options["registration"]->ReissuePasswordFunction())
		{
			$this->Error_Manager->SetLog("パスワード再発行の機能は無効設定の為、受付できません");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!$this->System->HttpQueries("reissue_password_confirm"))
		{
			$this->Error_Manager->SetLog("パスワード再発行の確認チェックがなされていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			$this->FindRegisteredLinkDataObject();
			if(!$this->Registered_Link_Object instanceof Link_Data_Object)
			{
				$this->Error_Manager->SetLog("登録情報が見つかりませんでした。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif($this->Registered_Link_Object->MailAddress() != $this->System->User->HttpQueries("mail_address"))
			{
				$this->Error_Manager->SetLog("メールアドレスが確認できない為、パスワード再発行の処理に失敗しました。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			else
			{
				$new_password = "";
				$length = mt_rand($this->System->Config->Options["registration"]->PpasswordMinimumLength(), $this->System->Config->Options["registration"]->PpasswordMaximumLength());
				for($cnt = 1; $cnt <= $length; $cnt++)
				{
					$text_list = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ".", "@", "%", "_", "!");
					shuffle($text_list);
					$select_index = mt_rand(0, count($text_list)-1);
					if(isset($text_line[$select_index]))
					{
						$new_password .= $text_line[$select_index];
					}
				}

				if($this->System->Config->Options["registration_notice_mail"]->PpasswordReissueCompleted())
				{
					//password sendmail
				}
				else
				{
					print $new_password;
				}

			}
		}
	}

	private function HashPassword(string $Password)
	{
		return password_hash($Password, $this->System::PASSWORD_HASH_ALGO);
	}

    private function CreateNewLinksObject()
    {
        $this->New_Link_Object = new Link_Data_Object();
        if($this->System->User->HttpQueries("user_name"))
        {
            $this->New_Link_Object->UserName($this->System->User->HttpQueries("user_name"));
        }
        if($this->System->User->HttpQueries("mail_address"))
        {
            $this->New_Link_Object->MailAddress($this->System->User->HttpQueries("mail_address"));
        }
        if($this->System->User->HttpQueries("password"))
        {
            $this->New_Link_Object->Password($this->HashPassword($this->System->User->HttpQueries("password")));
        }
        if($this->System->User->HttpQueries("site_name"))
        {
            $this->New_Link_Object->SiteName($this->System->User->HttpQueries("site_name"));
        }
        if($this->System->User->HttpQueries("site_url"))
        {
            $this->New_Link_Object->SiteUrl($this->System->User->HttpQueries("site_url"));
        }
        if($this->System->User->HttpQueries("banner_url"))
        {
            $this->New_Link_Object->BannerUrl($this->System->User->HttpQueries("banner_url"));
        }
        if($this->System->User->HttpQueries("description"))
        {
            $this->New_Link_Object->Description(str_replace(array("\r\n", "\r", "\n"), "<br />", $this->System->User->HttpQueries("description")));
        }
        if($this->System->User->HttpQueries("ordinary_categories"))
        {
			$selected_categories = array();
			if($this->System->User->HttpQueries("ordinary_category1"))
			{
				for($i = 1; $i <= $this->System->Config->Options["registration"]->CategoryMaximumPieces(); $i++)
				{
					if($this->System->User->HttpQueries("ordinary_category{$i}"))
					{
						$selected_categories[] = $this->System->User->HttpQueries("ordinary_category{$i}");
					}
				}
			}
			elseif(is_array($this->System->User->HttpQueries("ordinary_categories")))
            {
				$selected_categories = $this->System->User->HttpQueries("ordinary_categories");
			}
			if(is_array($selected_categories))
			{
				foreach($selected_categories as $category_alias)
				{
					if($category_alias)
					{
						$add_category = new Category_Data_Object();
						$add_category->TypeName($this->System::ORDINARY_CATEGORY_TYPE);
						$add_category->Alias($category_alias);
						$this->New_Link_Object->OrdinaryCategories($add_category);
					}
				}
            }
        }
        if($this->System->User->HttpQueries("special_categories"))
        {
			if(is_array($selected_categories))
			{
				foreach($selected_categories as $category_alias)
				{
					if($category_alias)
					{
						$add_category = new Category_Data_Object();
						$add_category->TypeName($this->System::SPECIAL_CATEGORY_TYPE);
						$add_category->Alias($category_alias);
						$this->New_Link_Object->OrdinaryCategories($add_category);
					}
				}
            }
        }

        if($this->System->User->HttpQueries("tag"))
        {
            $this->New_Link_Object->Tags($this->System->User->HttpQueries("tag"));
        }
        $this->New_Link_Object->RegistrationTimestamp(time());
        $this->New_Link_Object->UpdateTimestamp(time());
        // - ip
        if($this->System->User->IpAddress())
		{
			$this->New_Link_Object->IpAddress($this->System->User->IpAddress());
		}
		elseif($this->System->User->HostName())
		{
			$this->New_Link_Object->IpAddress($this->System->User->HostName());
		}
    }

	private function VerifyUserName()
	{
	    // 名前
		$user_name_field = $this->System->Site_Config->Options["registration"]->UserNameField();
		$user_name_maximum_length = intval($this->System->Config->Options["registration"]->UserNameMaximumLength());
		if(!$user_name_field and $this->New_Link_Object->UserName())
		{
			$this->Error_Manager->SetLog("お名前の入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
        elseif($user_name_field >= 2 and !$this->New_Link_Object->UserName())
        {
            $this->Error_Manager->SetLog("お名前が入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(mb_strlen($this->New_Link_Object->UserName()) > $user_name_maximum_length)
        {
            $this->Error_Manager->SetLog("お名前は、{$user_name_maximum_length}文字以内で入力して下さい。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
	}

	private function VerifyPassword()
	{
		$password_maximum_length = intval($this->System->Config->Options["registration"]->PasswordMaximumLength());
		$password_minimum_length = intval($this->System->Config->Options["registration"]->PasswordMinimumLength());
		//	if($Form['mode'] != "act_mente")
		if(!$this->New_Link_Object->Password())
		{
            $this->Error_Manager->SetLog("パスワードが入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(preg_match("/\W/", $this->New_Link_Object->Password()))
		{
            $this->Error_Manager->SetLog("パスワードに全角文字は使用できません。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(strlen($this->System->User->HttpQueries("password")) < $password_minimum_length or strlen($this->System->User->HttpQueries("password")) >= $password_maximum_length)
		{
			$this->Error_Manager->SetLog("パスワードは、半角英数字で{$password_minimum_length}文字以上、{$password_maximum_length}文字以内で入力してください。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!password_verify($this->System->User->HttpQueries("confirm_password"), $this->New_Link_Object->Password()))
		{
			$this->Error_Manager->SetLog("2回のパスワード入力が一致しませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifyMailAddress()
	{
		// メールアドレス
		$mail_address_field = $this->System->Config->Options["registration"]->MailAddressField();
		$mail_address_maximum_length = intval($this->System->Config->Options["registration"]->MailAddressMaximumLength());
		if(!$mail_address_field and $this->New_Link_Object->MailAddress())
		{
			$this->Error_Manager->SetLog("メールアドレスの入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		if($mail_address_field >= 2 and !$this->New_Link_Object->MailAddress())
		{
            $this->Error_Manager->SetLog("メールアドレスが入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!filter_var($this->New_Link_Object->MailAddress(), FILTER_VALIDATE_EMAIL))
		{
			$this->Error_Manager->SetLog("メールアドレスの入力形式が正しくありません。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(strlen($this->New_Link_Object->MailAddress()) > $mail_address_maximum_length)
		{
	        $this->Error_Manager->SetLog("メールアドレスは、{$mail_address_maximum_length}文字以内で入力してください。");
            $this->Error_Manager->PrintLastErrorAsPage();
		}
	}
	
	private function VerifySiteName()
	{
		// ホームページのタイトル
		$site_name_field = $this->System->Config->Options["registration"]->SiteNameField();
		$site_name_maximum_length = intval($this->System->Config->Options["registration"]->SiteNameMaximumLength());

		if(!$site_name_field and $this->New_Link_Object->SiteName())
		{
			$this->Error_Manager->SetLog("ホームページタイトルの入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($site_name_field >= 2 and !$this->New_Link_Object->SiteName())
		{
			$this->Error_Manager->SetLog("ホームページタイトルが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(mb_strlen($this->New_Link_Object->SiteName()) > $site_name_maximum_length)
		{
			$this->Error_Manager->SetLog("ホームページタイトルは、{$site_name_maximum_length}文字以内で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifyBannerUrl()
	{
		// タイトルバナーのURL
		$banner_url_field = $this->System->Config->Options["registration"]->BannerUrlField();
		$banner_url_maximum_length = intval($this->System->Config->Options["registration"]->BannerUrlMaximumLength());
		if(!$banner_url_field and $this->New_Link_Object->BannerUrl())
		{
			$this->Error_Manager->SetLog("ホームページバナーのURL入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($banner_url_field >= 2 and !$this->New_Link_Object->BannerUrl())
		{
			$this->Error_Manager->SetLog("ホームページバナーのURLが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif (strlen($this->New_Link_Object->BannerUrl()) > $banner_url_maximum_length)
		{
			$this->Error_Manager->SetLog("ホームページバナーのURLは、半角{$banner_url_maximum_length}文字以内で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif (!preg_match("/^https?:\/\/.+\..+\.(gif|jpg|jpeg|png)$/", $this->New_Link_Object->BannerUrl() or !filter_var($this->New_Link_Object->BannerUrl(), FILTER_VALIDATE_URL)))
		{
			$this->Error_Manager->SetLog("ホームページバナーのURLの形式が、正しくありません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifySiteUrl()
	{
		$site_url_field = $this->System->Config->Options["registration"]->SiteUrlField();
		$site_url_maximum_length = intval($this->System->Config->Options["registration"]->SiteIUrlMaximumLength());
		if(!$site_url_field and $this->New_Link_Object->SiteUrl())
		{
			$this->Error_Manager->SetLog("ホームページアドレスの入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($site_url_field >= 2 and !$this->New_Link_Object->SiteUrl())
		{
			$this->Error_Manager->SetLog("ホームページアドレスが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(!filter_var($this->New_Link_Object->SiteUrl(), FILTER_VALIDATE_URL) or !preg_match("/^https?:)\/\/.+/", $this->New_Link_Object->SiteUrl()))
		{
			$this->Error_Manager->SetLog("ホームページアドレスの入力形式が正しくありません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(strlen($this->New_Link_Object->SiteUrl()) > $site_url_maximum_length)
		{
			$this->Error_Manager->SetLog("ホームページアドレスは、{$site_url_maximum_length}文字以内で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifyDescription()
	{
		$description_field = $this->System->Config->Options["registration"]->DescriptionField();
		$description_maximum_length = intval($this->System->Config->Options["registration"]->DescriptionMaximumLength());
		if(!$description_field and $this->New_Link_Object->Description())
		{
			$this->Error_Manager->SetLog("ホームページの紹介文の入力欄は無効設定になっている為、受付できません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($description_field >= 2 and !$this->New_Link_Object->Description())
		{
			$this->Error_Manager->SetLog("ホームページの紹介文が入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(mb_strlen($this->New_Link_Object->Description()) > $description_maximum_length)
		{
			$this->Error_Manager->SetLog("ホームページの紹介文は、{$description_maximum_length}文字以内で入力してください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifyTags()
	{
		// キーワード
		$tag_keyword_field = $this->System->Config->Options["registration"]->TagKeywordField();
		$tag_keyword_maximum_pieces = intval($this->System->Config->Options["registration"]->TagKeywordMaximumPieces());
		if(!$tag_keyword_field and count($this->New_Link_Object->Tags()) > 0)
		{
			$this->Error_Manager->SetLog("タグキーワードの入力欄は無効設定になっている為、受付できません");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($tag_keyword_field >= 2 and count($this->New_Link_Object->Tags()) === 0)
		{
			$this->Error_Manager->SetLog("タグキーワードが入力されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(count($this->New_Link_Object->Tags()) > $tag_keyword_maximum_pieces)
		{
			$this->Error_Manager->SetLog("タグキーワードは、スペース区切りで{$tag_keyword_maximum_pieces}個までにしてください。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif($this->CheckDuplicateTags())
		{
			$this->Error_Manager->SetLog("重複しているタグキーワードがあります。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
	}

	private function VerifyOrdinaryCategories()
	{
		$category_minimum_pieces = intval($this->System->Config->Options["registration"]->CategoryMinimumPieces());
		$category_maximum_pieces = intval($this->System->Config->Options["registration"]->CategoryMaximumPieces());
		if(count($this->New_Link_Object->OrdinaryCategories()) <= 0)
		{
			$this->Error_Manager->SetLog("カテゴリが選択されていません。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		elseif(count($this->New_Link_Object->OrdinaryCategories()) < $category_minimum_pieces or count($this->New_Link_Object->OrdinaryCategories()) >= $category_maximum_pieces)
		{
			$this->Error_Manager->SetLog("登録できるカテゴリ数は、{$category_minimum_pieces}から{$category_maximum_pieces}個までです。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			/* Category Exists*/
			$check_category_list = $this->New_Link_Object->OrdinaryCategories();
			$this->New_Link_Object->ClearOrdinaryCategories();
			if(is_array($check_category_list))
			{
				foreach($check_category_list as $check_category)
				{
					if($check_category instanceof Category_Data_Object)
					{
						$this->Category_Search_Manager->ClearSearchData();
						$this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($check_category->Alias());
						$this->Category_Search_Manager->SearchCategories();
						$category_data_object = $this->Category_Search_Manager->GetCategoryList(0);
						if(!$category_data_object)
						{
							$this->Error_Manager->SetLog("存在しないカテゴリが選択されています。");
							$this->Error_Manager->PrintLastErrorAsPage();
						}
						elseif($this->Authority_Type != $this::AUTHORITY_ADMINISTRATOR and !$category_data_object->RegistrableFlag())
						{
							$this->Error_Manager->SetLog("登録が許可されていないカテゴリが選択されています。");
							$this->Error_Manager->PrintLastErrorAsPage();
						}
						else
						{
							$this->New_Link_Object->OrdinaryCategories($category_data_object);
						}
					}
				}
			}
		}
	}

	private function VerifySpecialCategories()
	{
		/* Category Exists*/
		$check_category_list = $this->New_Link_Object->SpecialCategories();
		$this->New_Link_Object->ClearSpecialCategories();
		if(is_array($check_category_list))
		{
			foreach($check_category_list as $check_category)
			{
				if($check_category instanceof Category_Data_Object)
				{
					$this->Category_Search_Manager->ClearSearchData();
					$this->Category_Search_Manager->SpecifySearchCriteriaOnAlias($check_category->Alias());
					$this->Category_Search_Manager->SearchCategories();
					$category_data_object = $this->Category_Search_Manager->GetCategoryList(0);
					if(!$category_data_object)
					{
						$this->Error_Manager->SetLog("存在しない特別カテゴリが選択されています。");
						$this->Error_Manager->PrintLastErrorAsPage();
					}
					else
					{
						$this->New_Link_Object->SpecialCategories($category_data_object);
					}
				}
			}
		}
	}

	private function VerifyNotificationToAdministrator()
	{
//		if($Form['mode'] != "act_mente" && $Form['changer'] != "admin")
		if($this->Authority_Type == $this::AUTHORITY_USER and $this->System->Config->Options["notify_administrator"]->IsEnable())
		{
			// 管理人へのメッセージ
			if($this->System->Config->Options["notify_administrator"]->MessageField() >= 2 and !$this->System->User->HttpQueries("notification"))
			{
				$this->Error_Manager->SetLog("管理人へのメッセージ記入必須項目です。入力してください。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif(mb_strlen($this->System->User->HttpQueries("notification")) > intval($this->System->Config->Options["notify_administrator"]->MessageMaximumLength()))
			{
				$this->Error_Manager->SetLog
					(
						"管理人へのメッセージは、"
						. intval($this->System->Config->Options["notify_administrator"]->MmessageMaximumLength())
						. "文字以内で入力してください。"
					);
				$this->Error_Manager->PrintLastErrorAsPage();
			}
		
			if($this->System->Config->RegstrationSettings("notify_administrator", "request_category_field") >= 2 and !$this->System->User->HttpQueries('Fadd_kt'))
			{
				$this->Error_Manager->SetLog("追加して欲しいカテゴリは、記入必須項目です。入力してください。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			elseif(mb_strlen($this->System->User->HttpQueries("Fadd_kt")) > intval($this->System->Config->Options["notify_administrator"]->RequestCategoryMaximumLength()))
			{
				$this->Error_Manager->SetLog
					(
						"追加して欲しいカテゴリは、"
						. intval($this->System->Config->Options["notify_administrator"]->RequestCategoryMaximumLength())
						. "文字以内で入力してください。"
					);
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			if($this->System->User->HttpQueries("Fsougo") and $this->System->Config->Options["notify_administrator"]->MutualLink())
			{
				//相互リンクする
			}
		}
	}
/*
	private function VerifyDenyWord()
	{
		if($this->System->Config->Options->GetValue('kt_no_word'))
		{
			// ワードチェック対象の項目
			$check_str = implode(' ', array($Form['Fname'], $Form['Femail'], $Form['Furl'], $Form['Fbana_url'], $Form['Ftitle'], $Form['Fsyoukai'], $Form['Fkey']));
			$no_words = explode(' ', $cfg_reg['kt_no_word']);
			foreach($no_words as $word)
			{
				if(stristr($check_str, $word))
				{
					mes("登録データの中にが禁止されている言葉が入っています。<br>登録しようとしているデータのジャンルをこのサーチエンジンが禁止している可能性があります。", "ワードチェックエラー", "back_reg");
				}
			}
			if(!$_SERVER["REMOTE_HOST"])
			{
				$_SERVER["REMOTE_HOST"] = gethostbyaddr($_SERVER["REMOTE_ADDR"]);
			}
			$addr_host = $_SERVER["REMOTE_ADDR"] . " " . $_SERVER["REMOTE_HOST"];
			foreach($no_words as $word)
			{
				if(stristr($addr_host, $word))
				{
					mes("このIP又はホスト名からの登録は禁止されている可能性があります。<br>{$_SERVER["REMOTE_ADDR"]}/{$_SERVER["REMOTE_HOST"]}<br>", "IP/HOSTチェックエラー", "back_reg");
				}
			}
		}	
	}
*/
    private function VerifyNewLinkObject()
    {
		$this->VerifyUserName();
		$this->VerifyPassword();
		$this->VerifyMailAddress();
		$this->VerifySiteName();
		$this->VerifyBannerUrl();
		$this->VerifySiteUrl();
		$this->VerifyDescription();
		$this->VerifyTags();
		$this->VerifyOrdinaryCategories();
		$this->VerifySpecialCategories();
		$this->VerifyNotificationToAdministrator();
		return;
    }

	protected function CheckDuplicateTags()
	{
		$check_tag = array();
		if($this->New_Link_Object instanceof Link_Data_Object)
		{
			$check_tag = $this->New_Link_Object->Tags();
		}
		if(is_array($check_tag))
		{
			for($check_cnt = 0; $check_cnt < count($check_tag); $check_cnt++)
			{
				$check_word = preg_quote(array_shift($check_tag), "/");
				if($check_word and preg_grep("/^" . $check_word ."$/", $check_tag))
				{
					return true;
				}
			}
		}
		return false;
	}

    private function CheckPasswordAuthority(string $Password, Link_Data_Object $Target_Object = new Link_Data_Object())
    {
		$this->Authority_Type = $this::AUTHORITY_USER;
        if($Password)
        {
			if(password_verify($Password, $this->System->Config->Options["about_administrator"]->Password()))
            {
                $this->Authority_Type = $this::AUTHORITY_ADMINISTRATOR;
                return true;
            }
            elseif($Target_Object->Password() and password_verify($Password, $Target_Object->Password()))
            {
                if(!$this->System->Config->Options["registration"]->UserEditOperation())
                {
                    $this->Error_Manager->SetLog("登録者による修正・削除を行えない設定になっている為、処理を行えませんでした。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
                else
                {
                    return true;
                }
            }
        }
        return false;
    }

    private function FindRegisteredLinkDataObject()
    {
        unset($this->Registered_Link_Object);
        if(is_numeric($this->System->User->HttpQueries('link_id')))
        {
            $this->Link_Search_Manager->ClearSearchData();
            $this->Link_Search_Manager->SpecifySearchCriteriaOnLinkId(intval($this->System->User->HttpQueries('id')));
            $this->Link_Search_Manager->SearchLinks();
            $link_data = $this->Link_Search_Manager->GetLinkList(0);
            if($link_data instanceof Link_Data_Object)
            {
                $this->Registered_Link_Object = $link_data;
            }
        }
        return;
    }

	//URLの重複チェック
	private function DuplicatedSiteUrlExists(string $Site_Url, int $Link_Id = 0, bool $Check_Temporary_Data = false)
	{
		if(filter_var($Site_Url, FILTER_VALIDATE_URL))
		{
			$this->Link_Search_Manager->ClearSearchData();
			$this->Link_Search_Manager->SpecifySearchCriteriaOnSiteUrl($Site_Url);
			if($Check_Temporary_Data === false)
			{
				$this->Link_Search_Manager->SearchLinks();
			}
			else
			{
				$this->Link_Search_Manager->SearchTemporaryLinks();
			}
			$link_data = $this->Link_Search_Manager->GetLinkList(0);
			if($link_data instanceof Link_Data_Object and  $Link_Id > 0 and $link_data->LinkId() !== $Link_Id)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		return null;
	}
/*
		if($fl <= $i) {
			mes('そのURLはすでに登録されています<br><br>'.$log_data[1].' :<br>'.$log_data[2], '2重登録エラー', 'java');
		}
		if($fl == '2' && $i == '1' && $pre_log[2] != $_POST['Furl']) {
			mes('そのURLはすでに登録されています<br><br>'.$pre_title.' :<br>'.$_POST['Furl'], '2重登録エラー', 'java');
		}
	}
	if($_POST['changer'] != 'admin' && $cfg['user_check'] && $_POST['mode'] == 'act_regist' && $fl == 1) {
		// 仮登録モードでユーザの新規登録時
		$query = 'SELECT title FROM '.$db->db_pre.'log_temp WHERE url=\''.$_POST['Furl'].'\' LIMIT 1';
		$log_data = $db->single_num($query);
		if($log_data) {
			mes('そのURLは現在登録申請中です<br><br>'.$log_data[0].' :<br>'.$_POST['Furl'], '2重登録エラー', 'java');
		}
	}
*/

}