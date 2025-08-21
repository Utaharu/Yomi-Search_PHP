<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Search;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Search_Page_Printer
{
	private object $System;
	private Error_Manager $Error_Manager;

	private string $Template_Path = "";

	function __construct(object $Yomi_Search)
	{
	    $this->System = &$Yomi_Search;
		$this->Error_Manager = new Error_Manager("Search Page Printer Error");
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

	public function PrintFormPage()
	{
		require $this->Template_Path . 'search_ex.html';
	}

	public function PrintResultPage(array $Link_Data_List, array $Category_Data_List)
	{
        $title = "";
        if(isset($category['title']))
        {
            $title = $category['title'];
        }
        $guide = "";
        if(isset($category['comment']))
        {
            $guide = $category['description'];
        }
        $regist = false;
        if(isset($category['regist']))
        {
            $regist = $category['regist'];
        }


		if(preg_match('/^today-?(\d*)$/', $this->System->User->HttpQueries("search_day"), $match))
		{
			// today-x
			if($match[1] > 10000)
			{
				$match[1] = 0;
			}
			if($match[1] > 0)
			{
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			}
			else
			{
				$search_day = '★本日に更新されたサイト<br>';
			}
		}
		elseif(preg_match('/^(\d+)\-(\d+)$/', $this->System->User->HttpQueries('search_day'), $match))
		{
			// [str_day]-[end_day]
			if($match[2] > 0)
			{
				$search_day = '★' . $match[1] . '日前～' . $match[2] . '日前に更新されたサイト<br>';
			}
			else
			{
				$search_day = '★' . $match[1] . '日以内に更新されたサイト<br>';
			}
		}
		elseif(preg_match('/^(\d+)\/(\d+)\/(\d+)$/', $this->System->User->HttpQueries('search_day'), $match))
		{
			// year/mon/day
			$search_day = '★' . $this->System->User->HttpQueries('search_day') . ' に更新されたサイト<br>';
		}

		switch($this->System->User->HttpQueries("method"))
		{
			case "0" :
				$Stitle = '検索結果(検索ワード：' . $this->System->User->HttpQueries("word") . ' / 検索条件 : AND)';
			break;
			case "1" :
				$Stitle = '検索結果(検索ワード：' . $this->System->User->HttpQueries("word") . ' / 検索条件 : OR)';
			break;
			case "2" :
				$Stitle = '検索結果(検索式：' . $this->System->User->HttpQueries("word") . ')';
			break;
		}

            // ナビゲーションバーを表示
//            $navi = Navigation::Bredcrumbs($Form['path']);


		require $this->Template_Path . 'search.html';
	}

	
	// 検索オプションをクッキーに記録
	// オプション
	// [0]=>検索条件(a|o)/[1]=>検索式の使用有無(0|1)/[2]=>検索エンジン名(ID)/
	// [3]=>検索エンジン名(表示名)/[4]=>www.(0|1)/[5]=>カテゴリ指定(ID)
	// [6]=>カテゴリ指定(表示名)/[7]=>指定カテゴリ(0|1)/[8]=>日付指定(data)
	// [9]=>日付指定(表示名)/[10]=>日付指定コマンド(data)/[11]=>カテゴリ名検索(0|1)
/*
	private function SaveCookie()
	{
		if(isset($Form['set_option']))
		{
			if($Form['set_option'] == 'on')
			{
				if($Form['method'] == 'and')
				{
					$cookie_lines[0] = 'a'; // [0]
				}
				else
				{
					$cookie_lines[0] = 'o';
				}
				if($Form['use_str'] == 'on')
				{
					$cookie_lines[1] = '1'; // [1]
				}
				else
				{
					$cookie_lines[1] = '0';
				}
				$cookie_lines[2] = $Form['engine']; // [2]
				if($Form['engine'] == 'pre')
				{
					$cookie_lines[3] = $cfg['search_name'] . 'で'; // [3]
				}
				else
				{
					if(preg_match('/<option value=\"{$Form["engine"]}\">(.+)で/', $text['search_form'], $match))
					{
						$cookie_lines[3] = $match[1];
					}
				}
				$cookie_lines[3] = str_replace(',','，', $cookie_lines[3]);
				if($Form['www'] == 'on')
				{
					$cookie_lines[4]='0'; // [4]
				}
				else
				{
					$cookie_lines[4] = '1';
				}
				$cookie_lines[5] = $Form['search_kt']; // [5]
				if($Form['search_kt'])
				{
					$cookie_lines[6] = full_category($Form['search_kt']); // [6]
				}
				else
				{
					$cookie_lines[6] = '指定しない';
				}
				if($Form['search_kt_ex'] != '-b_all')
				{
					$cookie_lines[7]=0; // [7]
				}
				else
				{
					$cookie_lines[7] = 1;
				}
				$cookie_lines[8] = $Form['search_day']; // [8]
				if($Form['search_day'] == 'today')
				{
					$cookie_lines[9] = '本日'; // [9]
				}
				elseif(preg_match('/^(\d+)-/', $Form['search_day'], $match))
				{
					$cookie_lines[9] = $match[1] . '日以内';
				}
				else
				{
					$cookie_lines[9] = '指定しない';
				}
				$cookie_lines[10] = $Form['search_day_ex']; // [10]
				$cookie_lines[10] = str_replace(',','，', $cookie_lines[10]);
				if($Form['kt_search'] == 'on')
				{
					$cookie_lines[11]=0; // [11]
				}
				else
				{
					$cookie_lines[11] = 1;
				}
				$cookie_data[5] = implode(',', $cookie_lines);
				$cookie_data[5] = str_replace(';', '', $cookie_data[5]);
				Cookie::set_cookie($cookie_data);
			}
		}	
	}
*/
}