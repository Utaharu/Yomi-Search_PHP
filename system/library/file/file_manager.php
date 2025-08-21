<?php
namespace Sora_Kanata\Common_Library\File;

use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class File_Manager
{
    private object $Error_Manager;

	function __construct()
	{
		$this->Error_Manager = new Error_Manager("File Manager Error");
	}

	public function ReadToFile(string $File, bool $Load_Array = true)
	{
		switch($Load_Array)
		{
			case true :
				$data = array();
			break;
			default:
				$data = "";
			break;
		}
		$compression_protocol = "";
		$compression_protocol = $this->CompressionProtocol($File);
		$file_resource = fopen($compression_protocol . $File, "r");
		if($file_resource === false)
		{
			$this->Error_Manager->SetLog("ファイルが開けませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			flock($file_resource, LOCK_SH);
				while(feof($file_resource) !== true)
				{
					$file_line = fgets($file_resource);
					if(is_array($data))
					{
						if(rtrim($file_line, "\r\n"))
						{
							$data[] = rtrim($file_line, "\r\n");
						}
					}
					else
					{
						$data .= $file_line;
					}

				}
			flock($file_resource, LOCK_UN);	
			fclose($file_resource);
		}

		return $data;
	}

	public function WriteToFile(string $File, string $Data)
	{
		$write_flag = false;
		$compression_protocol = "";
		$compression_protocol = $this->CompressionProtocol($File);
		$file_resource = fopen($compression_protocol . $File, "w");
		if($file_resource === false)
		{
			$this->Error_Manager->SetLog("ファイルが開けませんでした。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		else
		{
			flock($file_resource, LOCK_EX);
				$write_flag = fwrite($file_resource, $Data);
			flock($file_resource, LOCK_UN);
			if($Data and $write_flag === false)
			{
				$this->Error_Manager->SetLog("書き込めませんでした。");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
			fclose($file_resource);
		}

		return true;
	}

	private function CompressionProtocol(string $File_Path)
	{
		$compress_protocol = "";
		$file_ext = strtolower(pathinfo($File_Path, PATHINFO_EXTENSION));
		switch($file_ext)
		{
			case "gz" :
				$compress_protocol = "compress.zlib://";
			break;
			case "bz2" :
				$compress_protocol = "compress.bzip2://";
			break;
		}
		return $compress_protocol;
	}

	public function DataDecompression($Type, $Data)
	{
		switch($Type)
		{
			case "gz":
				$Data = gzdecode($Data);
				break;
			case "bz2":
				$Data = bzdecompress($Data);
				break;
		}
		return $Data;
	}

	public function DataCompression($Type, $Data)
	{	
		switch($Type)
		{
			case "gz":
				$Data  = gzencode($Data);
				break;
			case "bz2":
				$Data = bzcompress($Data);
				break;
		}
		return $Data;
	}	

	public function LockToFile($Lock_File, $Type)
	{
		$file_resource = null;
		if(is_resource($Lock_File))
		{
			$file_resource = $Lock_File;
		}
		elseif(is_file($Lock_File))
		{
			$file_resource = fopen($Lock_File, "a");
			if($file_resource === false)
			{
				$this->Error_Manager->SetLog("LockFileが開けません");
				$this->Error_Manager->PrintLastErrorAsPage();
			}
		}
		
		switch($Type)
		{
			case "SH":
				$lock_type = LOCK_SH | LOCK_NB;
			break;
			case "EX":
				$lock_type = LOCK_EX | LOCK_NB;
			break;
			case "UN":
				$lock_type = LOCK_UN;
			break;
		}
		$flock_flag = flock($file_resource, $lock_type);
		if($flock_flag === false)
		{
			$this->Error_Manager->SetLog("ファイルロックの処理が失敗しました。");
			$this->Error_Manager->PrintLastErrorAsPage();
		}
		if($Type == "UN" and $flock_flag)
		{
			if(fclose($file_resource))
			{
				$file_resoruce = null; 
			}
		}

		return $file_resource;
	}

//Directory
	public function MakeDirectory(string $Path)
	{
        umask(0);
        $make_flag = mkdir($Path, 0777);
        if($make_flag === false)
		{
			$this->Error_Manager->SetLog("ディレクトリの生成に失敗。");
		}
        $this->WriteToFile($Path . "/index.html", "<html><head><title>403 Forbidden</title></head>\n<body>\n<div>403 Forbidden</div>\n</html>", "index_html");
        umask(0);
        chmod($Path . "/index.html", 0644);
		return $make_flag;
	}

	public  function DeleteDirectory(string $Path)
	{
		$del_flag = false;
		if(file_exists($Path))
		{
	  		$dirs = dir($Path);
			$f_cnt = 0; $ng_cnt = 0;
			$base_dir = dirname($Path);
			if($base_dir != "/" and $base_dir != "\\")
			{
				while(($ent = $dirs->read()) !== false)
				{
					if($ent != ".." and $ent != ".")
					{
						$delete_path = $Path . "/" . $ent;
						if(!is_dir($delete_path))
						{
							//Delete File
							$this->DeleteFiles(array($delete_path));
						}
						else
						{
							$this->DeleteDirectory($delete_path);
						}
					}
				}
				//Delete Directory
				$del_flag =  rmdir($Path);
			}
		}
		return $del_flag;
	}

	public function DeleteFiles(array $File_List)
	{
		if(is_array($File_List))
		{
			foreach ($File_List as $file)
			{
				if(is_file($file))
				{
					if(unlink($file) === false)
					{
						$this->Error_Manager->SetLog("ファイル : {$file}の削除に失敗しました。");
					}
				}
				else
				{
					$this->Error_Manager->SetLog("{$file}は、ファイルでは無いか存在しない為、削除を行いませんでした。");
				}
			}
		}
		return;
	}
	

}
