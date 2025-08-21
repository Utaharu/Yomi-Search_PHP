<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Link\Page\Registration;

use Wonder_Link\Yomi_Search_PHP\System\Link\Operator\Link_Data_Operator;
use Wonder_Link\Yomi_Search_PHP\System\Link\Page\Registration\Link_Registration_Page_Printer;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Link_Registration_Page_Print_Moderator
{
	private object $System;
	private Error_Manager $Error_Manager;
    private Link_Data_Operator $Link_Operator;
    private Link_Registration_Page_Printer $Registration_Page_Printer;

	function __construct(&$Yomi_Search)
	{
        $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Link Registration Related Page Print Moderator Error");
        $this->Link_Operator = new Link_Data_Operator($Yomi_Search);
        $this->Registration_Page_Printer = new Link_Registration_Page_Printer($Yomi_Search);
    }

    public function Printer()
    {
        switch($this->System->Page_Mode)
        {
            case 'act_regist' :
            case "registration" :
                $registered_link_object = $this->Link_Operator->Registration();
                $this->System->Page_Title[] = $this->System->Config->Options["registration"]->NewRegistrationCompleteTitle();
                $this->Registration_Page_Printer->PrintCompleatePage($registered_link_object);
            break;
            case "registration_preview" :
                $registration_link_object = $this->Link_Operator->RegistrationPreview();

                //preview = on
                require $path_set['template']['root'] . '/regist_new_preview.html';
                require $path_set['template']['root'] . '/regist_new_end.html';
            break;
            case 'delete_registration' :
                $deleted_link_object = $this->Link_Operator->Deleting();
                $this->System->Page_Title[] = $this->System->Config->Options["registration"]->RegistrationDeletionCompletedTitle();
                $this->Registration_Page_Printer->PrintCompleatePage($deleted_link_object);
            break;
            case 'act_repass' :
                // パスワード再発行
                require $script['lib']['registration']['reissue'];
            break;
            case 'act_mente':
            case "registration_editing" :
                $edited_link_object = $this->Link_Operator->Editing();
                $this->System->Page_Title[] = $this->System->Config->Options["registration"]->RegistrationEditingCompletedTitle();
                $this->Registration_Page_Printer->PrintCompleatePage($edited_link_object);
            break;
            case 'enter' :
            case "operation_form" :
                $this->Registration_Page_Printer->PrintOperationFormPage();
            break;            
            case 'registration_edit_form' :
                $edit_link_object = $this->Link_Operator->GetEditingLinkData();
                $this->System->Page_Title[] = $this->System->Config->Options["registration"]->RegistrationEditingTitle();
                $this->Registration_Page_Printer->PrintEditorFormPage($edit_link_object);
            break;            
            case 'registration_form':
                $this->System->Page_Title[] = $this->System->Config->Options["registration"]->NewRegistrationTitle();
                $this->Registration_Page_Printer->PrintEditorFormPage();
            break;
        }
    }
}