<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Administration;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Option_About_Administrator;
use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Option_Keyword_Ranking;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Search_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Manager\Link_Temporary_Data_Manager;

class Administration_Page_Moderator
{
    private object $System;
    private Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Error_Manager = new Error_Manager("Administration Page Moderator Error");

        if($this->System->Page_Mode == "administration")
        {
            if(!$this->System->User->HttpQueries("password"))
            {
                require $this->System->Config->Directory_List["administrator_template"] . "admin.html";
            }
            elseif(password_verify($this->System->User->HttpQueries("password"), $this->System->Config->Options["administrator"]->Password()) === false)
            {
                $this->Error_Manager->SetLog("パスワードが一致しない為、ログインできません。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            else
            {
                switch($this->System->User->HttpQueries("method"))
                {
                    case "cfg_kanri" :
                        require $cfg["temp_path"] . "admin/cfg_kanri.html";
                        exit;
                    case "log_conv" :
                        require $cfg["temp_path"] . "admin/log_conv.html";
                        exit;
                    case "log_repair" :
                        require $cfg["temp_path"] . "admin/log_repair.html";
                        exit;
                    case "config" :
                        require $cfg["temp_path"] . "admin/config.html";
                        exit;
                    case "rank_cfg" :
                        require $cfg["temp_path"] . "admin/rank_cfg.html";
                        exit;
                    case "mylink_cfg" :
                        require $cfg["temp_path"] . "admin/mylink_cfg.html";
                        exit;
                    case "cfg_admin_pass" :
                        require $cfg["temp_path"] . "admin/cfg_admin_pass.html";
                        exit;
                    case "cfg_reg" : 
                        require $cfg["temp_path"] . "admin/cfg_reg.html";
                        exit;
                    case "do_formal_registration" :
                        $this->DoFormalRegistration();
                    break;
                    case "new_password":
                        $this->NewPassword();
                    break;
                    case "look_mes":
                    	require $cfg["temp_path"] . "admin/look_mes.html";
                    	exit;
                    break;
                }
            }
        }
    }


    private function TemporaryToRegistration()
    {
        $link_search_manager = new Link_Search_Manager($this->System);
        $link_search_manager->ClearSearchData();
        $link_search_manager->SearchTemporaryLinks();
        $link_list = $link_search_manager->GetLinkList();
        $link_count = $link_search_manager->GetRegisteredLinkNumber();

    	require $cfg["temp_path"] . "admin/temp_to_regist.html";
    }

    private function DoFormalRegistration()
    {
        $temporary_manager = new Link_Temporary_Data_Manager($this->System);
        $temporary_id_list = $this->System->User->HttpQueries("temporary_id");
        if(is_array($temporary_id_list))
        {
            foreach($temporary_id_list as $temporary_id)
            {
                if(is_numeric($temporary_id) and $temporary_id > 0)
                {
                    $temporary_manager->DoFormalRegistration(intval($temporary_id));
                        // 仮登録→新規登録時のメールを送信                   
                }
            }
        }
    }

    private function UpdateKeywordRankingOption()
    {
        $option_keyword_ranking = new Option_Keyword_Ranking($this->System);
        if($this->System->User->HttpQueries("is_enabled") == 0 )
        $option_keyword_ranking->IsEnabled();
    }
}