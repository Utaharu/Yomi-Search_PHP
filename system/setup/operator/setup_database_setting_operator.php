<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Setup\Operator;

/* Library */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;
use Sora_Kanata\Common_Library\File\File_Manager;
use Wonder_Link\Yomi_Search_PHP\System\DataBase\DataBase_Connector;

use PDO;
use PDOException;

class Setup_DataBase_Setting_Operator
{
    private object $System;
    private Error_Manager $Error_Manager;
    private File_Manager $File_Manager;

    private bool $Create_DataBase_Flag = false;
    private string $Data_Source_Name;
    private string $Host_Address;
    private int $Port_Number;
    private string $User_Name;
    private string $Password;
    private string $DataBase_Name;
    private string $Table_Prefix;
    private string $Charset;


    function __construct(object &$Yomi_Search)
    {
        $this->System = &$Yomi_Search;

        $this->Error_Manager = new Error_Manager("Setup DataBase Error");
        $this->File_Manager = new File_Manager();
    }

    public function VerifyDataBaseSettingValues()
    {
        $this->VerifyDataSourceName($this->System->User->HttpQueries("database_data_source_name"));
        $this->VerifyDataBaseHostAddress($this->System->User->HttpQueries("database_host_address"));
        $this->VerifyDataBasePortNumber(intval($this->System->User->HttpQueries("database_port_number")));
        $this->VerifyUserName($this->System->User->HttpQueries("database_user_name"));
        $this->VerifyPassword($this->System->User->HttpQueries("database_password"), $this->System->User->HttpQueries("no_database_password"));
        $this->VerifyDataBaseName($this->System->User->HttpQueries("database_name"));
        $this->VerifyCreateDataBase((bool) $this->System->User->HttpQueries("create_new_database"));
        $this->VerifyTablePrefix($this->System->User->HttpQueries("database_table_prefix"));
        $this->VerifyDataBaseCharset($this->System->User->HttpQueries("database_charset"));
        return true;
    }

    public function ConnectTest()
    {
        $database_connector = new DataBase_Connector();
        $database_connector->DataSourceName($this->Data_Source_Name);
        $database_connector->HostAddress($this->Host_Address);
        $database_connector->PortNumber($this->Port_Number);
        $database_connector->UserName($this->User_Name);
        $database_connector->Password($this->Password);
        $database_connector->DataBaseName($this->DataBase_Name);
        $database_connector->Charset($this->Charset);
        $database_connector->TablePrefix($this->Table_Prefix);
        $pdo_connect_error = $database_connector->Connect();
        if($pdo_connect_error instanceof PDOException)
        {
            return $pdo_connect_error;
        }
        else
        {
            return $database_connector;
        }
    }

    public function CreateDataBase()
    {   
        if($this->Create_DataBase_Flag)
        {
            return $this->System->DataBase_Manager->CreateDataBase();
        }
    }

    private function VerifyDataSourceName(string $Source_Type)
    {
        if(!$Source_Type)
        {
            $this->Error_Manager->SetLog("データベースの種類が選択されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!$this->System->CheckDataSourceName($Source_Type))
        {
            $this->Error_Manager->SetLog("対応していないデータベースの種類が選択されました。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Data_Source_Name = $this->System->CheckDataSourceName($Source_Type);
        }
    }

    private function VerifyDataBaseHostAddress(string $DataBase_Host_Address)
    {
        if(!$DataBase_Host_Address)
        {
            $this->Error_Manager->SetLog("DB - ホストアドレスが入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!preg_match("/^[a-z0-9-\.]+$/i", $DataBase_Host_Address) or !filter_var($DataBase_Host_Address, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))
        {
            $this->Error_Manager->SetLog("DB - ホストアドレスの形式が正しくありません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Host_Address = $DataBase_Host_Address;    
        }
    }

    private function VerifyDataBasePortNumber(int $DataBase_Port_Number)
    {
        if(is_numeric($DataBase_Port_Number))
        {
            if($DataBase_Port_Number < 0)
            {
                $this->Error_Manager->SetLog("DB - ポート番号は、0以上を入力してください。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            else
            {
                $this->Port_Number = intval($DataBase_Port_Number);
            }
       }
    }

    private function VerifyUserName(string $User_Name)
    {
        if(!$User_Name)
        {
            $this->Error_Manager->SetLog("DB - ログインユーザ名が入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        elseif(!preg_match("/^[a-z0-9]+$/", $User_Name))
        {
            $this->Error_Manager->SetLog("DB - ログインユーザ名の形式が不正です。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->User_Name = $User_Name;
        }
    }

    private function VerifyPassword(string $Password, bool $No_Password_Flag = false)
    {
        if(!$No_Password_Flag)
        {
            if(!$Password)
            {
                $this->Error_Manager->SetLog("DB - ログインパスワードが入力されていません。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            elseif($Password)
            {
                $this->Password = $Password;
            }
        }
        else
        {
            $this->Password = "";
        }
    }

    private function VerifyDataBaseName(string $DataBase_Name)
    {
        if(!$DataBase_Name)
        {
            $this->Error_Manager->SetLog("DB - データベース名が入力されていません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->DataBase_Name = $DataBase_Name;
        }
    }

    private function VerifyCreateDataBase(bool $Creaate_Flag)
    {
        if($Creaate_Flag == true)
        {
            $this->Create_DataBase_Flag = true;
        }
        else
        {
            $this->Create_DataBase_Flag = false;
        }
    }
    
    private function VerifyTablePrefix(string $Table_Prefix)
    {
        if(!preg_match("/^[a-z_]{0, 5}$/", $Table_Prefix))
        {
            $this->Error_Manager->SetLog("DB - テーブルPrefixに、利用できない文字が使用されています。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Table_Prefix = $Table_Prefix;
        }

    }

    private function VerifyDataBaseCharset(string $Charset)
    {
        if(!$Charset)
        {
            $this->Error_Manager->SetLog("データベースで利用する文字コードが入力されていません");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $this->Charset = $Charset;
        }
    }


    public function CreateSystemConfigFile(string $Template_System_Config_File_Path, string $Create_System_Config_File_Path)
    {
        if(!file_exists($Template_System_Config_File_Path))
        {
            $this->Error_Manager->SetLog("セットアップに必要なファイルが無い為、処理を行えません。");
            $this->Error_Manager->PrintLastErrorAsPage();
        }
        else
        {
            $file_data = $this->File_Manager->ReadToFile($Template_System_Config_File_Path, false);
            $pattern_list = 
                array(
                    "/%host_address%/i",
                    "/%user_name%/i",
                    "/%password%/i",
                    "/%database_name%/i",
                    "/%table_prefix%/i",
                    "/%charset%/i",
                    "/%port_number%/i",
                    "/%data_source_name%/i"                    
                );

            $replace_list =
                array(
                    $this->Host_Address,
                    $this->User_Name,
                    $this->Password,
                    $this->DataBase_Name,
                    $this->Table_Prefix,
                    $this->Charset,
                    $this->Port_Number,
                    $this->Data_Source_Name
                );
            $file_data = preg_replace($pattern_list, $replace_list, $file_data);
            if($file_data)
            {
                $this->File_Manager->WriteToFile($Create_System_Config_File_Path, $file_data);
            }
            if(!file_exists($Create_System_Config_File_Path))
            {
                $this->Error_Manager->SetLog("System Configファイルの生成に失敗しました。");
                $this->Error_Manager->PrintLastErrorAsPage();
            }
            return true;
        }
    }


    

}