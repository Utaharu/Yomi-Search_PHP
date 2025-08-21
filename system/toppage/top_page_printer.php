<?php
namespace Wonder_Link\Yomi_Search_PHP\System\TopPage;

/* Liblary */
use Sora_Kanata\Common_Library\Error\Manager\Error_Manager;

class Top_Page_Printer
{
    private object $System;
    private Error_Manager $Error_Manager;
    private string $Template_Path = "";

    function __construct(&$Yomi_Search)
    {
        $this->System = &$Yomi_Search;
       	$this->Error_Manager = new Error_Manager("Top Page Printer Error");
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

    public function PrintPage(array $Ordinary_Category_List, array $Special_Category_List, array $Keyword_Ranking_List)
    {
//        $total_num = $this->System->DataBase_Manager->LogCount('log');

//        $search_words = $this->VariableRecommendSearchWords();
        $RssFeed = $this->VariableRssFeed();

        require $this->Template_Path . 'top.html';
    }

    private function VariableRssFeed()
    {
        $RssFeed  = '';
        $RssFeed .= '<a href="' . $this->System->Config->Root . 'rss/rss.php?mode=new" target="_blank">';
        $RssFeed .= '<img src="' . $this->System->Config->Root . 'img/rss_new.gif" border="0" alt="新着サイト" title="新着サイト">';
        $RssFeed .= '</a>';
        $RssFeed .= ' ';
        $RssFeed .= '<a href="' . $this->System->Config->Root . 'rss/rss.php?mode=update" target="_blank">';
        $RssFeed .= '<img src="' . $this->System->Config->Root . 'img/rss_update.gif" border="0" alt="更新サイト" title="更新サイト">';
        $RssFeed .= '</a>';
        return $RssFeed;
    }

    private function VariableRecommendSearchWords()
    {
        $search_words = "";
        $recommend_words = array();
        if($this->System->Config->Options["keyword_ranking"]->IsEnabled())
        {
    //        $recommend_words =  $this->Keyword_Ranking_Manager->GetRankingData(24*60*60*7, time(), 5);

            if( is_array($recommend_words) and count($recommend_words) > 0) 
            {
                $search_words = '<br>【 <a href="'. $this->System->Config->Options["rank"] .'?mode=keyrank">おすすめ検索ワード</a> =&gt; ';
                foreach ($recommend_words as $key => $row)
                {
                    if(isset($row['word']))
                    {
                        $word = rtrim($row['word']);
                        $word_en = urlencode($word);
                        if ($row['view_word'])
                        {
                            $word = $row['view_word'];
                        }
                        $search_words .= '<input type="checkbox" name="words[]" value="' . $word . '">' . "\n"
                                    .  '<a href="' . $this->System->Config->Options['search'] . '?mode=search&word='.$word_en.'&sort=' . $this->System->Config->Options['defo_hyouji'] . '&engine=pre&method=and">'
                                    .  $word
                                    .  '</a>'."\n";
                    }
                }
                $search_words .= '】<input type="hidden" name="kn" value="'. $key .'">';
            }
        }
        return $search_words;
    }

}