<?php
namespace Wonder_Link\Yomi_Search_PHP\System;

use PDOException;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Plugin\Plugin_Loader;

use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Page\Access_Ranking_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Access_Ranking\Manager\Access_Ranking_Data_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Category\Page\Category_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Keyword_Ranking\Page\Keyword_Ranking_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Search\Search_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\TopPage\Top_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\MyLink\MyLink_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Link\Page\Error_Report\Link_Error_Report_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Sitemap\Sitemap_Page_Print_Moderator;
use Wonder_Link\Yomi_Search_PHP\System\Setup\Setup_Moderator;

use Wonder_Link\Yomi_Search_PHP\System\Help\Help_Page_Printer;

use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Connector;

use Wonder_Link\Yomi_Search_PHP\System\Config\System_Config;

/* ---- Use Library ---- */
use Sora_Kanata\Common_Library\User\User_Manager;
use Sora_Kanata\Common_Library\File\File_Manager;
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Wonder_Link\Yomi_Search_PHP\System\Link\Page\Registration\Link_Ragistration_Page_Printer;
use Wonder_Link\Yomi_Search_PHP\System\Link\Page\Registration\Link_Registration_Page_Print_Moderator;

/*--------------------------------------------------------------------------*/
/* - # Yomi-Search PHP - # */
/*--------------------------------------------------------------------------*/
/*
	Yomi-Search				: http://yomi.pekori.to/
	Yomi-Search(PHP)			: http://sql.s28.xrea.com:8080/
	Yomi-Search(PHP)modified	: http://yomiphp-mod.sweet82.com/
	Yomi-Search(PHP)modified ver1.5.8.n2 : http://www.nkbt.net/yomi/
	Yomi-Search PHP Ver:8　～
*/
/*--------------------------------------------------------------------------*/

class Yomi_Search
{
    public const PASSWORD_HASH_ALGO = PASSWORD_DEFAULT;
    public const TEMPLATE_SYSTEM_CONFIG_FILE = __DIR__ . "/config/template_system_config.php";
    public const TEMPORARY_SYSTEM_CONFIG_FILE = __DIR__ . "/config/temporary_system_config.php";
    public const SYSTEM_CONFIG_FILE = __DIR__ . "/config/system_config.php";
   	public const MYSQL_DSN = "mysql:charset=%charset%;host=%host_address%;port=%port_number%;";
    public const ORDINARY_CATEGORY_TYPE = "ordinary";
    public const SPECIAL_CATEGORY_TYPE = "special";

	public object $User;
    public System_Config $Config;
    private string $Load_Config_File = "";
    public string $Template_Path;

    public $Html = "";
    public $Page_Robots = "";
    public $Page_Title  = array();
    public $Page_Description = "";
    public $Page_Keywords = "";
    public $Page_Type = "";
    public $Page_Mode = "";
    public $Page_Key = "";
    
	public int $Max_Page_Number = 1;
	public int $Page_Number = 1;
    public $Content_Title = "";

    public DataBase_Manager $DataBase_Manager;
    public object $Error_Manager;

    protected float $Start_Time = 0;
    protected float $End_Time = 0;


