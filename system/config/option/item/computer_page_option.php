<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Config\Option\Item;

use Wonder_Link\Yomi_Search_PHP\System\Config\Option\Inheritance_Class\Option_Inheritance_Class;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Computer_Page_Option extends Option_Inheritance_Class
{

    protected object $System;
    protected string $Option_Group_Name = "computer_page";
    protected array $Object_Config = array();
    public Error_Manager $Error_Manager;

    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
        $this->Object_Config =
            array(
				'search_engine_title'=> 'Yomi-Search',//　サーチエンジンの名称
				'search_engine_url' => '',                 // サーチエンジンのトップへのパスorURL
				'script'       => 'index.php',                 // yomi-search本体のファイル名
				'search'       => 'search.php',               // 検索用のPHPファイル名
				'rank'         => 'rank.php',                 // ランキング用のPHPファイル名
				'admin'        => 'admin.php',                // 管理処理用のPHPファイル名
				"banner_height" =>31,
				"banner_width" => 88,
				"link_list_pieces" => 20// 1ページに表示するログ件数
            );
        $this->Error_Manager = new Error_Manager("Option Computer Page Error");
        $this->LoadOptionsValue();
    }

    public function SearchEngineTitle(?string $Title = null)
    {
		if(is_null($Title))
		{
			return $this->Object_Config["search_engine_title"];
		}
		elseif(!$Title)
        {
            $this->Error_Manager->SetLog("サーチエンジンの名称が入力されていません。");
        }
        else
        {
           $this->Object_Config["search_engine_title"] = $Title;
           return $this->ChangeOption("search_engine_title", $Title);
        }
    }

    public function SearchEngineUrl(?string $Search_Engine_Url = null)
    {
    	if(is_null($Search_Engine_Url))
		{
			return $this->Object_Config["search_engine_url"];
		}
		elseif(!$Search_Engine_Url)
		{
			$this->Error_Manager->SetLog("サーチエンジンのURLが入力されていません。");
		}
		elseif(!filter_var($Search_Engine_Url, FILTER_VALIDATE_URL) or !preg_match("/^https?:\/\/.+/", $Search_Engine_Url))
		{
			$this->Error_Manager->SetLog("サーチエンジンのURLの入力形式が正しくありません");
		}        
		else
        {
            $this->Object_Config["search_engine_url"] = $Search_Engine_Url;
            return $this->ChangeOption("search_engine_url", $Search_Engine_Url);
        }
    }

    public function Script(?string $Script = null)
    {
    	if(is_null($Script))
		{
			return $this->Object_Config["script"];
		}
		elseif($Script)
        {
            $this->Object_Config["script"] = $Script;
            return $this->ChangeOption("script", $Script);
        }
    }

    public function Search(?string $Search = null)
    {
		if(is_null($Search))
		{
			return $this->Object_Config["search"];
		}
		elseif($Search)
        {
            $this->Object_Config["search"] = $Search;
            return $this->ChangeOption("search", $Search);
        }
    }

    public function Rank(?string $Rank = null)
    {
        if(is_null($Rank))
		{
			return $this->Object_Config["rank"];
		}
		elseif($Rank)
        {
            $this->Object_Config["rank"] = $Rank;
            return $this->ChangeOption("rank", $Rank);
        }
    }

    public function Admin(?string $Admin = null)
    {
		if(is_null($Admin))
		{
			return $this->Object_Config["admin"];
		}
		elseif($Admin)
        {
            $this->Object_Config["admin"] = $Admin;
            return $this->ChangeOption("admin", $Admin);
        }
    }

    public function BannerHeight(null|int|string $Banner_Height = null)
    {
    	if(is_null($Banner_Height))
		{
			return $this->Object_Config["banner_height"];
		}
		elseif(!is_numeric($Banner_Height))
        {
            $this->Error_Manager->SetLog("バナーの高さは、数字で指定してください。");
        }
        else
        {
            $int_value = $this->GetConvertIntValue($Banner_Height, 0);
            if($int_value !== false)
            {
                $this->Object_Config["banner_height"] = $int_value;
                return $this->ChangeOption("banner_height", $int_value);
            }
        }
    }

    public function BannerWidth(null|int|string $Banner_Width = null)
    {
		if(is_null($Banner_Width))
		{
			return $this->Object_Config["banner_width"];
		}
		elseif(!is_numeric($Banner_Width))
        {
            $this->Error_Manager->SetLog("バナーの幅は、数字で指定してください。");
        }        
		else
        {  
            $int_value = $this->GetConvertIntValue($Banner_Width, 0);
            if($int_value !== false)
            {
                $this->Object_Config["banner_width"] = $int_value;
                return $this->ChangeOption("banner_width", $int_value);
            }
        }
    }

    public function LinkListPieces(null|int|string $Link_Pieces = null)
    {
		if(is_null($Link_Pieces))
		{
			return $this->Object_Config["link_list_pieces"];
		}
		elseif(!is_numeric($Link_Pieces))
        {
            $this->Error_Manager->SetLog("表示個数は、数字で指定してください。");
        }
        else        
        {    
            $int_value = $this->GetConvertIntValue($Link_Pieces, 0);
            if($int_value !== false)
            {
                $this->Object_Config["link_list_pieces"] = $int_value;
                return $this->ChangeOption("link_list_pieces", $int_value);
            }
        }
    }

}