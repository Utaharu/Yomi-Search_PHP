<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Registration_Notice_Mail_Option extends Option_Inheritance_Class
{
    protected object $System;
    protected string $Option_Group_Name = "registration_notice_mail";
    protected array $Object_Config = array();
    public Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Object_Config =
            array(
				'send_to_adminitrator' => false,// 管理人にメールを送信(しない=0/する=1)
				'send_to_registrant' => false,// 登録者にメールを送信(しない=0/する=1)
				'registration_completed' => false,// 新規登録完了メールを送信(しない=0/する=1)
				'edit_completed' => false, // 登録内容変更完了メールを送信(しない=0/する=1)
				'temporary_registration_completed' => false, // 仮登録完了メールを送信(しない=0/する=1)
				'password_reissue_completed'=> true // パスワード変更メールを送信(しない=0/する=1)
            );
        $this->Error_Manager = new Error_Manager("Option Regstration Notice Mail Error");
        $this->LoadOptionsValue();
    }

    public function SendToAdministrator(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["send_to_administrator"];
		}
		else
        {    
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["send_to_administrator"] = $bool_value;
            return $this->ChangeOption("send_to_administrator", $bool_value);
        }
    }

    public function SendToRegistrant(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["send_to_registrant"];
		}
		else
        {    
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["send_to_registrant"] = $bool_value;
            return $this->ChangeOption("send_to_registrant", $bool_value);
        }
    }

    public function RegistrationCompleted(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["registration_completed"];
		}
		else
        {
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["registration_completed"] = $bool_value;
            return $this->ChangeOption("registration_completed", $bool_value);
        }
    }
    
    public function EditCompleted(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["edit_completed"];
		}
		else
        {
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["edit_completed"] = $bool_value;
            return $this->ChangeOption("edit_completed", $bool_value);
        }
    }

    public function TemporaryRegistrationCompleted(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["temporary_registration_completed"];
		}
		else
        {
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["temporary_registration_completed"] =  $bool_value;
            return $this->ChangeOption("temporary_registration_completed", $bool_value);
        }
    }

    public function PasswordReissueCompleted(?bool $Value = null)
    {
		if(is_null($Value))
		{
			return $this->Object_Config["password_reissue_completed"];
		}
		else
        {    
            $bool_value = $this->GetConvertBoolValue($Value);
            $this->Object_Config["password_reissue_completed"] = $bool_value;
            return $this->ChangeOption("password_reissue_completed", $bool_value);
        }
    }
}