	function __construct()
	{
		$this->Start_Time = microtime(true);
        $this->Error_Manager = new Error_Manager();

        $this->RequireSystemConfigFile();
        $this->Config = new System_Config($this);
        $this->DataBase_Manager = new DataBase_Manager($this);
        $this->User = new User_Manager();

        if($this->Load_Config_File != $this::TEMPLATE_SYSTEM_CONFIG_FILE)
        {
            $database_connector = $this->LoadDataBaseConnectorFromSystemConfig();
            $this->DataBase_Manager->SetDataBaseConnector($database_connector);
            $pdo_error = $this->DataBase_Manager->ConnectDataBase();
            if($pdo_error instanceof PDOException)
            {
                $this->Error_Manager->SetLog("データベースへの接続に失敗しました。({$pdo_error->getMessage()})");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            else
            {
                $this->DataBase_Manager->SelectDataBase();
                $this->Config->LoadOptions();
                $this->EnvironmentalSpecifiedCall();
                if($this->Config->Options["locale"]->CharacterEncoding())
                {
                    $this->User->SetSystemEncoding($this->Config->Options["locale"]->CharacterEncoding());
                }
            }
        }
        $this->SettingUserPageRequest();
        $this->PageTypeSetting();        
	}

    private function RequireSystemConfigFile()
    {
        $this->Load_Config_File = "";
        if(file_exists($this::SYSTEM_CONFIG_FILE))
        {
print "main";
            $this->Load_Config_File = $this::SYSTEM_CONFIG_FILE;                
        }
        elseif(file_exists($this::TEMPORARY_SYSTEM_CONFIG_FILE))
        {
print "temporary";
            $this->Load_Config_File = $this::TEMPORARY_SYSTEM_CONFIG_FILE;
        }
        else
        {
print "template";
            $this->Load_Config_File = $this::TEMPLATE_SYSTEM_CONFIG_FILE;
        }
        if($this->Load_Config_File and file_exists($this->Load_Config_File))
        {
            require_once $this->Load_Config_File;
        }
        else
        {
            $this->Error_Manager->SetLog("Configファイルが見つかりませんでした。");
            $this->Error_Manager->PrintLastErrorAsPage();

        }
    }
    
    private function LoadDataBaseConnectorFromSystemConfig()
    {
        $database_connector = new DataBase_Connector();
        $database_connector->DataSourceName($this->Config->GetDataBaseSetting("data_source_name"));
        $database_connector->HostAddress($this->Config->GetDataBaseSetting("host_address"));
        $database_connector->PortNumber($this->Config->GetDataBaseSetting("port_number"));
        $database_connector->UserName($this->Config->GetDataBaseSetting("user_name"));
        $database_connector->Password ($this->Config->GetDataBaseSetting("password"));
        $database_connector->DataBaseName($this->Config->GetDataBaseSetting("database_name"));
        $database_connector->TablePrefix($this->Config->GetDataBaseSetting("table_prefix"));
        $database_connector->Charset($this->Config->GetDataBaseSetting("charset"));
        return $database_connector;
    }

    private function SettingUserPageRequest()
    {
        // - # Page Query Property
        if($this->User->HttpQueries('mode'))
        {
            $this->Page_Mode = $this->User->HttpQueries('mode');          
        }
        if($this->User->HttpQueries('key'))
        {
            $this->Page_Key = $this->User->HttpQueries('key');
        }
        if(is_numeric($this->User->HttpQueries('page')))
        {
            $this->Page_Number = $this->User->HttpQueries('page');
            // ページ設定
            if($this->Page_Number < 1 or  $this->Page_Number > 1000)
            {
                $this->Page_Number = 1;
            }
        }
        return;
    }

	private function EnvironmentalSpecifiedCall()
    {
        if($this->Load_Config_File === $this::SYSTEM_CONFIG_FILE)
        {
            if($this->Config->Options["locale"]->Language())
            {
                setlocale(LC_ALL, $this->Config->Options["locale"]->Language());
            }
            if($this->Config->Options["locale"]->CharacterEncoding())
            {
                mb_internal_encoding($this->Config->Options["locale"]->CharacterEncoding());
                mb_http_output($this->Config->Options["locale"]->CharacterEncoding());
                ob_start('mb_output_handler');
                mb_regex_encoding($this->Config->Options["locale"]->CharacterEncoding());   
            }
            if($this->Config->Options["locale"]->TimeZone())
            {      
                date_default_timezone_set($this->Config->Options["locale"]->TimeZone());
            }
        }
    }

  /* # Setting @ Caria - Page */
       protected function PageTypeSetting()
    {
        $this->Page_Type = $this->User->Device();

		switch($this->User->GetCookie('caria'))
		{
			case "pc" :
				$this->Page_Type = "pc";
			break;
			case "mobile" :
				$this->Page_Type = "mobile";
			break;
		}
      
        // - # Caria - Coookie Set
        if($this->User->HttpQueries('caria'))
        {
            if(($this->Page_Type == "pc" and $this->User->HttpQueries('caria') == "pc") or ($this->Page_Type == "mobile" and $this->User->HttpQueries('caria') != "pc"))
            {
                $this->User->SetCookie("caria", "", time()-999999);
            }
            elseif($this->Page_Type == "mobile" and $this->User->HttpQueries('caria') == "pc") 
            {
                $this->User->SetCookie("caria", "pc", (time() + (24 * 60 * 60)));
                $this->Page_Type = "pc";
            }
            elseif($this->Page_Type == "pc" and $this->User->HttpQueries('caria') == "mobile")
            {
                $this->User->SetCookie("caria", "mobile", time() + (24 * 60 * 60));
                $this->Page_Type = "mobile";
            }
        }

        return $this->Page_Type;
    }

    public function CheckDataSourceName(string $Data_Source_Name)
    {
        if(defined(__CLASS__ . "::" . $Data_Source_Name))
        {
            return constant(__CLASS__ . "::" . $Data_Source_Name);
        }
        return false;
    }

    /* # Switching @ Page Mode */
    protected function PageModeSwitcher()
    {
        switch($this->Page_Mode)
        {
            /*
                case "signup":
                case "new":
                    require $script['lib']['registration']['index'];
                break;
                case "enter":
                case "edit":
                    require $script['lib
                break;
            */
            case "link":
                $access_ranking_manager = new Access_Ranking_Data_Manager($this);
                $transfar_url = $access_ranking_manager->AccessOutCountUp(intval($this->User->HttpQueries("id")));
                if($transfar_url === false or !filter_var($transfar_url, FILTER_VALIDATE_URL))
                {
                    $this->Error_Manager->SetLog("リンク先のURLが判別できなかった為、転送処理に失敗しました。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
                else
                {
                    location($transfar_url);
                    exit;
                }
            break;
            case "r_link" :
                $access_ranking_manager = new Access_Ranking_Data_Manager($this);
                $transfar_page_url = $access_ranking_manager->AccessOutCountUp(intval($this->User->HttpQueries("id")));
                if($transfar_page_url === false or !filter_var($transfar_page_url, FILTER_VALIDATE_URL))
                {
                    $this->Error_Manager->SetLog("ジャンプ先のページが取得できなかった為、処理に失敗しました。");
                    $this->Error_Manager->PrintLastErrorAsPage();
                }
                else
                {
                    location($transfar_page_url);
                    exit;
                }
            break;
            case 'help':
                $help_page = new Help_Page_Printer($this);
                $help_page->PrintPage();
            break;
        }
    }

	public function StartUp()
	{
    
        $plugin = new Plugin_Loader($this);
        $plugin->LoadPlugin();
        $this->PageModeSwitcher();

        $access_ranking_page = new Access_Ranking_Page_Print_Moderator($this);
        $access_ranking_page->Printer();

        $keyword_ranking_page = new Keyword_Ranking_Page_Print_Moderator($this);
        $keyword_ranking_page->Printer();

        $category_page = new Category_Page_Print_Moderator($this);
        $category_page->Printer();

        // 検索結果表示画面
        $search_page = new Search_Page_Print_Moderator($this);
        $search_page->Printer();

        $mylink_page = new MyLink_Page_Print_Moderator($this);
        $mylink_page->Printer();

        $sitemap_page = new Sitemap_Page_Print_Moderator($this);
        $sitemap_page->Printer();

        $report_page = new Link_Error_Report_Page_Print_Moderator($this);
        $report_page->Printer();

        $registration_page = new Link_Registration_Page_Print_Moderator($this);
        $registration_page->Printer();

        $setup_page = new Setup_Moderator($this);
        $setup_page->Printer();

        $top_page = new Top_Page_Print_Moderator($this);
        $top_page->Printer();

        $this->Footer();
	}

    /* Print @ Footer */
    public function Footer()
    {
		if($this->Config->Debug_Mode)
		{
			$this->End_Time = microtime(true);
			print "<div style=\"text-align:right;\">Speed:" . round(($this->End_Time - $this->Start_Time), 5) . " Sec</div>";

			$fms = memory_get_peak_usage();
			if($fms > 0){$fmst = "B";}
			if($fmst == "B" and $fms >= 1000){$fmst = "KB"; $fms = $fms/1024;}
			if($fmst == "KB" and $fms >= 1000){$fmst = "MB"; $fms = $fms/1024;}
			if($fmst == "MB" and $fms >= 1000){$fmst = "GB"; $fms = $fms/1024;}
			if($fmst == "GB" and $fms >= 1000){$fmst = "TB"; $fms = $fms/1024;}

			print "<div style=\"text-align:right;\">Memory Max Use:" . round($fms ,2) . " ". $fmst."</div>";

		}
        $version = array
        (
            array('','Yomi-Search PHP Ver:8.0β'),
            array('http://www.nkbt.net/yomi/','Yomi-Search(PHP)modified ver1.5.8.n2'),
            array('http://yomiphp-mod.sweet82.com/','Yomi-Search(PHP)modified ver1.5.8'),
            array('http://sql.s28.xrea.com/','Yomi-Search(PHP)'),
            array('http://yomi.pekori.to/','Yomi-Search')
        );

        print	"<style type=\"text/css\">\n".
                "	<!--\n".
                "		#copyright {float:right; margin-top:10px; padding:5px; border:1px solid black; background-color:silver; color:black;}\n".
                "		#copyright > #version_name {text-decoration:underline;}\n".
                "		#copyright a{color:black;}\n".
                "		#copyright_version {display:none; padding:5px 5px 5px 20px; margin:5px 0 0 0; border-top:dashed black 1px;}\n".
                "		#copyright_version li {margin:0 0 0 10px; text-align:left;}\n".
                "		#copyright:hover > #copyright_version, #copyright:focus > #copyright_version,#copyright:active > #copyright_version{display:block;}\n".
                "	-->\n".
                "</style>\n";
        print	"<div id=\"copyright\">\n";
        print	"	This program is : <span id=\"version_name\">" .  $version[0][1] . "</span>\n";
        print	"	<ul id=\"copyright_version\">\n";

        foreach($version as $line)
        {
            if($line)
            {
                print "		<li>\n";
                if(preg_match("/^https?:\/\/.+/",$line[0]))
                {
                    print "		<a href=\"" . $line[0] . "\" target=\"_blank\" title=\"" . $line[1] . "\">" . $line[1] . "</a>\n";
                }
                else
                {
                    print "		{$line[1]}\n";
                }
                print "		</li>\n";
            }
        }
        print	"	</ul>\n";
        print	"</div>\n";        
    }

}