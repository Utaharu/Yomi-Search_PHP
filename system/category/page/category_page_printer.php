<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Category\Page;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Category_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

   	private string $Template_Path = "";

    private string $Category_Key = "";
    private $Category_Data = null;

    private $Print_Data = null;

	function __construct(&$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Category Page Printer Error");
        switch($this->System->Page_Type)
        {
            default :
                $this->Template_Path = $this->System->Config->Directory_List['pc_template'];
            break;
            case "mobile" :
                $this->Template_Path = $this->System->Config->Directory_List['phone_template'];
            break;
        }
    }

	public function PrintPage(array $Link_Data_List, int $Registerd_Link_Number, object $Category_Data)
	{

        $navi = "";
        if($this->System->Page_Mode)
        {
            $navi .= '&nbsp;-&nbsp;現在の総登録数:<b>'.$Registerd_Link_Number.'</b>サイト';
        }
/*
        $arg = array($Form['page'], $log_count[$search_id], $cfg['hyouji'], '&mode='.$Form['mode'].'&path='.$Form['path'].'&sort='.$Form['sort'], $cfg['script']);
        $mokuji = Navigation::Page($arg);
*/
        require $this->Template_Path . 'category.html';
	}




}