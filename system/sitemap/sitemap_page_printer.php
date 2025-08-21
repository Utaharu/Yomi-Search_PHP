<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Sitemap;

use Wonder_Link\Yomi_Search_PHP\System\Category\Manager\Category_Data_Manager;
/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Sitemap_Page_Printer
{
	private object $System;
	private string $Template_Path = "";
	private Error_Manager $Error_Manager;
	private Category_Data_Manager $Categroy_Manager;

	function __construct(&$Yomi_Search)
	{
		$this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Sitemap Page Printer Error");
		$this->Categroy_Manager = new Category_Data_Manager($Yomi_Search);
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

	public function PrintPage(array $Category_Data_List)
	{
		$navi = "";
		$sort = "";
		if($this->System->User->HttpQueries('sort'))
		{
			$sort = $this->System->User->HttpQueries('sort');
		}

		require $this->Template_Path . "sitemap.html";
	}


    private function ReplaceTimeZoneList(string $Template_Html, array $Category_List)
    {
        $tag_replacer = new Template_Tag_Replacer("parent_category");
        $values = array("alias"=>$Category_List['data']->Alias(), "title"=>$Category_List['data']->Title());
        return $tag_replacer->Replace($values ,$Template_Html);
    }


}


