<?php
namespace Wonder_Link\Yomi_Search_PHP\System\Template_Parts;

class Template_Parts_Tag_Replacer
{
    private string $Tag_Name;
    private string $Tag_Pattern;
    private string $Html_Tag;
    private array $Variable_Name_List;
    function __construct(string $Tag_Name)
    {
        $this->Tag_Name = $Tag_Name;
        $this->Tag_Pattern = "/<!--.*?" . $this->Tag_Name . ".*?tag:(?P<tag>.+)\s?-->/i";
    }

    public function Replace(array $Replace_Variable_Values, string $Template_Html)
    {
        if(preg_match($this->Tag_Pattern, $Template_Html, $matches))
        {
            if(isset($matches['tag']))
            {
                $this->Html_Tag = $matches['tag'];

                preg_match_all("/\\$(?P<name>[a-z0-9_]+)/i", $this->Html_Tag, $variable_name,PREG_SET_ORDER);
                foreach($variable_name as  $line)
                {
                    if(isset($line['name']))
                    {
                        $this->Variable_Name_List[] = $line['name'];
                    }
                }
                $html_tag_list = array();
                for($index = 0; $index < count($Replace_Variable_Values[$this->Variable_Name_List[0]]); $index++)
                {
                    $match_patterns = array();
                    $replace_values = array();                
                    foreach($this->Variable_Name_List as $name)
                    {
                        if(isset($Replace_Variable_Values[$name][$index]))
                        {
                            $match_patterns[] = '/\$' . $name . '/i';
                            $replace_values[] = $Replace_Variable_Values[$name][$index];
                        }
                    }
                    $replaced_html_tag_list[] = preg_replace($match_patterns, $replace_values, $this->Html_Tag);
                }
                return preg_replace($this->Tag_Pattern, implode("", $replaced_html_tag_list), $Template_Html);
            }
        }
        return $Template_Html;
    }    
}